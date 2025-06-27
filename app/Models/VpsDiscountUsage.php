<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpsDiscountUsage extends Model
{
    use HasFactory;

    protected $table = 'vps_discount_usages';

    protected $fillable = [
        'vps_discount_code_id',
        'user_id',
        'order_id',
        'used_at',
    ];

    public function discountCode()
    {
        return $this->belongsTo(VpsDiscountCode::class, 'vps_discount_code_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
