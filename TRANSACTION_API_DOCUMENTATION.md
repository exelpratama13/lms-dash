# Transaction API Documentation

**Versi 2.0 - Diperbarui untuk Batch & On-Demand Pricing**

## Overview

API untuk membuat transaksi pembayaran kursus menggunakan Midtrans Snap payment gateway. API ini sekarang mendukung dua model pembelian: **Batch-Based** (harga tetap per-batch) dan **On-Demand** (pilihan harga fleksibel).

---

## 1. Create Transaction (Midtrans Payment)

### Endpoint

```
POST /api/transactions
```

### Authentication

**Required**: Yes (Bearer Token JWT)

```
Authorization: Bearer {JWT_TOKEN}
```

### Content-Type

```
application/json
```

---

## 2. Request Body

Backend sekarang secara otomatis menghitung semua jumlah (harga, pajak, total). Frontend hanya perlu mengirimkan ID yang relevan.

### **Contoh 1: Pembelian Kursus On-Demand (Tanpa Batch)**

Hanya kirim `course_id` dan `pricing_id` yang dipilih pengguna.

```json
{
    "course_id": 2,
    "pricing_id": 3
}
```

### **Contoh 2: Pembelian Kursus Berbasis Batch**

Kirim `course_id`, `pricing_id` (yang terikat pada batch), dan `course_batch_id`.

```json
{
    "course_id": 1,
    "pricing_id": 5,
    "course_batch_id": 1
}
```

---

## 3. Request Fields Detail

### Required Fields

#### `course_id` (Integer, Required)

-   **Description**: ID dari kursus yang akan dibeli.
-   **Example**: `1`

#### `pricing_id` (Integer, Required)

-   **Description**: ID dari paket harga (`pricing`) yang dipilih. Untuk kursus berbasis batch, ini adalah ID harga yang terikat pada batch tersebut.
-   **Example**: `3`

### Optional / Conditional Fields

#### `course_batch_id` (Integer, Conditional)

-   **Description**: ID dari `course_batch` yang dipilih. **Wajib diisi jika kursus yang dibeli memiliki batch aktif**. Kosongkan atau `null` jika membeli kursus on-demand.
-   **Example**: `1` atau `null`

---

### DEPRECATED Fields (Tidak Perlu Dikirim)

Field-field berikut **TIDAK PERLU** dikirim lagi dari frontend. Semua perhitungan dilakukan secara aman di backend.

-   `sub_total_amount`
-   `total_tax_amount`
-   `grand_total_amount`
-   `proof`
-   `payment_type` (otomatis di-set ke `midtrans`)

---

## 4. Backend Logic & Calculations

1.  **Penentuan Harga**: Harga dasar (`sub_total_amount`) diambil dari database berdasarkan `pricing_id` yang dikirim. **Nilai harga dari frontend akan diabaikan**.
2.  **Perhitungan Pajak**: Pajak **12%** secara otomatis dihitung dari harga dasar.
3.  **Total Keseluruhan**: `grand_total_amount` adalah hasil dari `harga dasar + pajak 12%`.
4.  **Validasi Cerdas**:
    -   Jika `api/courses/{slug}` mengembalikan `has_batch: true`, maka request **harus** menyertakan `course_batch_id`.
    -   Jika `course_batch_id` dikirim, backend akan memvalidasi bahwa `pricing_id` yang dikirim sesuai dengan harga yang terikat pada batch tersebut.
    -   Jika `has_batch: false`, backend akan memvalidasi bahwa `pricing_id` yang dikirim tersedia untuk kursus on-demand tersebut.

---

## 5. Response

### Success Response (HTTP 201 Created)

Respons berisi `snap_token` untuk membuka UI pembayaran Midtrans dan `booking_trx_id` untuk referensi.

```json
{
    "status": "success",
    "message": "Midtrans payment initiated successfully.",
    "data": {
        "snap_token": "0d6c5e5c-e2cb-4b97-b0d1-3f0c5e5c5e5c",
        "booking_trx_id": "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6"
    }
}
```

