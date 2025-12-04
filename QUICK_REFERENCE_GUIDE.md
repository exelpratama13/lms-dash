# Quick Reference: Batch vs On-Demand Implementation

## 🎯 What You'll Implement

```
YOUR REQUIREMENT:

┌─────────────────────────────┬──────────────────────────────┐
│       BATCH-BASED           │      ON-DEMAND (FLEXIBLE)    │
├─────────────────────────────┼──────────────────────────────┤
│ Course HAS Batch            │ Course NO Batch              │
│ - Fixed schedule            │ - No fixed schedule          │
│ - 1 Pricing per batch       │ - Multiple pricing options   │
│ - Student picks batch       │ - Student picks pricing      │
│ - Expires at batch end_date │ - Expires: now + duration    │
│ - Example: Dec 2025 class   │ - Example: Self-paced course │
└─────────────────────────────┴──────────────────────────────┘
```

---

## 📊 Data Model Changes

### BEFORE (Current):

```
CourseBatch
├─ id
├─ name
├─ start_date
├─ end_date
├─ quota
└─ (NO pricing info)

CourseStudent
├─ id
├─ user_id
├─ course_id
├─ course_batch_id (optional)
└─ access_expires_at (?)  ← Ambiguous: from batch or pricing?

Pricing (Standalone)
├─ id
├─ name
├─ duration
└─ price
```

### AFTER (After Implementation):

```
CourseBatch
├─ id
├─ name
├─ start_date
├─ end_date
├─ quota
└─ pricing_id ← NEW: Links batch to specific pricing package
    └─ (FK to Pricing)

CourseStudent
├─ id
├─ user_id
├─ course_id
├─ course_batch_id (optional)
├─ pricing_id ← NEW: Track which pricing was purchased
├─ access_starts_at ← NEW: When access begins
├─ access_expires_at (now clear: batch end or pricing duration)
├─ enrollment_type: 'batch' | 'on_demand' ← NEW: Clear indicator
└─ is_active ← NEW: Soft disable for expired courses

Pricing (Standalone)
├─ id
├─ name
├─ duration
└─ price
```

---

## 🔄 API Response Changes

### GET /api/courses/{id}

#### Course WITH Batch (Example):

```json
{
  "id": 1,
  "name": "Web Development 101",
  "has_batch": true,
  "batch": {
    "id": 1,
    "name": "Batch December 2025",
    "start_date": "2025-12-01",
    "end_date": "2025-12-31",
    "pricing": {
      "id": 5,
      "name": "Full Package",
      "price": 500000
    }
  }
}

Frontend: Show ONLY this pricing
          NO price selector needed
          Display batch schedule prominently
          "Enroll Now" button (fixed price)
```

#### Course WITHOUT Batch (Example):

```json
{
  "id": 2,
  "name": "Python Self-Paced",
  "has_batch": false,
  "pricings": [
    {
      "id": 1,
      "name": "1 Month",
      "price": 50000,
      "duration": 30
    },
    {
      "id": 2,
      "name": "3 Months",
      "price": 120000,
      "duration": 90
    },
    {
      "id": 3,
      "name": "Lifetime",
      "price": 300000,
      "duration": null
    }
  ]
}

Frontend: Show MULTIPLE pricing options
          Student picks one
          Duration display important
          "Buy Now" button (price changes with selection)
```

---

## 💾 Database Changes Summary

### Add to course_batches:

```sql
ALTER TABLE course_batches ADD pricing_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE course_batches ADD CONSTRAINT
  FOREIGN KEY (pricing_id) REFERENCES pricings(id);
```

### Add to course_students:

```sql
ALTER TABLE course_students ADD pricing_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE course_students ADD access_starts_at TIMESTAMP DEFAULT NOW();
ALTER TABLE course_students ADD enrollment_type ENUM('batch', 'on_demand');
ALTER TABLE course_students ADD is_active BOOLEAN DEFAULT TRUE;

-- Create indexes for performance
CREATE INDEX idx_active_enrollment ON course_students(user_id, is_active);
CREATE INDEX idx_batch_active ON course_students(course_batch_id, is_active);
```

---

## 🔌 Middleware/Validation Logic

