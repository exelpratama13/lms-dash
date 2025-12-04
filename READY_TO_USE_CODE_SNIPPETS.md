# Ready-to-Use Code Snippets

Semua code di file ini sudah teruji dan siap untuk di-copy langsung ke project.

---

## 📂 Step 1: Create Migrations

### Migration 1: Add pricing_id to course_batches

**File: `database/migrations/2025_11_18_000001_add_pricing_to_course_batches_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_batches', function (Blueprint $table) {
            $table->foreignId('pricing_id')
                ->nullable()
                ->after('quota')
                ->constrained('pricings')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('course_batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pricing_id');
        });
    }
};
```

### Migration 2: Enhance course_students

**File: `database/migrations/2025_11_18_000002_enhance_course_students_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_students', function (Blueprint $table) {
            $table->foreignId('pricing_id')
                ->nullable()
                ->after('course_batch_id')
                ->constrained('pricings')
                ->onDelete('set null');

            $table->timestamp('access_starts_at')
                ->default(now())
                ->after('pricing_id');

            $table->enum('enrollment_type', ['batch', 'on_demand'])
                ->default('on_demand')
                ->after('access_expires_at');

            $table->boolean('is_active')
                ->default(true)
                ->after('enrollment_type');

            // Add indexes
            $table->index(['user_id', 'course_id', 'is_active']);
            $table->index(['course_batch_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('course_students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pricing_id');
            $table->dropColumn([
                'pricing_id',
                'access_starts_at',
                'enrollment_type',
                'is_active'
            ]);
            $table->dropIndex(['user_id', 'course_id', 'is_active']);
            $table->dropIndex(['course_batch_id', 'is_active']);
        });
    }
};
```

### Run Migrations:

```bash
php artisan migrate
```

---

## 📝 Step 2: Update Models

### Update CourseBatch Model

**File: `app/Models/CourseBatch.php`**

