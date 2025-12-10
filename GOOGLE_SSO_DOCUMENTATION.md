# Dokumentasi Fitur Login dengan Google (SSO)

Dokumen ini menjelaskan alur implementasi dan teknis untuk fitur login menggunakan akun Google di dalam sistem.

## Alur Otentikasi

Fitur ini menggunakan alur `OAuth2` yang melibatkan komunikasi antara frontend, backend, dan Google.

1.  **Inisiasi oleh Frontend**: Pengguna menekan tombol "Login dengan Google" di frontend. Frontend kemudian meminta URL otentikasi ke backend.
2.  **Backend Memberikan URL**: Backend menerima permintaan dan mengembalikan URL otentikasi unik dari Google.
3.  **Redirect ke Google**: Frontend mengarahkan browser pengguna ke URL yang diberikan oleh backend.
4.  **Otentikasi Pengguna**: Pengguna melakukan login di halaman Google dan menyetujui izin yang diminta aplikasi.
5.  **Callback ke Backend**: Setelah berhasil, Google mengarahkan pengguna kembali ke *endpoint callback* di backend, sambil membawa kode otorisasi.
6.  **Backend Memproses & Membuat Token**: Backend menukar kode otorisasi dengan data pengguna Google. Backend kemudian:
    *   Mencari pengguna di database berdasarkan `google_id`.
    *   Jika tidak ada, mencari berdasarkan `email`. Jika ada, `google_id` ditambahkan ke akun tersebut.
    *   Jika tidak ada sama sekali, membuat akun pengguna baru.
    *   Membuat sebuah token otentikasi (JWT).
7.  **Redirect ke Frontend**: Backend mengarahkan browser pengguna kembali ke halaman *callback* di frontend, dengan menyertakan token yang baru dibuat sebagai *query parameter*.
8.  **Frontend Menyimpan Token**: Frontend menerima token dari URL, menyimpannya (misal: di Local Storage), dan mengarahkan pengguna ke halaman dashboard.

---

## Konfigurasi Backend

Pastikan variabel environment berikut ada di file `.env`:

```env
# Kredensial dari Google Cloud Console
GOOGLE_CLIENT_ID=xxxxxxxxxx
GOOGLE_CLIENT_SECRET=xxxxxxxxxx

# URL Callback yang didaftarkan di Google Cloud Console
GOOGLE_REDIRECT_URI=${APP_URL}/api/auth/google/callback

# URL utama aplikasi frontend untuk redirect setelah login berhasil
FRONTEND_URL=http://localhost:3000
```

---

## API Endpoints

### 1. Memulai Proses Login

Endpoint ini digunakan untuk mendapatkan URL otentikasi Google.

-   **Method**: `GET`
-   **Endpoint**: `/api/auth/google/redirect`
-   **Deskripsi**: Mengembalikan URL tujuan untuk otentikasi Google. Frontend harus mengarahkan pengguna ke URL ini.
-   **Request**: Tidak ada body atau parameter yang diperlukan.

-   **Contoh Respon Sukses** (`200 OK`)

    ```json
    {
      "url": "https://accounts.google.com/o/oauth2/v2/auth?client_id=...&redirect_uri=...&scope=openid+profile+email&response_type=code&state=..."
    }
    ```

### 2. Menangani Callback dari Google

Endpoint ini **tidak untuk dipanggil langsung oleh frontend**. Google akan mengarahkan pengguna ke sini setelah otentikasi berhasil.

-   **Method**: `GET`
-   **Endpoint**: `/api/auth/google/callback`
-   **Deskripsi**: Menangani data pengguna dari Google, membuat/login pengguna, men-generate token, dan mengarahkan kembali ke frontend.

-   **Contoh Respon Sukses** (`302 Found` / Redirect)

    Endpoint ini tidak mengembalikan JSON. Ia melakukan redirect ke frontend dengan format:
    
    `{FRONTEND_URL}/auth/callback?token={GENERATED_JWT_TOKEN}`

-   **Contoh Respon Gagal** (`302 Found` / Redirect)

    Jika terjadi error saat proses otentikasi dengan Google, ia akan redirect ke:

    `{FRONTEND_URL}/auth/callback?error=socialite_error`

---

## Panduan Integrasi Frontend (Next.JS)

1.  **Buat Tombol Login**:
    -   Saat tombol diklik, panggil endpoint `/api/auth/google/redirect`.
    -   Dapatkan `url` dari respon JSON.
    -   Arahkan browser ke `url` tersebut (`window.location.href = url`).

2.  **Buat Halaman Callback** (`pages/auth/callback.js`):
    -   Gunakan `useEffect` dan `useRouter` untuk memeriksa *query parameter* di URL.
    -   Jika ada parameter `token`, simpan nilainya ke *Local Storage* atau state management.
    -   Arahkan pengguna ke halaman dashboard atau halaman terproteksi lainnya.
    -   Jika ada parameter `error`, tampilkan notifikasi kegagalan login.
