<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpsPlanPricing extends Model
{
    use HasFactory;

    protected $table = 'vps_plan_pricings';

    protected $fillable = [
        'vps_plan_id',
        'duration_months',
        'base_price',
        'discount_percent',
        'final_price',
        'base_price_per_month',
        'final_price_per_month'
    ];

    public function vpsPlan()
    {
        return $this->belongsTo(VpsPlan::class, 'vps_plan_id');
    }

    public function discountCodes()
    {
        return $this->hasMany(VpsDiscountCode::class);
    }
}
