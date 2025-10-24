<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; 
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'pricing_id' => ['required', 'integer', 'exists:pricings,id'], 
            'course_batch_id' => ['nullable', 'integer', 'exists:course_batches,id'],
            'payment_type' => ['required', 'string', 'max:50'],
            'proof' => ['nullable', 'string'], // Asumsi: ini adalah nama file atau URL
            'sub_total_amount' => ['required', 'integer', 'min:0'],
            'total_tax_amount' => ['required', 'integer', 'min:0'],
            'grand_total_amount' => ['required', 'integer', 'min:1'],
        ];
    }
}