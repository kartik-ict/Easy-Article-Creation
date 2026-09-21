<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLog extends Model
{
    protected $fillable = [
        'product_number',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'message'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    public static function logProductChange(string $productId, string $action, array $oldValues = null, array $newValues = null, string $message = null)
    {
        // Get product number from Shopware API
        $shopwareService = app(\App\Services\ShopwareAuthService::class);
        $productData = $shopwareService->makeApiRequest('GET', '/api/product/' . $productId);
        $productNumber = $productData['data']['attributes']['productNumber'] ?? $productId;
        
        return self::create([
            'product_number' => $productNumber,
            'user_id' => auth('admin')->id(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'message' => $message,
            'ip_address' => request()->ip()
        ]);
    }
}