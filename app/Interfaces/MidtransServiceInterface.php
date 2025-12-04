<?php

namespace App\Interfaces;

interface MidtransServiceInterface
{
    public function getSnapToken(array $params): string;
}
