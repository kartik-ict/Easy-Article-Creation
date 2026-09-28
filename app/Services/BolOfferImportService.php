<?php

namespace App\Services;

use App\Models\BolImageImportBatch;
use App\Models\BolOfferMapping;
use Illuminate\Support\Facades\Log;

/**
 * Bol offer ID is not stored anywhere in Shopware, and Channable only pushes product data
 * SW -> Bol (never the other way), so there's no existing path to learn a product's Bol offer ID.
 * Bol's own "offer export" endpoint solves this: it returns a CSV of every offer on the account,
 * including `offerId`, `ean`, and `referenceCode` — synced into bol_offer_mappings here.
 *
 * Matching key: EAN is NOT unique per product for refurbished stock — Kartik confirmed (2026-09-17)
 * every refurbished unit gets its own Shopware product number, but shares the same EAN as every
 * other unit of that model. So EAN alone can't tell two different offers/products apart.
 *
 * CONFIRMED against the real export (2026-09-17, once real credentials arrived): `referenceCode`
 * is Shopware's internal product **id** (the 32-char hex UUID, not the human-readable product
 * number) — whatever created these offers set it to the product's real Shopware id. Resolved via
 * `GET /api/product/{id}` to get the actual product number. Falls back to EAN only when
 * referenceCode is missing/unresolvable, and only when EAN resolves to exactly one product
 * (skipped, not guessed, when it resolves to more than one).
 */
class BolOfferImportService
{
    public function __construct(
        private BolAuthService $bolAuth,
        private ShopwareAuthService $shopwareAuth,
    ) {
    }

    /**
     * Full sync: request an offer export, poll until ready, parse the CSV, and upsert a
     * bol_offer_mappings row for every EAN we recognise as one of our own products.
     *
     * Bounded synchronous polling (like ShopwareAuthService::makeApiRequest's own retry loop) —
     * EAC has no queue/scheduler infra for a true background wait, so this blocks the request for
     * at most ~20s. Safe to also run from a scheduled console command later if the export ever
     * takes longer than that in practice.
     *
     * @return array{synced: int, error: string|null}
     */
    public function syncOfferMappings(): array
    {
        $exportRequest = $this->bolAuth->makeApiRequest('POST', '/retailer/offers/export', [
            'format' => 'CSV',
        ]);

        if (isset($exportRequest['error'])) {
            return ['synced' => 0, 'error' => $exportRequest['error']];
        }

        $processStatusId = $exportRequest['processStatusId'] ?? null;
        if ($processStatusId === null) {
            return ['synced' => 0, 'error' => 'Bol did not return a processStatusId for the export request.'];
        }

        $reportId = null;
        for ($i = 0; $i < 10; $i++) {
            sleep(2);
            // CONFIRMED (2026-09-17): process-status lives under /shared/, not /retailer/ — the
            // export request's own `links[0].href` gave this away. entityId on a SUCCESS response
            // is genuinely the export's report-id (verified: fetched a real 14k-row CSV with it).
            $status = $this->bolAuth->makeApiRequest('GET', '/shared/process-status/' . $processStatusId);

            if (($status['status'] ?? null) === 'SUCCESS') {
                $reportId = $status['entityId'] ?? null;
                break;
            }

            if (in_array($status['status'] ?? null, ['FAILURE', 'TIMEOUT'], true)) {
                return ['synced' => 0, 'error' => 'Bol export processing failed: ' . ($status['errorMessage'] ?? 'unknown reason')];
            }
        }

        if ($reportId === null) {
            return ['synced' => 0, 'error' => 'Timed out waiting for Bol to prepare the offer export.'];
        }

        $report = $this->bolAuth->makeApiRequest('GET', '/retailer/offers/export/' . $reportId, [], 3, 'application/vnd.retailer.v10+csv');
        if (isset($report['error']) || !isset($report['raw'])) {
            return ['synced' => 0, 'error' => $report['error'] ?? 'Bol did not return the export file.'];
        }

        $rows = $this->parseCsv($report['raw']);
        $rows = array_values(array_filter($rows, fn (array $r) => !empty($r['ean']) && !empty($r['offerId'])));

        // The export lists every offer on the whole account (14k+ rows on this shop, not just
        // circular-pilot ones) — resolving each row's product with its own live Shopware API call
        // was the same N+1 mistake DGM-306 already hit once (~14k rows, one HTTP round-trip each,
        // took minutes and still timed out). Batch-resolve instead: one chunked search for all
        // referenceCode ids, one chunked search for whatever EANs are left over.
        $idsToLookup = array_values(array_unique(array_filter(array_map(
            fn (array $r) => trim($r['referenceCode'] ?? ''),
            $rows
        ))));
        $productNumberById = $this->batchLookupProductNumbersById($idsToLookup);

        $eansStillNeeded = array_values(array_unique(array_filter(array_map(
            function (array $r) use ($productNumberById) {
                $refCode = trim($r['referenceCode'] ?? '');
                return isset($productNumberById[$refCode]) ? null : $r['ean'];
            },
            $rows
        ))));
        $productNumbersByEan = $this->batchLookupProductNumbersByEan($eansStillNeeded);

        $synced = 0;
        $skipped = 0;
        $upserts = [];

        foreach ($rows as $row) {
            $referenceCode = trim($row['referenceCode'] ?? '');
            $productNumber = $productNumberById[$referenceCode] ?? null;

            if ($productNumber === null) {
                $eanMatches = $productNumbersByEan[$row['ean']] ?? [];
                $productNumber = count($eanMatches) === 1 ? $eanMatches[0] : null;
            }

            if ($productNumber === null) {
                $skipped++;
                continue; // ambiguous or unrecognised — skip rather than guess wrong
            }

            $upserts[$productNumber] = [
                'ean' => $row['ean'],
                'offer_id' => $row['offerId'],
            ];
        }

        foreach ($upserts as $productNumber => $data) {
            BolOfferMapping::updateOrCreate(
                ['product_number' => $productNumber],
                array_merge($data, ['last_synced_at' => now()])
            );
            $synced++;
        }

        if ($skipped > 0) {
            Log::warning("Bol offer sync: skipped {$skipped} offer(s) — no reliable product match (ambiguous EAN and no usable referenceCode). See BolOfferImportService docblock.");
        }

        return ['synced' => $synced, 'error' => null];
    }

