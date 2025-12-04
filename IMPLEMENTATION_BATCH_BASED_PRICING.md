\# Implementation Guide: Batch-Based Pricing & Flexible On-Demand

## 🎯 Requirements Summary

```
Requirement Anda:

✅ Course DENGAN Batch:
   - Course memiliki 1 batch (atau multiple batch)
   - Setiap batch terikat 1 pricing package
   - Student hanya bisa membeli dengan pricing batch tersebut
   - access_expires_at = batch.end_date (bukan dari pricing duration)
   - access_starts_at = batch.start_date (atau dari pembelian, whichever later)

✅ Course TANPA Batch:
   - Course tidak memiliki batch terstruktur
   - Pricing bisa dipilih sesuai kebutuhan (fleksibel)
   - access_expires_at = now() + pricing.duration
   - On-demand, dapat dibeli kapan saja

✅ Implementation Level:
   - Backend: API harus deteksi course punya batch atau tidak
   - Frontend: UI disesuaikan berdasarkan batch availability
```

---

## 🔄 Business Logic Flow

### Flow 1: Course WITH Batch

```
┌────────────────────────────────────┐
│ Course dibuat dengan Batch:        │
│ - name: "Web Development 101"      │
│ - start_date: 2025-12-01           │
│ - end_date: 2025-12-31             │
│ - pricing_id: 5 (terhubung batch) │
└────────────────┬───────────────────┘
                 │
                 ▼
         ┌──────────────────┐
         │ Student Browse   │
         │ Course Detail    │
         └────────┬─────────┘
                  │
                  ▼
      ┌───────────────────────────┐
      │ Backend Return:           │
      │ {                         │
      │   has_batch: true,        │
      │   batch: {                │
      │     id: 1,                │
      │     start_date: '...',    │
      │     end_date: '...',      │
      │   },                      │
      │   pricing: {              │
      │     id: 5,                │
      │     name: "Full Package", │
      │     price: 500000         │
      │   }                       │
      │ }                         │
      └───────────┬───────────────┘
                  │
                  ▼
      ┌─────────────────────────┐
      │ Frontend Render:        │
      │ ✅ "Enroll Now" button  │
      │    (fixed pricing)      │
      │ ✅ Batch info display   │
      │ ✅ NO pricing selection │
      └──────────┬──────────────┘
                 │
                 ▼
    ┌──────────────────────────┐
    │ Student Click "Enroll"   │
    │ Request: {               │
    │   course_id: 1,          │
    │   pricing_id: 5,         │
    │   enrollment_type: 'batch'
    │ }                        │
    └──────────┬───────────────┘
               │
               ▼
    ┌──────────────────────────┐
    │ Backend Validate:        │
    │ ✅ Course has batch      │
    │ ✅ Pricing matches batch │
    │ ✅ Batch belum expired   │
    └──────────┬───────────────┘
               │
               ▼
    ┌──────────────────────────────┐
    │ Create Transaction           │
    │ access_expires_at =          │
    │ batch.end_date               │
    │ (not pricing.duration)       │
    │                              │
    │ Get Snap Token from Midtrans │
    └──────────┬───────────────────┘
               │
               ▼
    ┌──────────────────────┐
    │ Return Snap Token    │
    │ & Payment URL        │
    └──────────┬───────────┘
               │
               ▼
    ┌──────────────────────────┐
    │ Student Complete Payment │
    │ via Snap                 │
    └──────────┬───────────────┘
               │
               ▼
    ┌────────────────────────────────┐
    │ Midtrans Webhook Callback      │
    │ - Update transaction status    │
    │ - Create CourseStudent record: │
    │   - access_starts_at: now()    │
    │   - access_expires_at:         │
    │     batch.end_date             │
    │   - course_batch_id: batch_id  │
    │   - enrollment_type: 'batch'   │
    └────────────────────────────────┘
```

### Flow 2: Course WITHOUT Batch

