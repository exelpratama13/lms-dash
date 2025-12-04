<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'proof' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $courseId = $this->input('course_id');
            $pricingId = $this->input('pricing_id');
            $courseBatchId = $this->input('course_batch_id');

            $course = \App\Models\Course::find($courseId);

            if (!$course) {
                return; // Course validation akan error di rules() sudah
            }

            // Check if course has active batches
            $hasActiveBatch = $course->batches()
                ->where('end_date', '>=', now()->toDateString())
                ->exists();

            // Logic for Batch-Based Courses
            if ($hasActiveBatch) {
                if (!$courseBatchId) {
                    $validator->errors()->add(
                        'course_batch_id',
                        'This is a batch-based course. Please select a batch.'
                    );
                    return;
                }

                $batch = \App\Models\CourseBatch::find($courseBatchId);
                if (!$batch || $batch->course_id != $courseId) {
                    $validator->errors()->add(
                        'course_batch_id',
                        'Selected batch is not valid for this course.'
                    );
                    return;
                }

                if (now()->isAfter($batch->end_date)) {
                    $validator->errors()->add(
                        'course_batch_id',
                        'Selected batch has ended and is no longer available.'
                    );
                    return;
                }

                if ($batch->pricing_id !== $pricingId) {
                    $validator->errors()->add(
                        'pricing_id',
                        "Pricing mismatch. For this batch, the selected pricing is not valid."
                    );
                }
            } 
            // Logic for On-Demand (Subscription) Courses
            else { 
                if ($courseBatchId) {
                    $validator->errors()->add(
                        'course_batch_id',
                        'This is an on-demand course. Batch selection is not applicable.'
                    );
                    return;
                }

                $pricingExists = \App\Models\CoursePricing::where('course_id', $courseId)
                    ->where('pricing_id', $pricingId)
                    ->exists();

                if (!$pricingExists) {
                    $validator->errors()->add(
                        'pricing_id',
                        'This pricing is not available for this on-demand course.'
                    );
                }
            }
        });
    }
}