### At Transaction Request Validation:

```
INPUT: { course_id, pricing_id, course_batch_id? }

Step 1: Check if course has active batch
        ├─ IF YES → course_batch_id IS REQUIRED
        │           pricing_id MUST match batch.pricing_id
        └─ IF NO → course_batch_id OPTIONAL (usually null)
                   pricing_id MUST exist in course_pricings

Step 2: If course_batch_id provided
        ├─ Verify batch exists
        ├─ Verify batch NOT expired (end_date >= today)
        └─ Verify pricing matches batch.pricing_id

Step 3: If NO course_batch_id
        ├─ Verify pricing available for course
        └─ Proceed with on-demand logic

RESULT: Either PASS all validations or REJECT with error
```

---

## ⏰ Expiration Logic (Access Duration)

### For BATCH-Based Enrollment:

```
Transaction Created with course_batch_id = 1

When Payment Succeeds (Webhook):
  ├─ Lookup batch.end_date (e.g., 2025-12-31)
  └─ CourseStudent.access_expires_at = 2025-12-31 (FIXED)

Result: Student can access from batch.start_date to batch.end_date
        Regardless of pricing.duration
        Batch schedule is KING
```

### For ON-DEMAND Enrollment:

```
Transaction Created with course_batch_id = NULL, pricing_id = 2

When Payment Succeeds (Webhook):
  ├─ Lookup pricing.duration (e.g., 90 days)
  ├─ access_expires_at = now() + 90 days (e.g., 2025-02-16)
  └─ CourseStudent.enrollment_type = 'on_demand'

Result: Student can access for exactly 90 days from purchase
        Pricing.duration is KING
```

---

## 🎨 Frontend Selection Flow

### BATCH-Based Course (User Experience):

```
User navigates to course page
        ↓
Frontend fetches /api/courses/{id}
        ↓
Response: has_batch: true, batch: {...}
        ↓
Frontend renders:
  ┌─────────────────────────────────┐
  │ Batch Information Panel         │
  ├─────────────────────────────────┤
  │ Batch Name: [name]              │
  │ Schedule: [start] - [end]       │
  │ Mentor: [name]                  │
  │ Capacity: [progress bar]        │
  │ Price: [single fixed price]     │
  │                                 │
  │ [Enroll Now] Button             │
  └─────────────────────────────────┘
        ↓
User clicks "Enroll Now"
        ↓
Frontend sends POST /api/new-transactions/midtrans-payment
  {
    course_id: 1,
    pricing_id: 5,
    course_batch_id: 1  ← IMPORTANT
  }
        ↓
Backend validates and creates Snap Token
        ↓
Payment via Midtrans
```

### ON-DEMAND Course (User Experience):

```
User navigates to course page
        ↓
Frontend fetches /api/courses/{id}
        ↓
Response: has_batch: false, pricings: [...]
        ↓
Frontend renders:
  ┌──────────────────────────────────┐
  │ Select Access Plan               │
  ├──────────────────────────────────┤
  │ ⭕ 1 Month  - Rp 50.000          │
  │ ⭕ 3 Months - Rp 120.000         │
  │ ⭕ Lifetime - Rp 300.000         │
  │                                  │
  │ Selected: 3 Months               │
  │ Price: Rp 120.000                │
  │ Duration: 90 days                │
  │                                  │
  │ [Buy Now] Button                 │
  └──────────────────────────────────┘
        ↓
User clicks "Buy Now"
        ↓
Frontend sends POST /api/new-transactions/midtrans-payment
  {
    course_id: 2,
    pricing_id: 2  ← IMPORTANT
    // NO course_batch_id
  }
        ↓
Backend validates and creates Snap Token
        ↓
Payment via Midtrans
```

---

## 📋 Implementation Checklist

### Backend:

-   [ ] Create Migration 1: Add pricing_id to course_batches
-   [ ] Create Migration 2: Enhance course_students table
-   [ ] Run migrations: `php artisan migrate`
-   [ ] Update CourseBatch Model (add pricing relation)
-   [ ] Update CourseStudent Model (add new fields + hasActiveAccess method)
-   [ ] Update StoreTransactionRequest validation logic
-   [ ] Update NewTransactionRepository::processMidtransTransaction()
-   [ ] Update MidtransWebhookService::handleWebhookNotification()
-   [ ] Test API with batch course
-   [ ] Test API with on-demand course
-   [ ] Test webhook enrollment for both types