```
┌──────────────────────────────┐
│ Course tanpa Batch:          │
│ - name: "Python Self-Paced" │
│ - No batch created          │
└────────────────┬─────────────┘
                 │
                 ▼
         ┌──────────────────┐
         │ Student Browse   │
         │ Course Detail    │
         └────────┬─────────┘
                  │
                  ▼
      ┌───────────────────────────┐
      │ Backend Return:           │
      │ {                         │
      │   has_batch: false,       │
      │   pricings: [             │
      │     {id: 1, name: '1 Mo', │
      │      price: 50000,        │
      │      duration: 30},       │
      │     {id: 2, name: '3 Mo', │
      │      price: 120000,       │
      │      duration: 90},       │
      │     {id: 3, name:'Forever'
      │      price: 300000,       │
      │      duration: null}      │
      │   ]                       │
      │ }                         │
      └───────────┬───────────────┘
                  │
                  ▼
      ┌──────────────────────────┐
      │ Frontend Render:         │
      │ ✅ Pricing selector      │
      │    (radio/dropdown)      │
      │ ✅ Duration info         │
      │ ✅ Price comparison      │
      │ ✅ NO batch info         │
      └──────────┬───────────────┘
                 │
                 ▼
    ┌──────────────────────────────┐
    │ Student Select Pricing       │
    │ Then Click "Buy"             │
    │ Request: {                   │
    │   course_id: 2,              │
    │   pricing_id: 2,             │
    │   enrollment_type: 'on_demand'
    │ }                            │
    └──────────┬───────────────────┘
               │
               ▼
    ┌───────────────────────────┐
    │ Backend Validate:         │
    │ ✅ Course has NO batch    │
    │ ✅ Pricing available for  │
    │    this course            │
    └──────────┬────────────────┘
               │
               ▼
    ┌──────────────────────────────┐
    │ Create Transaction           │
    │ access_expires_at =          │
    │ now() + pricing.duration     │
    │                              │
    │ Get Snap Token from Midtrans │
    └──────────┬───────────────────┘
               │
               ▼
    ┌──────────────────────┐
    │ Return Snap Token    │
    │ & Payment URL        │
    └──────────┬───────────┘
               │
               ▼
    ┌────────────────────────────────┐
    │ Student Complete Payment       │
    │ via Snap                       │
    └──────────┬────────────────────┘
               │
               ▼
    ┌────────────────────────────────┐
    │ Midtrans Webhook Callback      │
    │ - Update transaction status    │
    │ - Create CourseStudent record: │
    │   - access_starts_at: now()    │
    │   - access_expires_at:         │
    │     now() + pricing.duration   │
    │   - course_batch_id: null      │
    │   - enrollment_type: 'on_demand'
    └────────────────────────────────┘
```

---

## 📦 Database Schema Changes Needed

### Add to course_batches table:

```sql
ALTER TABLE course_batches ADD COLUMN pricing_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE course_batches ADD FOREIGN KEY (pricing_id) REFERENCES pricings(id) ON DELETE SET NULL;
```

### Modify course_students table (recommended):

```sql
ALTER TABLE course_students ADD COLUMN pricing_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE course_students ADD COLUMN access_starts_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE course_students ADD COLUMN enrollment_type ENUM('batch', 'on_demand') DEFAULT 'on_demand';
ALTER TABLE course_students ADD COLUMN is_active BOOLEAN DEFAULT TRUE;

-- Add foreign key for pricing
ALTER TABLE course_students ADD FOREIGN KEY (pricing_id) REFERENCES pricings(id) ON DELETE SET NULL;

-- Add indexes for performance
CREATE INDEX idx_course_students_user_course_active ON course_students(user_id, course_id, is_active);
CREATE INDEX idx_course_students_batch_active ON course_students(course_batch_id, is_active);
```

---

## 💻 Backend Implementation

### 1. Migration untuk add pricing_id ke course_batches

