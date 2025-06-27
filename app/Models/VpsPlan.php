<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpsPlan extends Model
{
    use HasFactory;

    protected $table = 'vps_plans';

    protected $fillable = [
        'name',
        'cpu',
        'ram',
        'storage',
        'bandwidth',
        'tagline',
        'description',
        'is_featured',
        'display_order',
    ];

    public function features()
    {
        return $this->hasMany(VpsPlanFeature::class);
    }

    public function pricing()
    {
        return $this->hasMany(VpsPlanPricing::class);
    }
}
