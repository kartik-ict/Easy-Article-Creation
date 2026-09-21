<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BolOfferMapping extends Model
{
    protected $fillable = [
        'product_number',
        'ean',
        'offer_id',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];
}
