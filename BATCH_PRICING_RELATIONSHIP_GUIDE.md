# Hubungan CourseBatch, Pricing, dan CourseStudent - Analisis & Rekomendasi

## 📊 Status Saat Ini

### 1. **CourseBatch Model** (Sudah Benar)

```
Struktur: name, mentor_id, course_id, quota, start_date, end_date

Karakteristik:
✅ Memiliki start_date dan end_date (bergantung pada waktu batch dimulai/selesai)
✅ Memiliki quota (kapasitas peserta)
✅ Terikat ke satu course dan satu mentor
✅ Memiliki relasi HasMany ke CourseStudent
```

### 2. **Pricing Model** (Sudah Benar)

```
Struktur: name, duration, price

Karakteristik:
✅ duration = jumlah hari akses yang ditambahkan ke access_expires_at
✅ Bisa dipilih saat membeli (langganan 1 bulan, 3 bulan, lifetime, dll)
✅ ManyToMany dengan Course via course_pricings
✅ Bisa digunakan berulang kali di berbagai course dan batch
```

### 3. **CourseStudent Model** (Perlu Dipahami Dengan Baik)

```
Struktur: user_id, course_id, course_batch_id, access_expires_at

Karakteristik:
✅ Menyimpan record enrollment student ke course
✅ access_expires_at dihitung dari Pricing.duration
⚠️  course_batch_id adalah optional (nullable)
```

---

## 🔍 Analisis Hubungan

### Skenario 1: Student Membeli Course Tanpa Batch (Lifetime/Flexible)

```
Timeline:
┌─────────────────────────┐
│ Student Beli Pricing    │
│ (Lifetime/Flexible)     │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│ Dibuat CourseStudent:               │
│ - course_batch_id = NULL (tidak     │
│   terikat batch tertentu)           │
│ - access_expires_at = NULL atau     │
│   tanpa batas waktu                 │
└─────────────────────────────────────┘

Status: ✅ BENAR - Course bisa diakses kapan saja oleh student
```

### Skenario 2: Student Membeli Course Dengan Batch (Terstruktur)

```
Timeline:
┌──────────────────────────────────────┐
│ CourseBatch dibuat                   │
│ start_date: 2025-12-01               │
│ end_date: 2025-12-31                 │
└──────────────────────┬───────────────┘
                       │
                       ▼
         ┌─────────────────────────┐
         │ Student membeli Pricing │
         │ duration: 60 hari       │
         └──────────┬──────────────┘
                    │
                    ▼
    ┌──────────────────────────────────┐
    │ Dibuat CourseStudent:            │
    │ - course_batch_id: 1 (terikat)   │
    │ - access_expires_at:             │
    │   2025-12-31 (batch end_date)    │
    │   ATAU 60 hari dari pembelian    │
    │   (tergantung business logic)    │
    └──────────────────────────────────┘
          │
          ▼
    ┌──────────────────────────────────┐
    │ Student bisa akses kursus mulai  │
    │ dari start_date hingga end_date  │
    │ (atau sesuai access_expires_at)  │
    └──────────────────────────────────┘
```

### ⚠️ **MASALAH DESAIN SAAT INI**

#### Problem 1: Konflik Waktu Akses

```
Kasus: Student membeli pricing dengan 60 hari akses, tapi batch berakhir dalam 30 hari

Opsi A (Batch End Date Prioritas):
┌────────────────────────────────────┐
│ access_expires_at = batch.end_date │
│ (60 hari pricing diabaikan)        │
└────────────────────────────────────┘
Dampak: Student kehilangan 30 hari akses yang dibayarnya ❌

Opsi B (Pricing Duration Prioritas):
┌──────────────────────────────────────┐
│ access_expires_at = now() + 60 hari  │
│ (bisa melebihi batch.end_date)      │
└──────────────────────────────────────┘
Dampak: Student bisa akses content setelah batch berakhir
        tapi mentor tidak mengajar lagi ❌
```

#### Problem 2: Tidak Ada Kontrol Akses Batch

```
Saat ini: Tidak ada validasi bahwa access_expires_at
          harus dalam range batch.start_date - batch.end_date

Risiko: Student membeli di hari terakhir batch,
        tapi tiba-tiba batch dihapus atau diubah
```

---

## ✅ Rekomendasi Desain yang Benar

### **Option 1: Batch-Centric (RECOMMENDED untuk kursus terstruktur)**

