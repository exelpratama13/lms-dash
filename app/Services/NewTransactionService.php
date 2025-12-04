<?php

namespace App\Services;

use App\Interfaces\NewTransactionRepositoryInterface;
use App\Interfaces\PricingRepositoryInterface;
use App\Interfaces\CourseRepositoryInterface;
use App\Interfaces\MidtransServiceInterface; // Import MidtransServiceInterface
use App\Interfaces\NewTransactionServiceInterface; // Add this line
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewTransactionService implements NewTransactionServiceInterface
{
    protected $newTransactionRepository;
    // Remove midtransService, pricingRepository, courseRepository from service
    // as they are now injected into the repository

    public function __construct(
        NewTransactionRepositoryInterface $newTransactionRepository
        // Remove midtransService, pricingRepository, courseRepository from constructor
    ) {
        $this->newTransactionRepository = $newTransactionRepository;
    }

    public function createMidtransTransaction(array $data): Transaction
    {
        return $this->newTransactionRepository->processMidtransTransaction($data);
    }
}