File: `database/migrations/2025_11_18_XXXXXX_add_pricing_to_course_batches_table.php`

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
            $table->dropForeignKeyIfExists(['pricing_id']);
            $table->dropColumn('pricing_id');
        });
    }
};
```

### 2. Migration untuk enhance course_students

File: `database/migrations/2025_11_18_XXXXXX_enhance_course_students_table.php`

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
            $table->dropForeignKeyIfExists(['pricing_id']);
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

### 3. Update CourseBatch Model

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
        'pricing_id',  // NEW
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

    public function pricing(): BelongsTo  // NEW
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

### 4. Update CourseStudent Model

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
        'pricing_id',           // NEW
        'access_starts_at',     // NEW
        'access_expires_at',
        'enrollment_type',      // NEW
        'is_active',            // NEW
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

    public function pricing(): BelongsTo  // NEW
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

## 🔌 Backend API Changes

### A. GET /api/courses/{id} - Return pricing info dan batch info

**Response: Course WITH Batch**

```json
{
    "id": 1,
    "name": "Web Development 101",
    "slug": "web-development-101",
    "thumbnail_url": "...",
    "about": "...",
    "has_batch": true,
    "batch": {
        "id": 1,
        "name": "Batch A - December 2025",
        "start_date": "2025-12-01",
        "end_date": "2025-12-31",
        "quota": 30,
        "mentor": { "id": 1, "name": "John Doe" },
        "pricing": {
            "id": 5,
            "name": "Full Web Package",
            "price": 500000,
            "duration": null // null = batch-based, tidak dari duration
        },
        "student_count": 15,
        "is_available": true,
        "days_remaining": 43
    },
    "category": { "id": 1, "name": "Programming" },
    "students_count": 0
}
```

**Response: Course WITHOUT Batch**

```json
{
    "id": 2,
    "name": "Python Self-Paced",
    "slug": "python-self-paced",
    "thumbnail_url": "...",
    "about": "...",
    "has_batch": false,
    "pricings": [
        {
            "id": 1,
            "name": "1 Month Access",
            "price": 50000,
            "duration": 30
        },
        {
            "id": 2,
            "name": "3 Months Access",
            "price": 120000,
            "duration": 90
        },
        {
            "id": 3,
            "name": "Lifetime Access",
            "price": 300000,
            "duration": null
        }
    ],
    "category": { "id": 1, "name": "Programming" },
    "students_count": 0
}
```

### B. POST /api/new-transactions/midtrans-payment - Updated to handle both types

**Request Body (Sama untuk kedua jenis):**

```json
{
    "course_id": 1,
    "pricing_id": 5,
    "course_batch_id": 1 // OPTIONAL - jika batch, harus diisi
}
```

**Backend Validasi Logic:**

```php
// Di StoreTransactionRequest validation:

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

        $course = Course::find($courseId);
        $hasActiveBatch = $course->batches()
            ->where('end_date', '>=', now()->toDateString())
            ->exists();

        // If course has active batch, must provide course_batch_id
        if ($hasActiveBatch && !$courseBatchId) {
            $validator->errors()->add(
                'course_batch_id',
                'This course requires a batch selection.'
            );
        }

        // If course_batch_id provided, must be active
        if ($courseBatchId) {
            $batch = CourseBatch::find($courseBatchId);
            if (!$batch || now()->isAfter($batch->end_date)) {
                $validator->errors()->add(
                    'course_batch_id',
                    'Selected batch is no longer active.'
                );
            }

            // Pricing must match batch pricing
            if ($batch->pricing_id !== $pricingId) {
                $validator->errors()->add(
                    'pricing_id',
                    'Pricing must match the selected batch.'
                );
            }
        } else {
            // If NO batch, pricing must be available for course
            $pricingExists = CoursePricing::where('course_id', $courseId)
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
```

### C. Update NewTransactionRepository::processMidtransTransaction()

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
            $courseBatch = CourseBatch::findOrFail($courseBatchId);
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

### D. Update MidtransWebhookService::handleWebhookNotification()

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
                        $accessStartsAt = max(now(), $batch->start_date);
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

private function getNewStatus(string $transactionStatus, ?string $fraudStatus): string
{
    if ($transactionStatus == 'capture') {
        if ($fraudStatus == 'challenge') {
            return 'challenge';
        } else if ($fraudStatus == 'accept') {
            return 'success';
        }
    } else if ($transactionStatus == 'settlement') {
        return 'success';
    } else if ($transactionStatus == 'pending') {
        return 'pending';
    } else if ($transactionStatus == 'deny') {
        return 'failed';
    } else if ($transactionStatus == 'expire') {
        return 'expired';
    } else if ($transactionStatus == 'cancel') {
        return 'cancelled';
    }
    return 'pending';
}
```

---

## 🎨 Frontend Implementation (Next.js)

### 1. API Service Layer

**File: `lib/api/courses.ts`**

```typescript
export interface CourseBatch {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    quota: number;
    mentor: { id: number; name: string };
    pricing: Pricing;
    student_count: number;
    is_available: boolean;
    days_remaining: number;
}

export interface CoursePricing {
    id: number;
    name: string;
    price: number;
    duration: number | null;
}

export interface CourseDetail {
    id: number;
    name: string;
    slug: string;
    thumbnail_url: string;
    about: string;
    has_batch: boolean;
    batch?: CourseBatch; // Jika punya batch
    pricings?: CoursePricing[]; // Jika tidak punya batch
    category: { id: number; name: string };
    students_count: number;
}

export const getCourseDetail = async (id: number): Promise<CourseDetail> => {
    const response = await fetch(`/api/courses/${id}`, {
        headers: {
            Authorization: `Bearer ${getToken()}`,
        },
    });
    return response.json();
};
```

**File: `lib/api/transactions.ts`**

```typescript
export const initiatePayment = async (
    courseId: number,
    pricingId: number,
    courseBatchId?: number
) => {
    const payload: any = {
        course_id: courseId,
        pricing_id: pricingId,
    };

    if (courseBatchId) {
        payload.course_batch_id = courseBatchId;
    }

    const response = await fetch("/api/new-transactions/midtrans-payment", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${getToken()}`,
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || "Failed to initiate payment");
    }

    return response.json();
};
```

### 2. Course Detail Component

**File: `components/CourseDetail.tsx`**

```typescript
"use client";

