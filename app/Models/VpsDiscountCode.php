<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpsDiscountCode extends Model
{
    use HasFactory;

    protected $table = 'vps_discount_codes';

    protected $fillable = [
        'vps_plan_pricing_id',
        'code',
        'discount_percent',
        'start_date',
        'end_date',
        'usage_limit',
        'used_count',
        'usage_limit'
    ];

    public function vpsPlanPricing()
    {
        return $this->belongsTo(VpsPlanPricing::class, 'vps_plan_pricing_id');
    }

    public function usages()
    {
        return $this->hasMany(VpsDiscountUsage::class);
    }
}