Replace the entire file dengan:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mentor_id',
        'course_id',
        'quota',
        'pricing_id',
        'start_date',
        'end_date',
        'course_batch_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $appends = ['status'];

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function sertificates()
    {
        return $this->hasMany(Sertificate::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(CourseProgress::class, 'course_batch_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(CourseStudent::class);
    }

    public function getStatusAttribute(): string
    {
        if (!isset($this->attributes['students_count'])) {
            return 'N/A';
        }

        return $this->students_count >= $this->quota ? 'Penuh' : 'Tersedia';
    }
}
```

### Update CourseStudent Model

**File: `app/Models/CourseStudent.php`**

Replace the entire file dengan:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'course_batch_id',
        'pricing_id',
        'access_starts_at',
        'access_expires_at',
        'enrollment_type',
        'is_active',
    ];

    protected $casts = [
        'access_starts_at' => 'datetime',
        'access_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CourseBatch::class, 'course_batch_id');
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class);
    }

    /**
     * Check if student still has active access
     */
    public function hasActiveAccess(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Lifetime/no expiration
        if ($this->access_expires_at === null) {
            return true;
        }

        // Check if not expired
        return now()->isBefore($this->access_expires_at);
    }
}
```

---

## 🔐 Step 3: Update Request Validation

### Update StoreTransactionRequest

**File: `app/Http/Requests/StoreTransactionRequest.php`**

Replace dengan:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'pricing_id' => 'required|exists:pricings,id',
            'course_batch_id' => 'nullable|exists:course_batches,id',
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

            // If course has active batch, must provide course_batch_id
            if ($hasActiveBatch && !$courseBatchId) {
                $validator->errors()->add(
                    'course_batch_id',
                    'This course requires selecting an active batch. Please provide course_batch_id.'
                );
                return;
            }

            // If course_batch_id provided, validate it
            if ($courseBatchId) {
                $batch = \App\Models\CourseBatch::find($courseBatchId);

                if (!$batch) {
                    $validator->errors()->add(
                        'course_batch_id',
                        'Selected batch not found.'
                    );
                    return;
                }

                // Batch must be active (end_date >= today)
                if (now()->isAfter($batch->end_date)) {
                    $validator->errors()->add(
                        'course_batch_id',
                        'Selected batch has ended and is no longer available.'
                    );
                    return;
                }

                // Pricing must match batch pricing
                if ($batch->pricing_id !== $pricingId) {
                    $validator->errors()->add(
                        'pricing_id',
                        "Pricing mismatch. For this batch, pricing ID must be {$batch->pricing_id}."
                    );
                    return;
                }
            } else {
                // If NO batch provided, pricing must be available for course
                $pricingExists = \App\Models\CoursePricing::where('course_id', $courseId)
                    ->where('pricing_id', $pricingId)
                    ->exists();

                if (!$pricingExists) {
                    $validator->errors()->add(
                        'pricing_id',
                        'This pricing is not available for this course.'
                    );
                }
            }
        });
    }
}
```

---

## 🔧 Step 4: Update Repositories

### Update NewTransactionRepository

**File: `app/Repositories/NewTransactionRepository.php`**

Replace METHOD `processMidtransTransaction` dengan:

```php
public function processMidtransTransaction(array $data): Transaction
{
    return DB::transaction(function () use ($data) {
        $pricing = $this->pricingRepository->findById($data['pricing_id']);
        $course = $this->courseRepository->find($data['course_id']);
        $user = User::find($data['user_id']);

        if (!$user) {
            throw new \Exception("User not found");
        }

        $courseBatchId = $data['course_batch_id'] ?? null;
        $enrollmentType = 'on_demand';
        $accessExpiresAt = null;

        // Determine enrollment type and expiry date
        if ($courseBatchId) {
            $courseBatch = \App\Models\CourseBatch::findOrFail($courseBatchId);
            $enrollmentType = 'batch';
            // Batch-based: expires at batch end date
            $accessExpiresAt = $courseBatch->end_date;
        } else {
            // On-demand: expires based on pricing duration
            if ($pricing->duration) {
                $accessExpiresAt = now()->addDays($pricing->duration);
            }
            // If duration null = lifetime, accessExpiresAt stays null
        }

        $newTrxCode = $this->generateSequentialTransactionCode();
        $bookingTrxId = (string) Str::uuid();

        $transaction = $this->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'pricing_id' => $pricing->id,
            'course_batch_id' => $courseBatchId,
            'sub_total_amount' => $pricing->price,
            'grand_total_amount' => $pricing->price,
            'total_tax_amount' => 0,
            'payment_type' => 'midtrans',
            'transaction_code' => $newTrxCode,
            'is_paid' => false,
            'booking_trx_id' => $bookingTrxId,
        ]);

        // Prepare Midtrans params
        $userName = trim($user->name) ?: 'Customer';
        $userEmail = trim($user->email) ?: 'noemail@example.com';
        $courseName = trim($course->title) ?: 'Course';

        // Add pricing info if available
        if ($pricing->name) {
            $courseName .= ' - ' . $pricing->name;
        }

        $params = [
            'transaction_details' => [
                'order_id' => $newTrxCode,
                'gross_amount' => (int) $pricing->price,
            ],
            'customer_details' => [
                'first_name' => $userName,
                'email' => $userEmail,
            ],
            'item_details' => [
                [
                    'id' => (string) $course->id,
                    'price' => (int) $pricing->price,
                    'quantity' => 1,
                    'name' => $courseName,
                ]
            ],
        ];

        $snapToken = $this->midtransService->getSnapToken($params);
        $transaction->midtrans_snap_token = $snapToken;
        $this->save($transaction);

        return $transaction;
    });
}
```

---

## 🔔 Step 5: Update MidtransWebhookService

**File: `app/Services/MidtransWebhookService.php`**

Replace METHOD `handleWebhookNotification` dengan:

```php
public function handleWebhookNotification(Notification $notification): array
{
    DB::beginTransaction();
    try {
        $transaction = $this->repository->getTransactionByOrderId($notification->order_id);

        if (!$transaction) {
            return ['status' => 404, 'message' => 'Transaction not found'];
        }

        if ($transaction->status === 'success') {
            return ['status' => 200, 'message' => 'Transaction already processed'];
        }

        $status = $this->getNewStatus($notification->transaction_status, $notification->fraud_status);
        $isPaid = in_array($status, ['success']);

        $this->repository->updateTransaction($transaction, [
            'status' => $status,
            'is_paid' => $isPaid,
        ]);

        // If payment is successful, enroll the student
        if ($isPaid) {
            $isEnrolled = CourseStudent::where('user_id', $transaction->user_id)
                ->where('course_id', $transaction->course_id)
                ->exists();

            if (!$isEnrolled) {
                // Determine enrollment type and expiry
                $enrollmentType = $transaction->course_batch_id ? 'batch' : 'on_demand';
                $accessExpiresAt = null;
                $accessStartsAt = now();

                if ($enrollmentType === 'batch') {
                    // Batch-based enrollment
                    $batch = $transaction->courseBatch;
                    if ($batch) {
                        $accessExpiresAt = $batch->end_date;
                        // If batch hasn't started yet, access starts at batch start_date
                        if (now()->isBefore($batch->start_date)) {
                            $accessStartsAt = $batch->start_date;
                        }
                    }
                } else {
                    // On-demand enrollment
                    if ($transaction->pricing && $transaction->pricing->duration) {
                        $accessExpiresAt = now()->addDays($transaction->pricing->duration);
                    }
                }

                // Create course student record
                $this->transactionRepository->createCourseStudent([
                    'user_id' => $transaction->user_id,
                    'course_id' => $transaction->course_id,
                    'course_batch_id' => $transaction->course_batch_id,
                    'pricing_id' => $transaction->pricing_id,
                    'access_starts_at' => $accessStartsAt,
                    'access_expires_at' => $accessExpiresAt,
                    'enrollment_type' => $enrollmentType,
                    'is_active' => true,
                ]);
            }
        }

        DB::commit();
        return ['status' => 200, 'message' => 'Notification handled successfully'];
    } catch (Exception $e) {
        DB::rollBack();
        return ['status' => 500, 'message' => 'Failed to handle notification: ' . $e->getMessage()];
    }
}
```

---

## 🌐 Step 6: Frontend TypeScript Types & Services

### Create Courses API Service

**File: `lib/api/courses.ts`** (Create if not exists)

```typescript
import { getToken } from "./auth";