import { useEffect, useState } from "react";
import { getCourseDetail } from "@/lib/api/courses";
import { initiatePayment } from "@/lib/api/transactions";
import CourseBatchPricing from "./CourseBatchPricing";
import CourseOnDemandPricing from "./CourseOnDemandPricing";

interface Props {
    courseId: number;
}

export default function CourseDetail({ courseId }: Props) {
    const [course, setCourse] = useState<CourseDetail | null>(null);
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

    if (loading) return <div>Loading...</div>;
    if (error) return <div className="text-red-500">{error}</div>;
    if (!course) return <div>Course not found</div>;

    return (
        <div className="max-w-4xl mx-auto py-8">
            <img
                src={course.thumbnail_url}
                alt={course.name}
                className="w-full h-96 object-cover rounded-lg mb-6"
            />

            <h1 className="text-4xl font-bold mb-4">{course.name}</h1>
            <p className="text-gray-600 mb-6">{course.about}</p>

            {/* Render pricing berdasarkan jenis course */}
            {course.has_batch ? (
                <CourseBatchPricing
                    course={course}
                    onEnroll={async () => {
                        const result = await initiatePayment(
                            course.id,
                            course.batch!.pricing.id,
                            course.batch!.id
                        );
                        // Handle payment result
                    }}
                />
            ) : (
                <CourseOnDemandPricing
                    course={course}
                    onSelectPricing={async (pricingId) => {
                        const result = await initiatePayment(
                            course.id,
                            pricingId
                        );
                        // Handle payment result
                    }}
                />
            )}
        </div>
    );
}
```

### 3. Batch-Based Pricing Component

**File: `components/CourseBatchPricing.tsx`**

```typescript
"use client";

import { CourseDetail } from "@/lib/api/courses";
import { formatCurrency, formatDate } from "@/lib/utils";

interface Props {
    course: CourseDetail;
    onEnroll: () => Promise<void>;
}

