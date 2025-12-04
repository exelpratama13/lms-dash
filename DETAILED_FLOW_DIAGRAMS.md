# Detailed Flow Diagrams: Implementation Walkthrough

## 🎬 Scenario 1: Student Enrolls in BATCH-BASED Course

### Complete Timeline:

```
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 1: Course Setup (Admin via Filament)                         │
└─────────────────────────────────────────────────────────────────────┘

Admin creates:
  Course "Web Development 101"
         └─ CourseBatch "Batch A - December 2025"
            ├─ start_date: 2025-12-01
            ├─ end_date: 2025-12-31
            ├─ quota: 30
            └─ pricing_id: 5 ← NEW: Links to specific package
               └─ Pricing "Full Package"
                  ├─ price: 500000
                  └─ duration: null (not used for batch)

Database State:
  ┌─ course_batches
  │  ├─ id: 1
  │  ├─ course_id: 1
  │  ├─ name: "Batch A - December 2025"
  │  ├─ start_date: 2025-12-01
  │  ├─ end_date: 2025-12-31
  │  ├─ pricing_id: 5 ← NEW
  │  └─ quota: 30
  │
  └─ course_pricings
     ├─ course_id: 1
     └─ pricing_id: 5

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 2: Student Views Course (Frontend)                            │
└─────────────────────────────────────────────────────────────────────┘

User navigates to: /courses/1

Frontend executes:
  fetch('/api/courses/1', {
    headers: { 'Authorization': 'Bearer TOKEN' }
  })

Backend returns:
  {
    "id": 1,
    "name": "Web Development 101",
    "has_batch": true,
    "batch": {
      "id": 1,
      "name": "Batch A - December 2025",
      "start_date": "2025-12-01",
      "end_date": "2025-12-31",
      "quota": 30,
      "student_count": 10,
      "is_available": true,
      "days_remaining": 43,
      "mentor": { "id": 1, "name": "John Doe" },
      "pricing": {
        "id": 5,
        "name": "Full Package",
        "price": 500000,
        "duration": null
      }
    }
  }

Frontend renders:
  ┌─────────────────────────────────────┐
  │ Web Development 101                  │
  ├─────────────────────────────────────┤
  │ Batch Information                   │
  │                                     │
  │ Batch Name: Batch A - December 2025 │
  │ Schedule: 1 Dec - 31 Dec 2025      │
  │ Mentor: John Doe                    │
  │ Capacity: [████████░░] 10/30        │
  │ Package: Full Package               │
  │ Price: Rp 500.000                   │
  │                                     │
  │ ✅ Available (43 days left)         │
  │                                     │
  │ [Enroll Now]                        │
  └─────────────────────────────────────┘

Key Point: NO pricing selector, fixed price shown

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 3: Student Clicks "Enroll Now" (Frontend initiates payment)   │
└─────────────────────────────────────────────────────────────────────┘

Frontend action:
  POST /api/new-transactions/midtrans-payment
  {
    "course_id": 1,
    "pricing_id": 5,
    "course_batch_id": 1  ← CRITICAL: Batch provided
  }

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 4: Backend Validates Request                                  │
└─────────────────────────────────────────────────────────────────────┘

StoreTransactionRequest::withValidator():

  Check 1: Does course exist?
  ✓ Course ID 1 found

  Check 2: Does course have active batches?
  ✓ Course has batch with end_date >= today
  ✓ Must require course_batch_id

  Check 3: Is course_batch_id provided?
  ✓ Yes, batch_id = 1

  Check 4: Is batch still active?
  ✓ Batch end_date (2025-12-31) > today

  Check 5: Does pricing match batch?
  ✓ Batch.pricing_id = 5
  ✓ Request pricing_id = 5
  ✓ MATCH! ✓

  Result: All validations PASS → Proceed

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 5: Create Transaction (Backend)                               │
└─────────────────────────────────────────────────────────────────────┘

NewTransactionRepository::processMidtransTransaction():

  Load dependencies:
    $pricing = Pricing::find(5)
    $course = Course::find(1)
    $batch = CourseBatch::find(1)  ← Load batch for expiry
    $user = User::find($auth_user_id)

  Determine enrollment type:
    $courseBatchId = 1 (provided)
    $enrollmentType = 'batch'
    $accessExpiresAt = $batch->end_date  // 2025-12-31
                                         // NOT from pricing.duration!

  Generate IDs:
    $transactionCode = 'invd#001'
    $bookingTrxId = 'a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6'

  Create Transaction record:
    INSERT INTO transactions VALUES (
      booking_trx_id: 'a1b2c3d4...',
      user_id: $auth_user_id,
      course_id: 1,
      pricing_id: 5,
      course_batch_id: 1,  ← Batch linked
      sub_total_amount: 500000,
      grand_total_amount: 500000,
      transaction_code: 'invd#001',
      is_paid: false,
      status: null,
      midtrans_snap_token: null
    )

  Prepare Midtrans params:
    {
      "transaction_details": {
        "order_id": "invd#001",
        "gross_amount": 500000
      },
      "customer_details": {
        "first_name": "Student Name",
        "email": "student@example.com"
      },
      "item_details": [{
        "id": "1",
        "name": "Web Development 101 - Full Package",
        "price": 500000,
        "quantity": 1
      }]
    }

  Call Midtrans SDK:
    $snapToken = MidtransService::getSnapToken($params)
    // Returns: '4e1e5a57-35c6-4ce5-b99f-c6565025a4a0' (example)

  Update transaction with Snap token:
    UPDATE transactions
    SET midtrans_snap_token = '4e1e5a57...'
    WHERE id = ...

  Database State After:
    ┌─ transactions
    │  ├─ id: 1
    │  ├─ booking_trx_id: 'a1b2c3d4...'
    │  ├─ user_id: $auth_user_id
    │  ├─ course_id: 1
    │  ├─ pricing_id: 5
    │  ├─ course_batch_id: 1  ← Batch recorded
    │  ├─ is_paid: false
    │  ├─ transaction_code: 'invd#001'
    │  └─ midtrans_snap_token: '4e1e5a57...'

  Return to frontend:
    {
      "status": "success",
      "data": {
        "snap_token": "4e1e5a57...",
        "booking_trx_id": "a1b2c3d4..."
      }
    }

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 6: Student Completes Payment (Midtrans Snap)                  │
└─────────────────────────────────────────────────────────────────────┘

Frontend:
  snap.pay(snap_token, {
    onSuccess: (result) => {
      // Payment successful
      // Redirect to success page
    },
    onError: (error) => {
      // Payment failed
    }
  })

Midtrans sends payment response to customer.
Student completes payment (e.g., via bank transfer, card).

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 7: Midtrans Webhook Callback (Backend)                        │
└─────────────────────────────────────────────────────────────────────┘

Midtrans → POST /api/midtrans/webhook
  {
    "order_id": "invd#001",
    "transaction_id": "xyz123",
    "status_code": "200",
    "transaction_status": "settlement",  // Payment success
    "fraud_status": "accept"
  }

MidtransWebhookService::handleWebhookNotification():

  Step 1: Lookup transaction by order_id
    $transaction = Transaction::where('transaction_code', 'invd#001')
                               ->with('pricing', 'courseBatch')
                               ->first()

  Step 2: Map Midtrans status
    $status = 'success'  (from settlement + accept)
    $isPaid = true

  Step 3: Update transaction record
    UPDATE transactions SET
      status = 'success',
      is_paid = true
    WHERE transaction_code = 'invd#001'

  Step 4: Enroll student (if not already enrolled)
    Check: Is student already enrolled?
    SELECT * FROM course_students
    WHERE user_id = $user_id
      AND course_id = 1

    → Not found, so proceed with enrollment

  Step 5: Calculate access expiration
    enrollmentType = 'batch'  (because course_batch_id = 1)
    batch = $transaction->courseBatch
    accessExpiresAt = batch->end_date  // 2025-12-31
    accessStartsAt = max(now(), batch->start_date)
                   = 2025-12-01 (since today is before start)

  Step 6: Create CourseStudent record
    INSERT INTO course_students VALUES (
      id: AUTO,
      user_id: $auth_user_id,
      course_id: 1,
      course_batch_id: 1,
      pricing_id: 5,
      access_starts_at: 2025-12-01,
      access_expires_at: 2025-12-31,
      enrollment_type: 'batch',  ← NEW
      is_active: true,           ← NEW
      created_at: now(),
      updated_at: now()
    )

  Final Database State:
    ┌─ transactions
    │  ├─ ... (all fields)
    │  ├─ is_paid: true  ← UPDATED
    │  └─ status: 'success'  ← UPDATED
    │
    └─ course_students
       ├─ id: 1
       ├─ user_id: $auth_user_id
       ├─ course_id: 1
       ├─ course_batch_id: 1  ← BATCH LINKED
       ├─ pricing_id: 5
       ├─ access_starts_at: 2025-12-01
       ├─ access_expires_at: 2025-12-31  ← FROM BATCH END DATE
       ├─ enrollment_type: 'batch'
       ├─ is_active: true
       └─ created_at: now()

  Result: Enrollment complete!

  Return:
    {
      "status": 200,
      "message": "Notification handled successfully"
    }

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 8: Student Can Now Access Course                              │
└─────────────────────────────────────────────────────────────────────┘

When student requests course content:
  Frontend: fetch('/api/my-courses')

  Backend checks:
    SELECT * FROM course_students
    WHERE user_id = $auth_user_id
      AND is_active = true
      AND course_batch_id = 1
      AND access_starts_at <= NOW()
      AND (access_expires_at IS NULL OR access_expires_at > NOW())

  Result: Course found!

  Student can access:
    - Course content
    - Videos
    - Quizzes
    - Until 2025-12-31

  On 2026-01-01:
    - access_expires_at < NOW()
    - Course automatically inaccessible (check is_active in query)

Key Point: Expiry tied to BATCH END DATE, not pricing duration
```

