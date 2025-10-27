<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\StatsServiceInterface;

class StatsController extends Controller
{
    protected $statsService;

    public function __construct(StatsServiceInterface $statsService)
    {
        $this->statsService = $statsService;
    }

    public function getCounts()
    {
        $data = $this->statsService->getCounts();
        return response()->json($data);
    }
}