export default function CourseBatchPricing({ course, onEnroll }: Props) {
    const batch = course.batch!;
    const pricing = batch.pricing;

    const formatDateRange = (startDate: string, endDate: string) => {
        const start = new Date(startDate);
        const end = new Date(endDate);
        return `${start.toLocaleDateString("id-ID")} - ${end.toLocaleDateString(
            "id-ID"
        )}`;
    };

    return (
        <div className="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                {/* Left: Batch Information */}
                <div>
                    <h2 className="text-2xl font-bold mb-4">
                        Batch Information
                    </h2>

                    <div className="space-y-4">
                        <div>
                            <label className="text-sm text-gray-500">
                                Batch Name
                            </label>
                            <p className="text-lg font-semibold">
                                {batch.name}
                            </p>
                        </div>

                        <div>
                            <label className="text-sm text-gray-500">
                                Schedule
                            </label>
                            <p className="text-lg">
                                {formatDateRange(
                                    batch.start_date,
                                    batch.end_date
                                )}
                            </p>
                        </div>

                        <div>
                            <label className="text-sm text-gray-500">
                                Mentor
                            </label>
                            <p className="text-lg">{batch.mentor.name}</p>
                        </div>

                        <div>
                            <label className="text-sm text-gray-500">
                                Capacity
                            </label>
                            <div className="flex items-center gap-2">
                                <div className="w-full bg-gray-200 rounded-full h-2">
                                    <div
                                        className="bg-blue-600 h-2 rounded-full"
                                        style={{
                                            width: `${
                                                (batch.student_count /
                                                    batch.quota) *
                                                100
                                            }%`,
                                        }}
                                    />
                                </div>
                                <span className="text-sm">
                                    {batch.student_count}/{batch.quota}
                                </span>
                            </div>
                        </div>

                        {batch.is_available ? (
                            <div className="bg-green-100 text-green-800 px-4 py-2 rounded">
                                ✅ Available - {batch.days_remaining} days left
                            </div>
                        ) : (
                            <div className="bg-red-100 text-red-800 px-4 py-2 rounded">
                                ❌ Batch has ended
                            </div>
                        )}
                    </div>
                </div>

                {/* Right: Fixed Pricing */}
                <div>
                    <h2 className="text-2xl font-bold mb-4">Course Package</h2>

                    <div className="bg-blue-50 rounded-lg p-6 space-y-4">
                        <div>
                            <label className="text-sm text-gray-500">
                                Package Name
                            </label>
                            <p className="text-xl font-bold">{pricing.name}</p>
                        </div>

                        <div>
                            <label className="text-sm text-gray-500">
                                Price
                            </label>
                            <p className="text-4xl font-bold text-blue-600">
                                {formatCurrency(pricing.price)}
                            </p>
                        </div>

                        <div className="bg-white rounded p-4">
                            <p className="text-sm text-gray-600">
                                📌 This course is structured-based (batch). Your
                                access will be available from batch start date
                                to end date.
                            </p>
                        </div>

                        <button
                            onClick={onEnroll}
                            disabled={!batch.is_available}
                            className={`w-full py-3 px-4 rounded-lg font-semibold text-white transition ${
                                batch.is_available
                                    ? "bg-blue-600 hover:bg-blue-700"
                                    : "bg-gray-400 cursor-not-allowed"
                            }`}
                        >
                            {batch.is_available ? "Enroll Now" : "Batch Closed"}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
```

### 4. On-Demand Pricing Component

**File: `components/CourseOnDemandPricing.tsx`**

```typescript
"use client";

import { useState } from "react";
import { CourseDetail, CoursePricing } from "@/lib/api/courses";
import { formatCurrency } from "@/lib/utils";

interface Props {
    course: CourseDetail;
    onSelectPricing: (pricingId: number) => Promise<void>;
}

export default function CourseOnDemandPricing({
    course,
    onSelectPricing,
}: Props) {
    const pricings = course.pricings || [];
    const [selectedPricingId, setSelectedPricingId] = useState<number | null>(
        pricings.length > 0 ? pricings[0].id : null
    );
    const [loading, setLoading] = useState(false);

    const selectedPricing = pricings.find((p) => p.id === selectedPricingId);

    const getDurationLabel = (duration: number | null): string => {
        if (!duration) return "Lifetime Access";
        if (duration === 30) return "1 Month";
        if (duration === 90) return "3 Months";
        if (duration === 365) return "1 Year";
        return `${duration} Days`;
    };

    const handlePurchase = async () => {
        if (!selectedPricingId) return;

        try {
            setLoading(true);
            await onSelectPricing(selectedPricingId);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 className="text-2xl font-bold mb-6">Choose Your Access Plan</h2>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                {pricings.map((pricing) => (
                    <div
                        key={pricing.id}
                        onClick={() => setSelectedPricingId(pricing.id)}
                        className={`p-4 rounded-lg border-2 cursor-pointer transition ${
                            selectedPricingId === pricing.id
                                ? "border-blue-600 bg-blue-50"
                                : "border-gray-200 hover:border-gray-400"
                        }`}
                    >
                        <label className="text-sm text-gray-500">
                            {getDurationLabel(pricing.duration)}
                        </label>
                        <p className="text-2xl font-bold text-blue-600">
                            {formatCurrency(pricing.price)}
                        </p>
                        <p className="text-sm text-gray-600 mt-2">
                            {pricing.name}
                        </p>
                        {pricing.duration && (
                            <p className="text-xs text-gray-500 mt-2">
                                📅 Access for {pricing.duration} days
                            </p>
                        )}
                    </div>
                ))}
            </div>

            {selectedPricing && (
                <div className="bg-blue-50 rounded-lg p-6 space-y-4">
                    <div>
                        <label className="text-sm text-gray-500">
                            Selected Plan
                        </label>
                        <p className="text-xl font-bold">
                            {selectedPricing.name}
                        </p>
                    </div>

                    <div>
                        <label className="text-sm text-gray-500">
                            Total Price
                        </label>
                        <p className="text-4xl font-bold text-blue-600">
                            {formatCurrency(selectedPricing.price)}
                        </p>
                    </div>

                    <div className="bg-white rounded p-4">
                        <p className="text-sm text-gray-600">
                            ⏱️ This course is on-demand. Your access duration:{" "}
                            {getDurationLabel(selectedPricing.duration)}
                        </p>
                    </div>

                    <button
                        onClick={handlePurchase}
                        disabled={loading}
                        className="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-3 px-4 rounded-lg font-semibold transition"
                    >
                        {loading ? "Processing..." : "Buy Now"}
                    </button>
                </div>
            )}
        </div>
    );
}
```

---

## ✅ Testing Checklist

### Backend Testing:

-   [ ] POST /api/new-transactions/midtrans-payment dengan batch
    -   [ ] Validasi course_batch_id required jika course punya batch
    -   [ ] Validasi pricing match batch pricing
    -   [ ] access_expires_at = batch.end_date
-   [ ] POST /api/new-transactions/midtrans-payment tanpa batch
    -   [ ] course_batch_id optional/null
    -   [ ] Pricing harus ada di course_pricings
    -   [ ] access_expires_at = now() + pricing.duration
-   [ ] Webhook processing
    -   [ ] Enrollment type correctly set
    -   [ ] access_expires_at correctly calculated
    -   [ ] Idempotent (tidak duplicate enroll)

### Frontend Testing:

-   [ ] Course detail page load dengan batch
    -   [ ] Display batch info saja (no pricing selector)
    -   [ ] "Enroll Now" button
-   [ ] Course detail page load tanpa batch

    -   [ ] Display pricing options
    -   [ ] Pricing selector functional
    -   [ ] "Buy Now" button

-   [ ] Payment flow end-to-end
    -   [ ] Submit correct payload ke backend
    -   [ ] Receive snap_token
    -   [ ] Payment via Midtrans

---

## 🚀 Deployment Checklist

Before going live:

-   [ ] Run migrations untuk add pricing_id ke course_batches
-   [ ] Run migrations untuk enhance course_students
-   [ ] Update Filament resources untuk manage batch+pricing relationship
-   [ ] Test existing batches: add pricing_id manually atau via seeder
-   [ ] Update API documentation
-   [ ] Test webhook dengan sample payloads dari Midtrans
-   [ ] Load test pricing selection logic
-   [ ] Test CORS headers untuk Next.js
