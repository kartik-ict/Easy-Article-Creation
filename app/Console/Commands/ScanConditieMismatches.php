<?php

namespace App\Console\Commands;

use App\Services\ShopwareAuthService;
use Illuminate\Console\Command;

/**
 * Read-only report: variants where the Conditie (condition) value is set in
 * customFields but never actually linked as a real Shopware property option —
 * the "N/A-pattern" ghost variants from DGM-309. Produces the review list
 * Rory asked for; does not change anything.
 */
class ScanConditieMismatches extends Command
{
    protected $signature = 'conditie:scan {--csv= : Optional file path to also write the results as CSV}';

    protected $description = 'List variants whose condition value is set but not linked as a real Conditie option';

    protected ShopwareAuthService $shopwareApiService;

    public function __construct(ShopwareAuthService $shopwareApiService)
    {
        parent::__construct();
        $this->shopwareApiService = $shopwareApiService;
    }

    public function handle()
    {
        $this->info('Looking up the Conditie property group...');
        $groupSearch = $this->shopwareApiService->makeApiRequest('POST', '/api/search/property-group', [
            'filter' => [['type' => 'equals', 'field' => 'name', 'value' => 'Conditie']],
            'limit' => 1,
        ]);
        $conditieGroupId = $groupSearch['data'][0]['id'] ?? null;

        if (!$conditieGroupId) {
            $this->error('No property group named "Conditie" found — nothing to scan.');
            return self::FAILURE;
        }

        $this->info('Scanning variants with a condition value set (this pages through the catalog, it can take a minute)...');

        $candidates = [];
        $allOptionIds = [];
        $page = 1;
        $limit = 200;

        // Shopware's default totalCountMode doesn't return an accurate total (it's
        // capped to the page size for performance), so we page until a page comes
        // back short of the limit rather than trusting meta.total.
        do {
            $result = $this->shopwareApiService->makeApiRequest('POST', '/api/search/product', [
                'filter' => [
                    ['type' => 'not', 'operator' => 'or', 'queries' => [
                        ['type' => 'equals', 'field' => 'parentId', 'value' => null],
                    ]],
                    ['type' => 'not', 'operator' => 'or', 'queries' => [
                        ['type' => 'equals', 'field' => 'customFields.migration_DMG_product_bol_condition', 'value' => null],
                    ]],
                ],
                'includes' => ['product' => ['id', 'productNumber', 'stock', 'optionIds', 'customFields']],
                'page' => $page,
                'limit' => $limit,
            ]);

            if (isset($result['error'])) {
                $this->error('Shopware API error while scanning: ' . $result['error']);
                return self::FAILURE;
            }

            $rows = $result['data'] ?? [];

            foreach ($rows as $row) {
                $conditionValue = $row['attributes']['customFields']['migration_DMG_product_bol_condition'] ?? null;
                if (in_array($conditionValue, [null, 'null', '0'], true)) {
                    continue;
                }
                $optionIds = $row['attributes']['optionIds'] ?? [];
                $candidates[] = [
                    'id' => $row['id'],
                    'productNumber' => $row['attributes']['productNumber'] ?? '',
                    'stock' => $row['attributes']['stock'] ?? 0,
                    'condition' => $conditionValue,
                    'optionIds' => $optionIds,
                ];
                foreach ($optionIds as $optionId) {
                    $allOptionIds[$optionId] = true;
                }
            }

            $this->line("  ...page {$page} checked (" . count($rows) . ' rows), ' . count($candidates) . ' candidates with a condition value so far');
            $isLastPage = count($rows) < $limit;
            $page++;
        } while (!$isLastPage && $page <= 1000); // hard safety cap against a runaway loop

        // One batched lookup instead of one API call per candidate: which of all the
        // option ids seen actually belong to the Conditie group.
        $realConditieOptionIds = [];
        if (!empty($allOptionIds)) {
            $optionsLookup = $this->shopwareApiService->makeApiRequest('POST', '/api/search/property-group-option', [
                'filter' => [
                    ['type' => 'equalsAny', 'field' => 'id', 'value' => array_keys($allOptionIds)],
                    ['type' => 'equals', 'field' => 'groupId', 'value' => $conditieGroupId],
                ],
                'limit' => 500,
            ]);
            foreach (($optionsLookup['data'] ?? []) as $row) {
                $realConditieOptionIds[$row['id']] = true;
            }
        }

        $mismatches = [];
        foreach ($candidates as $candidate) {
            $hasRealLink = false;
            foreach ($candidate['optionIds'] as $optionId) {
                if (isset($realConditieOptionIds[$optionId])) {
                    $hasRealLink = true;
                    break;
                }
            }
            if (!$hasRealLink) {
                $mismatches[] = $candidate;
            }
        }

        $this->newLine();
        $this->info(count($mismatches) . ' variant(s) have a condition value but no real Conditie link:');
        $this->table(
            ['Product number', 'Condition value', 'Stock', 'Shopware ID'],
            array_map(fn($m) => [$m['productNumber'], $m['condition'], $m['stock'], $m['id']], $mismatches)
        );

        if ($csvPath = $this->option('csv')) {
            $handle = fopen($csvPath, 'w');
            fputcsv($handle, ['product_number', 'condition_value', 'stock', 'shopware_id']);
            foreach ($mismatches as $m) {
                fputcsv($handle, [$m['productNumber'], $m['condition'], $m['stock'], $m['id']]);
            }
            fclose($handle);
            $this->info("Also written to {$csvPath}");
        }

        return self::SUCCESS;
    }
}
