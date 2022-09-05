<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\PositionRepository;
use App\Repositories\RoleRepository;

class PositionService
{

    protected $positionRepo;

    public function __construct(PositionRepository $positionRepo)
    {
        $this->positionRepo = $positionRepo;
    }

    public function getByUnitId($filters, $tahun)
    {
        return $this->positionRepo->getPositionByUnitId($filters, $tahun)->get();
    }
    public function getOccupationsId($filters, $tahun)
    {
        return $this->positionRepo->getOccupationsId($filters, $tahun)->get();
    }
}
