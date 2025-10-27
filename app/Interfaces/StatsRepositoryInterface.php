<?php

namespace App\Interfaces;

interface StatsRepositoryInterface
{
    public function getCounts(): array;
}