    /**
     * @param string[] $productIds Shopware product ids (referenceCode values)
     * @return array<string, string> id => productNumber
     */
    private function batchLookupProductNumbersById(array $productIds): array
    {
        $map = [];

        foreach (array_chunk($productIds, 500) as $chunk) {
            $response = $this->shopwareAuth->makeApiRequest('POST', '/api/search/product', [
                'filter' => [
                    ['type' => 'equalsAny', 'field' => 'id', 'value' => $chunk],
                ],
                'limit' => 500,
            ]);

            foreach ($response['data'] ?? [] as $row) {
                $map[$row['id']] = $row['attributes']['productNumber'] ?? null;
            }
        }

        return array_filter($map);
    }

    /**
     * @param string[] $eans
     * @return array<string, string[]> ean => productNumber[] (more than one entry means ambiguous)
     */
    private function batchLookupProductNumbersByEan(array $eans): array
    {
        $map = [];

        foreach (array_chunk($eans, 500) as $chunk) {
            $response = $this->shopwareAuth->makeApiRequest('POST', '/api/search/product', [
                'filter' => [
                    ['type' => 'equalsAny', 'field' => 'ean', 'value' => $chunk],
                ],
                'limit' => 500,
            ]);

            foreach ($response['data'] ?? [] as $row) {
                $ean = $row['attributes']['ean'] ?? null;
                $productNumber = $row['attributes']['productNumber'] ?? null;
                if ($ean !== null && $productNumber !== null) {
                    $map[$ean][] = $productNumber;
                }
            }
        }

        return $map;
    }

    /**
     * Submit (or replace) the full photo set for a product's Bol offer. Bol's own design
     * confirmed every import call replaces the entire asset list — always send the complete
     * current set, never just a delta.
     *
     * @param string[] $imageUrls ordered — first URL is the primary photo, per Bol's confirmed design
     */
    public function submitImageBatch(string $productNumber, array $imageUrls): array
    {
        $mapping = BolOfferMapping::where('product_number', $productNumber)->first();
        if ($mapping === null || !$mapping->offer_id) {
            return ['error' => 'No Bol offer ID known for this product yet — run a mapping sync first.'];
        }

        $path = str_replace('{offerId}', $mapping->offer_id, config('bol.offer_import_path'));
        $response = $this->bolAuth->makeApiRequest('POST', $path, [
            'assets' => array_map(fn (string $url) => ['url' => $url], $imageUrls),
        ], 3, 'application/vnd.retailer.v11-pilot-bulk-image-import+json');

        if (isset($response['error'])) {
            return ['error' => $response['error']];
        }

        $batchId = $response['batchId'] ?? null;
        $batch = BolImageImportBatch::create([
            'product_number' => $productNumber,
            'offer_id' => $mapping->offer_id,
            'batch_id' => $batchId,
            'status' => 'PENDING',
            'submitted_urls' => $imageUrls,
        ]);

        return ['batch' => $batch, 'error' => null];
    }

    /**
     * Check (and persist) the current status of a submitted batch.
     */
    public function pollBatchStatus(BolImageImportBatch $batch): BolImageImportBatch
    {
        if (!$batch->batch_id) {
            return $batch;
        }

        $path = str_replace(
            ['{offerId}', '{batchId}'],
            [$batch->offer_id, $batch->batch_id],
            config('bol.offer_import_status_path')
        );
        $response = $this->bolAuth->makeApiRequest('GET', $path, [], 3, 'application/vnd.retailer.v11-pilot-bulk-image-import+json');

        if (isset($response['error'])) {
            $batch->error = $response['error'];
            $batch->save();

            return $batch;
        }

        $batch->status = $response['status'] ?? $batch->status;
        $batch->asset_results = $response['assets'] ?? $batch->asset_results;
        $batch->error = null;
        $batch->save();

        return $batch;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseCsv(string $raw): array
    {
        $lines = array_filter(preg_split('/\r\n|\r|\n/', $raw));
        if (empty($lines)) {
            return [];
        }

        $header = str_getcsv(array_shift($lines));
        $rows = [];

        foreach ($lines as $line) {
            $values = str_getcsv($line);
            if (count($values) !== count($header)) {
                continue;
            }
            $rows[] = array_combine($header, $values);
        }

        return $rows;
    }
}