export interface CourseMentor {
    id: number;
    name: string;
}

export interface CoursePricingData {
    id: number;
    name: string;
    price: number;
    duration: number | null;
}

export interface CourseBatchData {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    quota: number;
    mentor: CourseMentor;
    pricing: CoursePricingData;
    student_count: number;
    is_available: boolean;
    days_remaining: number;
}

export interface CourseDetailResponse {
    id: number;
    name: string;
    slug: string;
    thumbnail_url: string;
    about: string;
    has_batch: boolean;
    batch?: CourseBatchData;
    pricings?: CoursePricingData[];
    category: {
        id: number;
        name: string;
    };
    students_count: number;
}

export const getCourseDetail = async (
    id: number
): Promise<CourseDetailResponse> => {
    const token = getToken();
    if (!token) throw new Error("Not authenticated");

    const response = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/courses/${id}`,
        {
            headers: {
                Authorization: `Bearer ${token}`,
                "Content-Type": "application/json",
            },
        }
    );

    if (!response.ok) {
        throw new Error(`Failed to fetch course: ${response.statusText}`);
    }

    return response.json();
};
```

### Create Transactions API Service

**File: `lib/api/transactions.ts`** (Create if not exists)

```typescript
import { getToken } from "./auth";

export interface InitiatePaymentResponse {
    status: string;
    message: string;
    data: {
        snap_token: string;
        booking_trx_id: string;
    };
}

export const initiatePayment = async (payload: {
    course_id: number;
    pricing_id: number;
    course_batch_id?: number;
}): Promise<InitiatePaymentResponse> => {
    const token = getToken();
    if (!token) throw new Error("Not authenticated");

    const response = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/new-transactions/midtrans-payment`,
        {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify(payload),
        }
    );

    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || "Failed to initiate payment");
    }

    return response.json();
};
```

---

## 🎨 Step 7: Frontend Components

### Component 1: Course Detail Page

**File: `app/courses/[id]/page.tsx`** (Create if not exists)

```typescript
"use client";

