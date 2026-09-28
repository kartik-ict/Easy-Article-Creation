<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BolImageImportBatch extends Model
{
    protected $fillable = [
        'product_number',
        'offer_id',
        'batch_id',
        'status',
        'submitted_urls',
        'asset_results',
        'error',
    ];

    protected $casts = [
        'submitted_urls' => 'array',
        'asset_results' => 'array',
    ];
}
