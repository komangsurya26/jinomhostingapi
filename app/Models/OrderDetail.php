<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'product_type',
        'product_id',
        'plan_pricing_id',
        'price'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function vpsPlanPricing()
    {
        return $this->belongsTo(VpsPlanPricing::class, 'plan_pricing_id');
    }
}
