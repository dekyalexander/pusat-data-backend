<?php

namespace App\Http\Controllers;

use App\Services\PositionService;
use Illuminate\Http\Request;
use App\Services\RoleService;
use App\Services\TahunPelajaranService;

class PositionController extends Controller
{

    protected $positionService;
    protected $tahunActive;
    protected $tahunPelajaranService;

    public function __construct(PositionService $positionService, TahunPelajaranService $tahunPelajaranService)
    {
        $this->positionService = $positionService;

        $this->tahunPelajaranService = $tahunPelajaranService;
    }

    public function getByUnitId(Request $request)
    {
        $data = $request->all();
        $this->tahunActive = $this->tahunPelajaranService->getTahunPelajaranActive()->first();
        return $this->positionService->getByUnitId($data, $this->tahunActive->name);
    }

    public function getOccupationsId(Request $request)
    {
        $data = $request->all();
        $this->tahunActive = $this->tahunPelajaranService->getTahunPelajaranActive()->first();
        return $this->positionService->getOccupationsId($data, $this->tahunActive->name);
    }
}
