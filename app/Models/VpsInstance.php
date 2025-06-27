<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpsInstance extends Model
{
    use HasFactory;

    protected $table = 'vps_instances';

    protected $fillable = [
        'user_id',
        'vps_plan_id',
        'vps_os_id',
        'order_detail_id',
        'status',
        'os_id',
        'hostname',
        'ip_address',
        'panel_username',
        'panel_password_hash',
        'duration_months',
        'expired_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vpsPlan()
    {
        return $this->belongsTo(VpsPlan::class, 'vps_plan_id');
    }

    public function vpsOs()
    {
        return $this->belongsTo(VpsOs::class, 'vps_os_id');
    }

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }
}