---

## 🎬 Scenario 2: Student Enrolls in ON-DEMAND Course

### Complete Timeline:

```
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 1: Course Setup (Admin via Filament)                         │
└─────────────────────────────────────────────────────────────────────┘

Admin creates:
  Course "Python Self-Paced"
    └─ NO batch created (course is flexible)
    └─ Add multiple pricing options via CoursePricing:
       ├─ Pricing "1 Month"
       │  ├─ price: 50000
       │  └─ duration: 30
       ├─ Pricing "3 Months"
       │  ├─ price: 120000
       │  └─ duration: 90
       └─ Pricing "Lifetime"
          ├─ price: 300000
          └─ duration: null

Database State:
  ┌─ courses
  │  └─ id: 2, name: "Python Self-Paced"
  │
  ├─ course_pricings
  │  ├─ course_id: 2, pricing_id: 1 (1 Month)
  │  ├─ course_id: 2, pricing_id: 2 (3 Months)
  │  └─ course_id: 2, pricing_id: 3 (Lifetime)
  │
  └─ course_batches
     └─ (EMPTY - no batch for this course)

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 2: Student Views Course (Frontend)                            │
└─────────────────────────────────────────────────────────────────────┘

User navigates to: /courses/2

Frontend executes:
  fetch('/api/courses/2', {
    headers: { 'Authorization': 'Bearer TOKEN' }
  })

Backend logic:
  $course = Course::find(2)
  $hasBatch = $course->batches()
                    ->where('end_date', '>=', now())
                    ->exists()
  // Result: false (no batches)

Backend returns:
  {
    "id": 2,
    "name": "Python Self-Paced",
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
    ]
  }

Frontend renders pricing selector:
  ┌─────────────────────────────────┐
  │ Python Self-Paced              │
  ├─────────────────────────────────┤
  │ Select Access Plan              │
  │                                 │
  │ ⭕ 1 Month Access               │
  │    Rp 50.000                    │
  │    📅 30 days                   │
  │                                 │
  │ ⭕ 3 Months Access              │
  │    Rp 120.000                   │
  │    📅 90 days                   │
  │                                 │
  │ ⭕ Lifetime Access              │
  │    Rp 300.000                   │
  │    ∞ Forever                    │
  │                                 │
  │ Selected: 3 Months              │
  │ Price: Rp 120.000               │
  │ Duration: 90 days               │
  │                                 │
  │ [Buy Now]                       │
  └─────────────────────────────────┘

Key Point: Pricing selector visible, student chooses duration

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 3: Student Selects 3 Months & Clicks "Buy Now"               │
└─────────────────────────────────────────────────────────────────────┘

Frontend action:
  POST /api/new-transactions/midtrans-payment
  {
    "course_id": 2,
    "pricing_id": 2,           ← Selected: 3 Months
    // NO course_batch_id      ← Batch NOT provided
  }

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 4: Backend Validates Request                                  │
└─────────────────────────────────────────────────────────────────────┘

StoreTransactionRequest::withValidator():

  Check 1: Does course exist?
  ✓ Course ID 2 found

  Check 2: Does course have active batches?
  SELECT * FROM course_batches
  WHERE course_id = 2 AND end_date >= today
  → 0 rows (no batch)
  ✓ course_batch_id NOT required

  Check 3: Is course_batch_id provided?
  ✗ No (it's null/missing)
  ✓ That's OK - on-demand course

  Check 4: Is pricing available for this course?
  SELECT * FROM course_pricings
  WHERE course_id = 2 AND pricing_id = 2
  → 1 row found
  ✓ VALID

  Result: All validations PASS → Proceed

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 5: Create Transaction (Backend)                               │
└─────────────────────────────────────────────────────────────────────┘

NewTransactionRepository::processMidtransTransaction():

  Load dependencies:
    $pricing = Pricing::find(2)  // 3 Months: duration=90
    $course = Course::find(2)
    $batch = null  (not provided in request)
    $user = User::find($auth_user_id)

  Determine enrollment type:
    $courseBatchId = null  (not provided)
    $enrollmentType = 'on_demand'
    $accessExpiresAt = now()->addDays(90)  // From pricing.duration
                     = (today) + 90 days
                     = ~2025-02-16

  Generate IDs:
    $transactionCode = 'invd#002'
    $bookingTrxId = 'b1c2d3e4-f5g6-h7i8-j9k0-l1m2n3o4p5q6'

  Create Transaction record:
    INSERT INTO transactions VALUES (
      booking_trx_id: 'b1c2d3e4...',
      user_id: $auth_user_id,
      course_id: 2,
      pricing_id: 2,
      course_batch_id: NULL,  ← NO batch
      sub_total_amount: 120000,
      grand_total_amount: 120000,
      transaction_code: 'invd#002',
      is_paid: false
    )

  Prepare Midtrans params:
    {
      "transaction_details": {
        "order_id": "invd#002",
        "gross_amount": 120000
      },
      "customer_details": {
        "first_name": "Student Name",
        "email": "student@example.com"
      },
      "item_details": [{
        "id": "2",
        "name": "Python Self-Paced - 3 Months Access",
        "price": 120000,
        "quantity": 1
      }]
    }

  Call Midtrans & get Snap token:
    $snapToken = '5e2f6b68-46d7-4df6-c00g-d7676136b5b1'

  Database State After:
    ┌─ transactions
    │  ├─ id: 2
    │  ├─ booking_trx_id: 'b1c2d3e4...'
    │  ├─ user_id: $auth_user_id
    │  ├─ course_id: 2
    │  ├─ pricing_id: 2
    │  ├─ course_batch_id: NULL  ← NO batch
    │  ├─ is_paid: false
    │  ├─ transaction_code: 'invd#002'
    │  └─ midtrans_snap_token: '5e2f6b68...'

  Return:
    {
      "status": "success",
      "data": {
        "snap_token": "5e2f6b68...",
        "booking_trx_id": "b1c2d3e4..."
      }
    }

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 6: Student Completes Payment                                  │
└─────────────────────────────────────────────────────────────────────┘

(Same as batch scenario)

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 7: Midtrans Webhook Callback                                  │
└─────────────────────────────────────────────────────────────────────┘

Midtrans → POST /api/midtrans/webhook
  {
    "order_id": "invd#002",
    "transaction_status": "settlement",
    "fraud_status": "accept"
  }

MidtransWebhookService::handleWebhookNotification():

  Step 1: Lookup transaction
    $transaction = Transaction::where('transaction_code', 'invd#002')
                               ->with('pricing')
                               ->first()

  Step 2: Update status
    UPDATE transactions SET
      status = 'success',
      is_paid = true
    WHERE transaction_code = 'invd#002'

  Step 3: Check already enrolled
    SELECT * FROM course_students
    WHERE user_id = $auth_user_id AND course_id = 2
    → Not found

  Step 4: Calculate access expiration
    enrollmentType = 'on_demand'  (because course_batch_id = NULL)
    batch = null
    accessExpiresAt = now() + pricing.duration
                   = now() + 90 days
                   = ~2025-02-16  ← Different from batch!
    accessStartsAt = now()

  Step 5: Create CourseStudent record
    INSERT INTO course_students VALUES (
      id: AUTO,
      user_id: $auth_user_id,
      course_id: 2,
      course_batch_id: NULL,  ← NO batch
      pricing_id: 2,
      access_starts_at: now(),
      access_expires_at: ~2025-02-16,  ← 90 days from NOW
      enrollment_type: 'on_demand',    ← NEW
      is_active: true
    )

  Final Database State:
    ┌─ transactions
    │  ├─ ... (all fields)
    │  ├─ is_paid: true  ← UPDATED
    │  └─ status: 'success'
    │
    └─ course_students
       ├─ id: 2
       ├─ user_id: $auth_user_id
       ├─ course_id: 2
       ├─ course_batch_id: NULL  ← NO batch
       ├─ pricing_id: 2
       ├─ access_starts_at: now()
       ├─ access_expires_at: ~2025-02-16  ← 90 days from now
       ├─ enrollment_type: 'on_demand'
       ├─ is_active: true
       └─ created_at: now()

┌─────────────────────────────────────────────────────────────────────┐
│ STEP 8: Student Can Access for 90 Days                             │
└─────────────────────────────────────────────────────────────────────┘

Access valid: Now → ~2025-02-16

Key Differences from Batch Scenario:
  ✗ NOT tied to any batch schedule
  ✓ Student can start immediately
  ✓ Access expires 90 days from purchase date
  ✓ Student chose duration (could have picked lifetime)
  ✗ No mentor-led structure
  ✓ Self-paced learning

On ~2025-02-17:
  - is_active still true, but access_expires_at < NOW()
  - Course inaccessible (check in query)
```