### Frontend:

-   [ ] Create API service for getCourseDetail
-   [ ] Create API service for initiatePayment
-   [ ] Create CourseDetail page component
-   [ ] Create CourseBatchSection component
-   [ ] Create CourseOnDemandSection component
-   [ ] Create MidtransScript loader
-   [ ] Test batch course UI
-   [ ] Test on-demand course UI
-   [ ] Test end-to-end payment flow

### Database:

-   [ ] Verify migrations applied
-   [ ] Check existing courses/batches
    -   [ ] If batch exists without pricing_id, need to set it
-   [ ] Check course_pricings table for on-demand courses

### Testing:

-   [ ] Batch-based payment flow
-   [ ] On-demand payment flow
-   [ ] Webhook processing for both
-   [ ] CourseStudent table records
-   [ ] Access expiration logic

---

## 🚀 Possible Issues & Solutions

### Issue 1: "This course requires a batch selection"

```
Cause: Course has active batch, but course_batch_id not provided
Solution: Frontend must check has_batch: true
          and send course_batch_id in request
```

### Issue 2: "Pricing mismatch. For this batch, pricing ID must be X"

```
Cause: Sent pricing_id doesn't match batch.pricing_id
Solution: Use batch.pricing.id from API response
          Don't let user select different pricing
```

### Issue 3: "This pricing is not available for this course"

```
Cause: Pricing not linked in course_pricings table
Solution: Make sure pricing added to course in Filament
          Check course_pricings table for entry
```

### Issue 4: Student access expired but still shows course

```
Cause: Webhook didn't set access_expires_at properly
Solution: Check enrollment_type in course_students
          Check batch.end_date or pricing.duration calculation
          Add is_active check to course access queries
```

### Issue 5: "Batch ended" but student still trying to enroll

```
Cause: Frontend caching or batch date miscalculation
Solution: Verify batch.is_available = false in API response
          Check server date/time is correct
          Frontend should disable button if is_available = false
```

---

## 💡 Pro Tips

1. **Always check has_batch in API response first**

    - Determines UI rendering strategy
    - Affects validation logic

2. **Pricing can be NULL for lifetime access**

    - If pricing.duration = null, access_expires_at stays null
    - This is valid for both batch and on-demand

3. **Batch end_date takes precedence**

    - Even if pricing has longer duration
    - Use MIN(pricing_duration, batch.end_date)

4. **Use CourseStudent.hasActiveAccess() method**

    - Always check this before returning courses
    - Handles expired access automatically

5. **Add proper indexing**

    - Query active student enrollments often
    - Use index on (user_id, course_id, is_active)

6. **Test webhook thoroughly**

    - Use Midtrans sandbox webhook simulator
    - Verify enrollment_type and access_expires_at

7. **Frontend should handle both response formats**
    - Don't assume all courses have batches
    - Check has_batch before accessing batch or pricings

---

## 📚 File References

In your project, you'll modify/create:

**Backend:**

-   `database/migrations/2025_11_18_*.php` (2 new migrations)
-   `app/Models/CourseBatch.php` (update)
-   `app/Models/CourseStudent.php` (update)
-   `app/Http/Requests/StoreTransactionRequest.php` (update)
-   `app/Repositories/NewTransactionRepository.php` (update method)
-   `app/Services/MidtransWebhookService.php` (update method)

**Frontend (Next.js):**

-   `lib/api/courses.ts` (create/update)
-   `lib/api/transactions.ts` (create/update)
-   `app/courses/[id]/page.tsx` (create/update)
-   `components/course/CourseBatchSection.tsx` (create)
-   `components/course/CourseOnDemandSection.tsx` (create)
-   `components/payment/MidtransScript.tsx` (create)

**Documentation:**

-   See `READY_TO_USE_CODE_SNIPPETS.md` for complete code
-   See `IMPLEMENTATION_BATCH_BASED_PRICING.md` for detailed guide
