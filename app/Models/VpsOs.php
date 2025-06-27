<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VpsOs extends Model
{
    protected $table = 'vps_os';

    protected $fillable = [
        'name',
        'os_id',
        'image_url',
    ];

    public function vpsInstances()
    {
        return $this->hasMany(VpsInstance::class);
    }
}