### Success Response for Free Courses (HTTP 201 Created)

Jika harga kursus yang dibeli adalah Rp 0 (gratis), proses pembayaran Midtrans akan dilewati. Siswa akan langsung terdaftar ke kursus dan respons akan menunjukkan pendaftaran yang berhasil tanpa `snap_token`.

```json
{
    "status": "success",
    "message": "Free course enrolled successfully.",
    "data": {
        "id": 123,
        "user_id": 456,
        "course_id": 789,
        "pricing_id": 101,
        "course_batch_id": null,
        "sub_total_amount": 0,
        "grand_total_amount": 0,
        "total_tax_amount": 0,
        "payment_type": "free",
        "transaction_code": "TRX-001-20251126-0001",
        "status": "success",
        "is_paid": true,
        "booking_trx_id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
        "midtrans_snap_token": null,
        "created_at": "2025-11-26T10:00:00.000000Z",
        "updated_at": "2025-11-26T10:00:00.000000Z"
    }
}
```

---

### Error Response

| HTTP Code | Message                                                              | Cause                                                              |
| :-------- | :------------------------------------------------------------------- | :----------------------------------------------------------------- |
| `422`     | `This course requires selecting an active batch.`                    | Kursus memiliki batch, tapi `course_batch_id` tidak dikirim.       |
| `422`     | `Selected batch has ended and is no longer available.`               | Batch yang dipilih sudah kedaluwarsa.                              |
| `422`     | `Pricing mismatch. For this batch, pricing ID must be X.`            | `pricing_id` tidak sesuai dengan yang terikat pada batch.          |
| `422`     | `This pricing is not available for this course.`                     | `pricing_id` tidak valid untuk kursus on-demand.                   |
| `500`     | `Failed to create transaction: ...`                                  | Error internal server (misal: Midtrans API key salah).             |

---

## 6. Request/Response Flow Example

### Step 1 & 2: Login & Get Course Detail

(Sama seperti sebelumnya, tapi sekarang perhatikan respons dari `GET /api/courses/{slug}`)

### Step 3: Create Transaction

**Skenario A: Beli Kursus Batch**

```
POST /api/transactions
Authorization: Bearer {JWT_TOKEN}
Body: {
  "course_id": 1,
  "pricing_id": 5,
  "course_batch_id": 1
}
```

**Skenario B: Beli Kursus On-Demand**

```
POST /api/transactions
Authorization: Bearer {JWT_TOKEN}
Body: {
  "course_id": 2,
  "pricing_id": 3
}
```

### Step 4: Initialize Midtrans Snap UI (Frontend)

(Sama seperti sebelumnya, gunakan `snap_token` dari respons)

---

## 7. Database Transaction Record Example (dengan Pajak)

Setelah request berhasil, data di tabel `transactions` akan terlihat seperti ini:

```
id: 2
booking_trx_id: "b1c2d3e4-f5g6-h7i8-j9k0-l1m2n3o4p5q6"
user_id: 1
course_id: 1
pricing_id: 5
course_batch_id: 1
transaction_code: "invd#002"
sub_total_amount: 500000
grand_total_amount: 560000  // <-- Termasuk Pajak
total_tax_amount: 60000     // <-- Pajak 12%
is_paid: false
payment_type: "midtrans"
midtrans_snap_token: "some-snap-token-from-midtrans"
...
```

---

## 8. Best Practices

✅ **DO:**

-   Kirim request minimal (`course_id`, `pricing_id`, dan `course_batch_id` jika perlu).
-   Selalu andalkan `has_batch` dari `GET /api/courses/{slug}` untuk menentukan payload request.
-   Implementasikan penanganan webhook untuk konfirmasi pembayaran.

❌ **DON'T:**

-   **JANGAN** mengirim `sub_total_amount`, `total_tax_amount`, atau `grand_total_amount` dari frontend.
-   Menggunakan kembali `snap_token` (kedaluwarsa dalam 15 menit).

---
**Last Updated**: 2025-11-26  
**API Version**: v2  
**Status**: Production Ready ✓