---

## 📊 Key Differences Summary

```
                    BATCH-BASED          ON-DEMAND
────────────────────────────────────────────────────
Batch             Required (1:1)         None (flexible)
Pricing           1 per batch            Multiple options
course_batch_id   Set in DB              NULL in DB
pricing_id        Fixed by batch admin   Chosen by student
access_expires_at Batch.end_date        now() + pricing.duration
enrollment_type   'batch'                'on_demand'
Student choice    Pick batch             Pick pricing
Access pattern    Cohort-based          Self-paced
Schedule          Fixed dates            Immediate
Mentor role       Active teaching        Resource provider

Database Signature:
  Batch:
    transaction.course_batch_id = 1
    course_student.course_batch_id = 1
    course_student.enrollment_type = 'batch'
    course_student.access_expires_at = 2025-12-31

  On-Demand:
    transaction.course_batch_id = NULL
    course_student.course_batch_id = NULL
    course_student.enrollment_type = 'on_demand'
    course_student.access_expires_at = 2025-02-16
```

---

## 🔍 Query Examples for Frontend

### Get All Active Courses for Student:

```sql
SELECT
  cs.*,
  c.name as course_name,
  c.thumbnail_url,
  cb.name as batch_name,
  cb.start_date,
  cb.end_date,
  p.name as pricing_name,
  p.duration
FROM course_students cs
JOIN courses c ON cs.course_id = c.id
LEFT JOIN course_batches cb ON cs.course_batch_id = cb.id
LEFT JOIN pricings p ON cs.pricing_id = p.id
WHERE cs.user_id = ?
  AND cs.is_active = true
  AND (
    cs.access_expires_at IS NULL
    OR cs.access_expires_at > NOW()
  )
ORDER BY cs.created_at DESC
```