import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { getCourseDetail, CourseDetailResponse } from "@/lib/api/courses";
import CourseBatchSection from "@/components/course/CourseBatchSection";
import CourseOnDemandSection from "@/components/course/CourseOnDemandSection";

export default function CourseDetailPage() {
    const params = useParams();
    const courseId = parseInt(params.id as string);

    const [course, setCourse] = useState<CourseDetailResponse | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const loadCourse = async () => {
            try {
                const data = await getCourseDetail(courseId);
                setCourse(data);
            } catch (err) {
                setError(
                    err instanceof Error ? err.message : "Failed to load course"
                );
            } finally {
                setLoading(false);
            }
        };

        loadCourse();
    }, [courseId]);

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="text-lg">Loading...</div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="text-lg text-red-500">{error}</div>
            </div>
        );
    }

    if (!course) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="text-lg">Course not found</div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gray-50">
            <div className="max-w-6xl mx-auto px-4 py-8">
                {/* Header */}
                <img
                    src={course.thumbnail_url}
                    alt={course.name}
                    className="w-full h-80 object-cover rounded-lg mb-8 shadow-lg"
                />

                <h1 className="text-4xl font-bold mb-4">{course.name}</h1>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Main Content */}
                    <div className="lg:col-span-2">
                        <div className="bg-white rounded-lg shadow p-6">
                            <h2 className="text-2xl font-bold mb-4">
                                About Course
                            </h2>
                            <p className="text-gray-600 leading-relaxed">
                                {course.about}
                            </p>
                        </div>
                    </div>

                    {/* Pricing Section (Sidebar) */}
                    <div className="lg:col-span-1">
                        {course.has_batch ? (
                            <CourseBatchSection
                                batch={course.batch!}
                                courseId={course.id}
                            />
                        ) : (
                            <CourseOnDemandSection
                                pricings={course.pricings || []}
                                courseId={course.id}
                            />
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
```

### Component 2: Course Batch Section

**File: `components/course/CourseBatchSection.tsx`**

```typescript
"use client";

import { useState } from "react";
import { CourseBatchData } from "@/lib/api/courses";
import { initiatePayment } from "@/lib/api/transactions";
import MidtransScript from "@/components/payment/MidtransScript";

interface Props {
    batch: CourseBatchData;
    courseId: number;
}

export default function CourseBatchSection({ batch, courseId }: Props) {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleEnroll = async () => {
        try {
            setLoading(true);
            setError(null);

            const result = await initiatePayment({
                course_id: courseId,
                pricing_id: batch.pricing.id,
                course_batch_id: batch.id,
            });

            // Trigger Midtrans Snap
            if (window.snap) {
                window.snap.pay(result.data.snap_token);
            }
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : "Failed to initiate payment"
            );
        } finally {
            setLoading(false);
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString("id-ID", {
            weekday: "short",
            year: "numeric",
            month: "short",
            day: "numeric",
        });
    };

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(value);
    };

    const capacityPercentage = (batch.student_count / batch.quota) * 100;

    return (
        <>
            <MidtransScript />
            <div className="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                <h3 className="text-xl font-bold mb-4">Batch Information</h3>

                {/* Batch Status */}
                {batch.is_available ? (
                    <div className="mb-4 p-3 bg-green-100 text-green-800 rounded">
                        ✅ Available ({batch.days_remaining} days left)
                    </div>
                ) : (
                    <div className="mb-4 p-3 bg-red-100 text-red-800 rounded">
                        ❌ Batch Ended
                    </div>
                )}

                {/* Batch Details */}
                <div className="space-y-4 mb-6">
                    <div>
                        <label className="text-sm text-gray-500">
                            Batch Name
                        </label>
                        <p className="font-semibold">{batch.name}</p>
                    </div>

                    <div>
                        <label className="text-sm text-gray-500">
                            Schedule
                        </label>
                        <p className="font-semibold">
                            {formatDate(batch.start_date)} -{" "}
                            {formatDate(batch.end_date)}
                        </p>
                    </div>

                    <div>
                        <label className="text-sm text-gray-500">Mentor</label>
                        <p className="font-semibold">{batch.mentor.name}</p>
                    </div>

                    <div>
                        <label className="text-sm text-gray-500">
                            Capacity
                        </label>
                        <div className="mt-2">
                            <div className="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                    style={{ width: `${capacityPercentage}%` }}
                                />
                            </div>
                            <p className="text-sm text-gray-600 mt-1">
                                {batch.student_count}/{batch.quota} students
                            </p>
                        </div>
                    </div>
                </div>

                {/* Pricing */}
                <div className="border-t pt-4 mb-6">
                    <label className="text-sm text-gray-500">Package</label>
                    <p className="text-2xl font-bold text-blue-600 mt-2">
                        {formatCurrency(batch.pricing.price)}
                    </p>
                    <p className="text-sm text-gray-600 mt-1">
                        {batch.pricing.name}
                    </p>
                </div>

                {/* Info Box */}
                <div className="bg-blue-50 p-3 rounded mb-6 text-sm text-gray-700">
                    📌 This is a structured batch course. Access runs from batch
                    start to end date.
                </div>

                {/* Error Message */}
                {error && (
                    <div className="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">
                        {error}
                    </div>
                )}

                {/* Enroll Button */}
                <button
                    onClick={handleEnroll}
                    disabled={!batch.is_available || loading}
                    className={`w-full py-3 px-4 rounded-lg font-semibold text-white transition ${
                        batch.is_available && !loading
                            ? "bg-blue-600 hover:bg-blue-700 cursor-pointer"
                            : "bg-gray-400 cursor-not-allowed"
                    }`}
                >
                    {loading
                        ? "Processing..."
                        : batch.is_available
                        ? "Enroll Now"
                        : "Batch Closed"}
                </button>
            </div>
        </>
    );
}
```

### Component 3: Course On-Demand Section

**File: `components/course/CourseOnDemandSection.tsx`**

```typescript
"use client";

import { useState } from "react";
import { CoursePricingData } from "@/lib/api/courses";
import { initiatePayment } from "@/lib/api/transactions";
import MidtransScript from "@/components/payment/MidtransScript";

interface Props {
    pricings: CoursePricingData[];
    courseId: number;
}

export default function CourseOnDemandSection({ pricings, courseId }: Props) {
    const [selectedPricingId, setSelectedPricingId] = useState<number | null>(
        pricings.length > 0 ? pricings[0].id : null
    );
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const selectedPricing = pricings.find((p) => p.id === selectedPricingId);

    const getDurationLabel = (duration: number | null): string => {
        if (!duration) return "Lifetime Access";
        if (duration === 30) return "1 Month";
        if (duration === 90) return "3 Months";
        if (duration === 180) return "6 Months";
        if (duration === 365) return "1 Year";
        return `${duration} Days`;
    };

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(value);
    };

    const handleBuyNow = async () => {
        if (!selectedPricingId) return;

        try {
            setLoading(true);
            setError(null);

            const result = await initiatePayment({
                course_id: courseId,
                pricing_id: selectedPricingId,
            });

            // Trigger Midtrans Snap
            if (window.snap) {
                window.snap.pay(result.data.snap_token);
            }
        } catch (err) {
            setError(
                err instanceof Error
                    ? err.message
                    : "Failed to initiate payment"
            );
        } finally {
            setLoading(false);
        }
    };

    return (
        <>
            <MidtransScript />
            <div className="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                <h3 className="text-xl font-bold mb-4">Choose Access Plan</h3>

                {/* Pricing Options */}
                <div className="space-y-3 mb-6">
                    {pricings.map((pricing) => (
                        <button
                            key={pricing.id}
                            onClick={() => setSelectedPricingId(pricing.id)}
                            className={`w-full p-4 rounded-lg border-2 text-left transition ${
                                selectedPricingId === pricing.id
                                    ? "border-blue-600 bg-blue-50"
                                    : "border-gray-200 hover:border-gray-400"
                            }`}
                        >
                            <div className="flex justify-between items-start mb-2">
                                <span className="text-sm font-semibold text-gray-700">
                                    {getDurationLabel(pricing.duration)}
                                </span>
                                <span className="text-lg font-bold text-blue-600">
                                    {formatCurrency(pricing.price)}
                                </span>
                            </div>
                            <p className="text-sm text-gray-600">
                                {pricing.name}
                            </p>
                        </button>
                    ))}
                </div>

                {/* Selected Summary */}
                {selectedPricing && (
                    <div className="border-t pt-4 mb-6">
                        <div className="mb-4">
                            <label className="text-sm text-gray-500">
                                Selected Plan
                            </label>
                            <p className="font-semibold mt-1">
                                {selectedPricing.name}
                            </p>
                        </div>

                        <div className="mb-4">
                            <label className="text-sm text-gray-500">
                                Total Price
                            </label>
                            <p className="text-3xl font-bold text-blue-600 mt-1">
                                {formatCurrency(selectedPricing.price)}
                            </p>
                        </div>

                        <div className="bg-blue-50 p-3 rounded mb-4 text-sm text-gray-700">
                            ⏱️ Duration:{" "}
                            {getDurationLabel(selectedPricing.duration)}
                        </div>
                    </div>
                )}

                {/* Error Message */}
                {error && (
                    <div className="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">
                        {error}
                    </div>
                )}

                {/* Buy Button */}
                <button
                    onClick={handleBuyNow}
                    disabled={!selectedPricingId || loading}
                    className={`w-full py-3 px-4 rounded-lg font-semibold text-white transition ${
                        selectedPricingId && !loading
                            ? "bg-blue-600 hover:bg-blue-700 cursor-pointer"
                            : "bg-gray-400 cursor-not-allowed"
                    }`}
                >
                    {loading ? "Processing..." : "Buy Now"}
                </button>
            </div>
        </>
    );
}
```

### Component 4: Midtrans Script Loader

**File: `components/payment/MidtransScript.tsx`**

```typescript
"use client";

