<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpsPlanFeature extends Model
{
    use HasFactory;

    protected $table = 'vps_plan_features';

    protected $fillable = [
        'vps_plan_id',
        'content',
        'highlight',
        'display_order',
    ];

    public function vpsPlan()
    {
        return $this->belongsTo(VpsPlan::class, 'vps_plan_id');
    }
}