### Get Course Info for Display:

```sql
-- For BATCH course
SELECT
  c.*,
  cb.id as batch_id,
  cb.start_date,
  cb.end_date,
  COUNT(cs.id) as student_count,
  p.id as pricing_id,
  p.name,
  p.price
FROM courses c
LEFT JOIN course_batches cb ON c.id = cb.course_id
  AND cb.end_date >= CURDATE()
LEFT JOIN pricings p ON cb.pricing_id = p.id
LEFT JOIN course_students cs ON cb.id = cs.course_batch_id
WHERE c.id = ?
GROUP BY c.id, cb.id

-- For ON-DEMAND course
SELECT
  c.*,
  p.id,
  p.name,
  p.price,
  p.duration
FROM courses c
JOIN course_pricings cp ON c.id = cp.course_id
JOIN pricings p ON cp.pricing_id = p.id
WHERE c.id = ?
  AND NOT EXISTS (
    SELECT 1 FROM course_batches
    WHERE course_id = c.id
    AND end_date >= CURDATE()
  )
```

---

## ✅ Verification Points

Before considering implementation complete, verify:

```
Backend Verification:
  [ ] POST /api/courses/{id} returns has_batch: true/false
  [ ] Batch courses return batch object with pricing
  [ ] On-demand courses return pricings array
  [ ] Validation rejects batch without course_batch_id
  [ ] Validation rejects wrong pricing for batch
  [ ] Transaction created with correct course_batch_id
  [ ] Webhook sets enrollment_type correctly
  [ ] CourseStudent has access_expires_at from correct source

Database Verification:
  [ ] course_batches.pricing_id populated for all batches
  [ ] course_students.enrollment_type set (batch/on_demand)
  [ ] course_students.access_expires_at dates reasonable
  [ ] Indexes created on (user_id, course_id, is_active)

Frontend Verification:
  [ ] Batch course shows no pricing selector
  [ ] On-demand course shows multiple pricing options
  [ ] Payment initiated with correct payload
  [ ] Success page shows correct course info

End-to-End:
  [ ] Complete batch enrollment flow
  [ ] Complete on-demand enrollment flow
  [ ] Webhook processes both types correctly
  [ ] Student can access immediately for on-demand
  [ ] Student can access from batch.start_date if enrolled early
  [ ] Expiry works correctly (batch end date vs pricing duration)
```