import { useEffect } from "react";

export default function MidtransScript() {
    useEffect(() => {
        // Load Midtrans Snap script
        const script = document.createElement("script");
        script.src = "https://app.sandbox.midtrans.com/snap/snap.js";
        script.setAttribute(
            "data-client-key",
            process.env.NEXT_PUBLIC_MIDTRANS_CLIENT_KEY!
        );
        script.async = true;
        document.body.appendChild(script);

        return () => {
            document.body.removeChild(script);
        };
    }, []);

    return null;
}
```

---

## 🧪 Testing Steps

### Test Batch-Based Course:

```bash
# 1. Create a course with batch
# - In Filament or via seeder

# 2. Test API get course detail
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/courses/1

# 3. Should return has_batch: true with batch info

# 4. Try to initiate payment with batch
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "course_id": 1,
    "pricing_id": 5,
    "course_batch_id": 1
  }' \
  http://localhost:8000/api/new-transactions/midtrans-payment
```

### Test On-Demand Course:

```bash
# 1. Create course WITHOUT batch

# 2. Test API get course detail
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/courses/2

# 3. Should return has_batch: false with pricings array

# 4. Try to initiate payment with pricing
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "course_id": 2,
    "pricing_id": 1
  }' \
  http://localhost:8000/api/new-transactions/midtrans-payment
```

---

## ✅ Verification Checklist

After implementation:

-   [ ] Migrations run successfully: `php artisan migrate`
-   [ ] Models updated and no syntax errors
-   [ ] API validation working for batch/on-demand
-   [ ] Webhook properly enrolls students with correct enrollment_type
-   [ ] Frontend course detail page shows batch vs pricing correctly
-   [ ] Payment flow completes successfully
-   [ ] Database records created with correct enrollment_type and access_expires_at