#### Logic:

```
1. Student membeli course + pricing + batch tertentu
2. access_expires_at = MIN(pricing duration, batch.end_date)
3. Student HANYA bisa akses content batch tersebut
4. Akses dimulai dari batch.start_date (atau dari pembelian jika lebih lambat)
```

#### Implementation:

```php
// NewTransactionRepository::processMidtransTransaction()

$batch = $this->courseRepository->getCourseBatch($courseBatchId);
$pricing = $this->courseRepository->getPricing($pricingId);

// Hitung expiry: lebih awal antara pricing duration atau batch end_date
$baseExpiry = now()->addDays($pricing->duration);
$batchExpiry = Carbon::parse($batch->end_date)->endOfDay();

$accessExpiresAt = $baseExpiry->isBefore($batchExpiry)
    ? $baseExpiry
    : $batchExpiry;

// Validasi: Tanggal mulai akses tidak boleh setelah batch berakhir
if ($accessExpiresAt->isBefore($batch->start_date)) {
    // Batch sudah berakhir, tolak pembelian
    throw new Exception('Batch ini sudah berakhir');
}
```

#### Keuntungan:

✅ Akses terbatas dan terstruktur sesuai batch  
✅ Mentor punya kontrol waktu pembelajaran  
✅ Student tahu kapan batch dimulai/selesai  
✅ Cocok untuk kursus dengan deadline

---

### **Option 2: Pricing-Centric (untuk kursus flexible/on-demand)**

#### Logic:

```
1. Student membeli course + pricing (TANPA batch spesifik)
2. course_batch_id = NULL (tidak terikat batch)
3. access_expires_at = now() + pricing.duration
4. Student bisa akses kapan saja selama belum expired
```

#### Implementation:

```php
// Untuk purchase tanpa batch
$pricing = $this->courseRepository->getPricing($pricingId);

$courseStudent = CourseStudent::create([
    'user_id' => $userId,
    'course_id' => $courseId,
    'course_batch_id' => null, // Tidak terikat batch
    'access_expires_at' => now()->addDays($pricing->duration),
]);
```

#### Keuntungan:

✅ Fleksibel, student bisa belajar sesuai kecepatan mereka  
✅ Tidak ada pressure deadline batch  
✅ Cocok untuk self-paced courses  
✅ Pricing duration full digunakan

---

### **Option 3: Hybrid (MOST FLEXIBLE - Recommended)**

#### Logic:

```
1. Jika student membeli dengan batch spesifik:
   → access_expires_at = MIN(pricing duration, batch.end_date)
   → course_batch_id = batch_id

2. Jika student membeli tanpa batch (on-demand):
   → access_expires_at = now() + pricing.duration
   → course_batch_id = NULL
```

#### Benefits:

✅ Support kedua model bisnis (structured + flexible)  
✅ Pricing duration selalu dipertimbangkan  
✅ Batch hanya batasan maksimal (tidak mengurangi hak student)  
✅ Student bisa pilih batch atau on-demand

---

## 🔧 Database Schema Improvement

### Current Schema:

```sql
CREATE TABLE course_students (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    course_batch_id BIGINT NULLABLE,  -- Ambiguous: optional batch
    access_expires_at TIMESTAMP NULLABLE,
    created_at, updated_at, deleted_at
);
```

### Improved Schema (Rekomendasi):

```sql
CREATE TABLE course_students (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    course_batch_id BIGINT NULLABLE,  -- NULL = on-demand purchase
    pricing_id BIGINT NOT NULL,       -- ✨ NEW: track mana pricing yang dibeli
    access_starts_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- ✨ NEW
    access_expires_at TIMESTAMP NULLABLE,  -- NULL = lifetime access
    is_active BOOLEAN DEFAULT TRUE,    -- ✨ NEW: soft disable
    enrollment_type ENUM('batch', 'on_demand') DEFAULT 'on_demand',  -- ✨ NEW
    created_at, updated_at, deleted_at
);
```

#### Migrasi untuk Add Kolom:

```php
Schema::table('course_students', function (Blueprint $table) {
    $table->foreignId('pricing_id')->nullable()->constrained('pricings')->onDelete('set null');
    $table->timestamp('access_starts_at')->default(now())->after('course_batch_id');
    $table->boolean('is_active')->default(true)->after('access_expires_at');
    $table->enum('enrollment_type', ['batch', 'on_demand'])->default('on_demand')->after('is_active');

    // Index untuk query performa
    $table->index(['user_id', 'course_id', 'is_active']);
    $table->index(['course_batch_id', 'is_active']);
});
```

