<?php
namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\TenantRepository;

class TenantService
{
    private $tenantRepositry;

    public function __construct(
        TenantRepository $tenantRepositry)
    {
        $this->tenantRepositry = $tenantRepositry;;
    }

    public function tenantForUserEmail($userEmail): array
    {
        return $this->tenantRepositry->findTenantByUserEmail($userEmail);
    }

}