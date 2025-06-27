<?php

namespace App\Services;

use App\Libraries\Virtualizor\VirtualizorAdminAPI;

class VirtualizorService
{
    protected $admin;

    public function __construct()
    {
        $this->admin = new VirtualizorAdminAPI(
            config('services.virtualizor.ip'),
            config('services.virtualizor.key'),
            config('services.virtualizor.pass')
        );
    }

    public function listIPs()
    {
        $ips = $this->admin->ips();
        $available_ips = [];
        foreach ($ips['ips'] as $ip) {
            if ($ip['vpsid'] === "0") {
                $available_ips[] = $ip['ip'];
            }
        }

        return (array) $available_ips;
    }

    public function addVPS(array $config)
    {
        $response = $this->admin->addvs($config);
        return (object) $response;
    }
}