---

## 📋 Best Practices Going Forward

### 1. **Set access_expires_at dengan Logic Benar**

```php
// ❌ JANGAN: Hanya dari pricing
$student->access_expires_at = now()->addDays($pricing->duration);

// ✅ BENAR: Pertimbangkan batch jika ada
if ($batch) {
    $expiryFromPricing = now()->addDays($pricing->duration);
    $batchEnd = Carbon::parse($batch->end_date)->endOfDay();
    $student->access_expires_at = $expiryFromPricing->min($batchEnd);
} else {
    $student->access_expires_at = now()->addDays($pricing->duration);
}
```

### 2. **Validasi Pembelian Batch**

```php
// Saat transaction dibuat:
if ($courseBatchId) {
    $batch = CourseBatch::find($courseBatchId);

    // Batch belum dimulai? Boleh
    // Batch sedang berjalan? Boleh
    // Batch sudah berakhir? TOLAK
    if (now()->isAfter($batch->end_date)) {
        throw new Exception('Batch ini sudah berakhir pada ' . $batch->end_date);
    }
}
```

### 3. **Query Student yang Akses Aktif**

```php
// Mendapatkan course yang student masih punya akses:
$activeEnrollments = CourseStudent::where('user_id', $userId)
    ->where('is_active', true)
    ->where(function ($q) {
        $q->whereNull('access_expires_at')  // Lifetime
          ->orWhere('access_expires_at', '>', now());
    })
    ->with(['course', 'batch', 'pricing'])
    ->get();
```

### 4. **Automatic Expiration (Optional - Scheduler)**

```php
// app/Console/Commands/ExpireStudentAccess.php
$expiredStudents = CourseStudent::where('is_active', true)
    ->whereNotNull('access_expires_at')
    ->where('access_expires_at', '<=', now())
    ->update(['is_active' => false]);
```

### 5. **Pricing Selection Logic**

```php
// Saat student membeli, validasi pricing cocok dengan course:
$pricingsByCourse = CoursePricing::where('course_id', $courseId)->pluck('pricing_id');

if (!$pricingsByCourse->contains($pricingId)) {
    throw new Exception('Pricing ini tidak tersedia untuk course ini');
}
```

---

## 🎯 Summary Table

| Aspek            | Batch                              | Pricing                               | CourseStudent                           |
| ---------------- | ---------------------------------- | ------------------------------------- | --------------------------------------- |
| **Kontrol**      | Waktu pembelajaran (mulai-selesai) | Durasi akses (berapa hari)            | Enrollment individual                   |
| **Sifat**        | 1 batch = 1 cohort peserta         | 1 pricing = banyak option durasi      | 1 student = banyak course               |
| **Hubungan**     | 1 course bisa banyak batch         | 1 course bisa banyak pricing          | FK ke user, course, batch, pricing      |
| **Flexible?**    | Tidak - punya jadwal               | Iya - berbagai pilihan durasi         | Iya - bisa batch atau on-demand         |
| **Jangka Waktu** | Tetap (sudah ditentukan di awal)   | Fleksibel (ditentukan saat pembelian) | Kombinasi (min(batch_end, pricing_exp)) |

---

## 🚀 Action Items

### Segera Implement:

-   [ ] Update `StoreTransactionRequest` untuk accept `enrollment_type` (batch/on_demand)
-   [ ] Update `TransactionController::store()` untuk validasi batch jika dipilih
-   [ ] Update `MidtransWebhookService::handleWebhookNotification()` untuk logic access_expires_at hybrid

### Phase 2 (Opsional):

-   [ ] Add migration untuk pricing_id, access_starts_at, enrollment_type di course_students
-   [ ] Create CourseStudentRepository method untuk cek akses aktif
-   [ ] Add scheduler untuk expire accesses otomatis
-   [ ] Add validasi CoursePricing kompatibilitas

### Testing:

-   [ ] Test pembelian dengan batch (access_expires_at = min)
-   [ ] Test pembelian tanpa batch (access_expires_at = durasi penuh)
-   [ ] Test batch sudah berakhir (reject)
-   [ ] Test query active enrollments
