    # Dokumentasi API LMS

    Dokumentasi lengkap untuk semua endpoint API yang tersedia di proyek LMS.

    ---

    ## Daftar Isi

    1.  [Autentikasi](#autentikasi)
    2.  [Kelas (Courses)](#kelas-courses)
    3.  [Struktur Kelas (Sections & Contents)](#struktur-kelas-sections--contents)
    4.  [Kategori](#kategori)
    5.  [Mentor](#mentor)
    6.  [Harga (Pricing)](#harga-pricing)
    7.  [Transaksi](#transaksi)
        *   [Midtrans Webhook](#midtrans-webhook)
    8.  [Statistik](#statistik)
    9.  [Sertifikat](#sertifikat)
    10. [Percobaan Kuis (Quiz Attempts)](#percobaan-kuis-quiz-attempts)

    ---

    ## 1. Autentikasi

    Endpoint untuk registrasi, login, dan manajemen profil pengguna.

    ### **Register**

    - **Endpoint:** `POST /api/register`
    - **Deskripsi:** Mendaftarkan pengguna baru dengan role default `student`.
    - **Autentikasi:** Tidak perlu.
    - **Request Body:**
    ```json
    {
        "name": "John Doe",
        "email": "john.doe@example.com",
        "password": "password123",
        "password_confirmation": "password123"
    }
    ```
    - **Respons Sukses (201):**
    ```json
    {
        "message": "User successfully registered",
        "user": {
        "name": "John Doe",
        "email": "john.doe@example.com",
        "updated_at": "2025-11-11T12:34:56.000000Z",
        "created_at": "2025-11-11T12:34:56.000000Z",
        "id": 1
        }
    }
    ```

    ### **Login**

    - **Endpoint:** `POST /api/login`
    - **Deskripsi:** Login untuk mendapatkan token JWT.
    - **Autentikasi:** Tidak perlu.
    - **Request Body:**
    ```json
    {
        "email": "john.doe@example.com",
        "password": "password123"
    }
    ```
    - **Respons Sukses (200):**
    ```json
    {
        "access_token": "ey...",
        "token_type": "bearer",
        "expires_in": 3600,
        "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@example.com",
        "roles": ["student"]
        }
    }
    ```

    ### **Logout**

    - **Endpoint:** `POST /api/logout`
    - **Deskripsi:** Logout dan membatalkan token JWT saat ini.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Respons Sukses (200):**
    ```json
    {
        "message": "Successfully logged out"
    }
    ```

    ### **Refresh Token**

    - **Endpoint:** `POST /api/refresh`
    - **Deskripsi:** Memperbarui token JWT yang sudah ada.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Respons Sukses (200):** (Struktur sama seperti Login)

    ### **Get My Profile**

    - **Endpoint:** `GET /api/me`
    - **Deskripsi:** Mendapatkan detail profil pengguna yang sedang login.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Respons Sukses (200):**
    ```json
    {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@example.com",
        "photo": null,
        "is_active": true,
        "created_at": "2025-11-11T12:34:56.000000Z",
        "updated_at": "2025-11-11T12:34:56.000000Z"
    }
    ```

    ### **Update My Profile**

    - **Endpoint:** `POST /api/me`
    - **Deskripsi:** Memperbarui detail profil pengguna (nama, email, password, foto).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Request Body:** `multipart/form-data` (untuk upload foto) atau `application/json` (untuk data lain)
    ```json
    {
        "name": "John Doe Smith",
        "email": "john.doe.smith@example.com",
        "password": "newpassword123",
        "password_confirmation": "newpassword123",
        "photo": "(file gambar)"
    }
    ```
    - **Respons Sukses (200):**
    ```json
    {
        "message": "Profile successfully updated",
        "user": {
        "id": 1,
        "name": "John Doe Smith",
        "email": "john.doe.smith@example.com",
        "photo": "http://localhost/storage/photos/profile.jpg",
        "is_active": true,
        "created_at": "...",
        "updated_at": "..."
        }
    }
    ```

    ---

    ## 2. Kelas (Courses)

    Endpoint untuk melihat dan mengelola kelas.

    ### **Get All Courses**

    - **Endpoint:** `GET /api/courses`
    - **Deskripsi:** Mengambil katalog semua kelas yang tersedia.
    - **Autentikasi:** Tidak perlu.
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Course catalog retrieved successfully",
        "count": 1,
        "data": [
        {
            "id": 1,
            "name": "Belajar Laravel dari Dasar",
            "slug": "belajar-laravel-dari-dasar",
            "thumbnail": "http://localhost/storage/thumbnails/example.jpg",
            "about": "Deskripsi singkat kelas.",
            "category_id": 1,
            "is_popular": true,
            "category": { "id": 1, "name": "Web Development" },
            "mentors": [
            {
                "user": {
                "id": 2,
                "name": "Budi Mentor",
                "photo": null
                }
            }
            ],
            "mentors_count": 1
        }
        ]
    }
    ```

    ### **Get Popular Courses**

    - **Endpoint:** `GET /api/courses/popular`
    - **Deskripsi:** Mengambil daftar kelas yang ditandai sebagai populer.
    - **Autentikasi:** Tidak perlu.
    - **Respons Sukses (200):** (Struktur data sama seperti Get All Courses)

    ### **Get Course Detail**

    - **Endpoint:** `GET /api/courses/{slug}`
    - **Deskripsi:** Mengambil detail lengkap sebuah kelas berdasarkan `slug`.
    - **Autentikasi:** Tidak perlu.
    - **Parameter URL:** `slug` (string, wajib).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Course details retrieved successfully",
        "data": {
        "id": 1,
        "name": "Belajar Laravel dari Dasar",
        "slug": "belajar-laravel-dari-dasar",
        "thumbnail": "...",
        "about": "...",
        "category": { "id": 1, "name": "Web Development", "slug": "web-development" },
        "mentors": [ { "user": { "id": 2, "name": "Budi Mentor", "photo": null } } ],
        "benefits": [ { "id": 1, "course_id": 1, "name": "Sertifikat", "description": "..." } ],
        "sections": [ { "id": 1, "title": "Pendahuluan", "contents": [ ... ] } ],
        "pricings": [ { "id": 1, "name": "30 Hari", "price": 100000, "duration_days": 30 } ],
        "batches": [ { "id": 1, "name": "Batch 1", "start_date": "...", "end_date": "..." } ]
        }
    }
    ```

    ### **Get Courses by Category**

    - **Endpoint:** `GET /api/courses/category/{categorySlug}`
    - **Deskripsi:** Mengambil daftar kelas dalam kategori tertentu.
    - **Autentikasi:** Tidak perlu.
    - **Parameter URL:** `categorySlug` (string, wajib).
    - **Respons Sukses (200):** (Struktur data sama seperti Get All Courses)

    ### **Get My Courses**



    -   **Endpoint:** `GET /api/my-courses`

    -   **Deskripsi:** Mengambil daftar kelas yang telah diikuti oleh pengguna, termasuk progres mereka.

    -   **Autentikasi:** **Wajib** (Bearer Token).

    -   **Respons Sukses (200):**

    ```json

    {

        "status": "success",

        "message": "My courses retrieved successfully",

        "count": 1,

        "data": [

        {

            "id": 1,

            "name": "Belajar Laravel dari Dasar",

            "slug": "belajar-laravel-dari-dasar",

            "thumbnail": "http://localhost/storage/thumbnails/example.jpg",

            "about": "Deskripsi singkat kelas.",

            "category_id": 1,

            "is_popular": true,

            "progress_percentage": 75,

            "category": { "id": 1, "name": "Web Development" },

            "mentors": [

            {

                "user": {

                "id": 2,

                "name": "Budi Mentor",

                "photo": null

                }

            }

            ],

            "mentors_count": 1

        }

        ]

    }

    ```



    ### **Search Courses**



    -   **Endpoint:** `GET /api/courses/search`

    -   **Deskripsi:** Mencari kelas berdasarkan nama atau deskripsi.

    -   **Autentikasi:** Tidak perlu.

    -   **Parameter Query:**

        -   `q` (string, wajib): Kata kunci pencarian.

    -   **Respons Sukses (200):**

        ```json

        {

        "status": "success",

        "message": "Course search results retrieved successfully",

        "count": 1,

        "data": [

            {

            "id": 1,

            "name": "Belajar Laravel dari Dasar",

            "slug": "belajar-laravel-dari-dasar",

            "thumbnail": "http://localhost/storage/thumbnails/example.jpg",

            "about": "Deskripsi singkat kelas.",

            "category_id": 1,

            "is_popular": true,

            "category": { "id": 1, "name": "Web Development" },

            "mentors": [

                {

                "user": {

                    "id": 2,

                    "name": "Budi Mentor",

                    "photo": null

                }

                }

            ],

            "mentors_count": 1,

            "price": 100000.00

            }

        ]

        }

        ```



    ### **Create Course**

    - **Endpoint:** `POST /api/courses`
    - **Deskripsi:** Membuat kelas baru (hanya untuk Admin/Mentor).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Request Body:** `multipart/form-data`
    - `name` (string, wajib, unik)
    - `thumbnail` (file, wajib, gambar)
    - `about` (string, opsional)
    - `category_id` (integer, wajib, ID kategori yang valid)
    - `is_popular` (boolean, wajib)
    - **Respons Sukses (201):**
    ```json
    {
        "status": "success",
        "message": "Course created successfully",
        "data": {
        "name": "Nama Kelas Baru",
        "about": "Deskripsi",
        "category_id": "1",
        "is_popular": "0",
        "thumbnail": "http://localhost/storage/thumbnails/...",
        "slug": "nama-kelas-baru",
        "updated_at": "...",
        "created_at": "...",
        "id": 17
        }
    }
    ```

    ### **Update Course**

    - **Endpoint:** `PUT /api/courses/{id}`
    - **Deskripsi:** Memperbarui kelas (hanya untuk Admin/Mentor).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Parameter URL:** `id` (integer, wajib).
    - **Request Body:** `multipart/form-data` (field sama seperti Create, tapi semua opsional).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Course updated successfully",
        "data": {
        "id": 17,
        "name": "Nama Kelas yang Diperbarui",
        "slug": "nama-kelas-yang-diperbarui",
        "thumbnail": "...",
        "about": "...",
        "category_id": 1,
        "is_popular": false,
        "created_at": "...",
        "updated_at": "..."
        }
    }
    ```

    ### **Delete Course**

    - **Endpoint:** `DELETE /api/courses/{id}`
    - **Deskripsi:** Menghapus kelas (hanya untuk Admin/Mentor).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Parameter URL:** `id` (integer, wajib).
    - **Respons Sukses (204):** (No Content)

    ---

    ## 3. Struktur Kelas (Sections & Contents)

    Endpoint untuk melihat dan mengelola struktur internal sebuah kelas.

    ### **Get Course Materi**

    - **Endpoint:** `GET /api/materi/{slug}`
    - **Deskripsi:** Mengambil semua materi sebuah kelas berdasarkan `slug`, termasuk sections, contents, dan data quiz yang terkait.
    - **Autentikasi:** Tidak perlu.
    - **Parameter URL:** `slug` (string, wajib).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Course materi retrieved successfully",
        "data": {
        "id": 9,
        "name": "Cara menang elclassico 5-2",
        "slug": "cara-menang-elclassico-5-2",
        "thumbnail": "http://127.0.0.1:8000/storage/thumbnails/01K9BW7KGCDT87S4E3S371BVD2.jpg",
        "about": "Tutor dek",
        "is_popular": true,
        "category_id": 5,
        "created_at": "2025-11-06T06:04:01.000000Z",
        "updated_at": "2025-11-06T06:04:01.000000Z",
        "deleted_at": null,
        "thumbnail_url": "http://127.0.0.1:8000/storage/thumbnails/01K9BW7KGCDT87S4E3S371BVD2.jpg",
        "creation_year": 2025,
        "sections": [
            {
            "id": 4,
            "name": "Bagian awal",
            "course_id": 9,
            "position": 1,
            "created_at": "2025-11-06T06:06:32.000000Z",
            "updated_at": "2025-11-06T06:06:32.000000Z",
            "deleted_at": null,
            "contents": [
                {
                "id": 4,
                "name": "Menjadi pria sigma",
                "course_section_id": 4,
                "content": "<h2>Lorem ipsum dolor sit amet</h2><p>consectetur adipiscing elit. Nullam ultricies quis justo in condimentum. Pellentesque nec diam eget risus cursus congue et venenatis erat. Vivamus hendrerit efficitur volutpat. In blandit enim nec efficitur ultricies. Nullam in metus et augue interdum pellentesque. Pellentesque ullamcorper eros est, in gravida metus fermentum in. Etiam vehicula tempor facilisis. Curabitur ac elit ut erat rutrum finibus. Quisque ac malesuada magna, a feugiat lacus. Duis ultricies, nisl et vehicula pellentesque, nisl nisl venenatis arcu, a porta mauris magna a velit. Etiam et ante non sem scelerisque porta. Ut tincidunt commodo elit.</p><pre>Maecenas quis tortor vel erat consectetur volutpat vulputate eu elit. Sed rhoncus eros at ligula fermentum varius. Sed congue turpis vitae molestie fringilla. Maecenas libero leo, semper non tortor nec, efficitur dignissim leo. Sed interdum leo elit, at ullamcorper nulla sagittis accumsan. In nec leo luctus mauris fringilla feugiat. Nam maximus risus vel viverra finibus.</pre><p>Praesent quis posuere enim. Nunc vel semper nunc, id mollis ipsum. Proin eros enim, ultrices vitae magna non, pellentesque vehicula eros. Pellentesque sed ullamcorper lorem, vel sodales nisl. Duis vel diam interdum, interdum nunc sed, sodales libero. Integer sagittis, sem vel sollicitudin interdum, risus ex euismod velit, ac maximus mauris justo vitae nibh. Proin pulvinar volutpat augue, et condimentum nisl pretium ut. Pellentesque ipsum tellus, pretium non arcu sit amet, condimentum pellentesque ipsum. Maecenas tincidunt, erat vel fermentum molestie, neque risus eleifend arcu, et dapibus ligula lacus sed purus. Praesent eget nunc non felis luctus porta ac in diam. Praesent sit amet scelerisque orci. Pellentesque condimentum tellus sed elit blandit congue. Nunc id dapibus nunc.</p>",
                "created_at": "2025-11-06T06:07:26.000000Z",
                "updated_at": "2025-11-13T06:17:40.000000Z",
                "deleted_at": null,
                "quiz": {
                    "id": 2,
                    "title": "Quiz 1",
                    "course_content_id": 4,
                    "created_at": "2025-11-13T02:47:24.000000Z",
                    "updated_at": "2025-11-13T02:47:24.000000Z",
                    "deleted_at": null,
                    "questions": [
                    {
                        "id": 3,
                        "quiz_id": 2,
                        "question_text": "TEST TEST  TEST TEST",
                        "created_at": "2025-11-13T02:48:20.000000Z",
                        "updated_at": "2025-11-13T02:48:20.000000Z",
                        "deleted_at": null,
                        "options": [
                        {
                            "id": 9,
                            "question_id": 3,
                            "option_text": "TEST 1",
                            "is_correct": false,
                            "created_at": "2025-11-13T02:48:54.000000Z",
                            "updated_at": "2025-11-13T02:48:54.000000Z",
                            "deleted_at": null
                        },
                        {
                            "id": 10,
                            "question_id": 3,
                            "option_text": "TEST 2",
                            "is_correct": false,
                            "created_at": "2025-11-13T02:49:08.000000Z",
                            "updated_at": "2025-11-13T02:49:08.000000Z",
                            "deleted_at": null
                        },
                        {
                            "id": 11,
                            "question_id": 3,
                            "option_text": "TEST 3",
                            "is_correct": true,
                            "created_at": "2025-11-13T02:49:21.000000Z",
                            "updated_at": "2025-11-13T02:49:21.000000Z",
                            "deleted_at": null
                        }
                        ]
                    }
                    ],
                    "quiz_attempts": [
                    {
                        "id": 11,
                        "user_id": 54,
                        "quiz_id": 2,
                        "start_time": "2025-11-13T09:49:52.000000Z",
                        "end_time": "2025-11-13T11:49:56.000000Z",
                        "score": 80,
                        "passed": true,
                        "created_at": "2025-11-13T02:50:14.000000Z",
                        "updated_at": "2025-11-13T02:50:14.000000Z",
                        "deleted_at": null,
                        "student_answers": []
                    },
                    {
                        "id": 12,
                        "user_id": 54,
                        "quiz_id": 2,
                        "start_time": "2025-11-14T09:50:44.000000Z",
                        "end_time": "2025-11-13T11:50:48.000000Z",
                        "score": 50,
                        "passed": false,
                        "created_at": "2025-11-13T02:50:58.000000Z",
                        "updated_at": "2025-11-13T02:50:58.000000Z",
                        "deleted_at": null,
                        "student_answers": []
                    }
                    ]
                },
                "attachment": {
                    "id": 11,
                    "file": "course-attachments/01K9BYBN0BRT0BTM61MRRSD3G6.jpg",
                    "course_content_id": 4,
                    "created_at": "2025-11-06T06:41:11.000000Z",
                    "updated_at": "2025-11-06T06:41:11.000000Z",
                    "deleted_at": null
                },
                "video": {
                    "id": 12,
                    "id_youtube": "https://youtu.be/lW8xlZE9sxE?si=PdOXsAK2oPeCJmLf",
                    "course_content_id": 4,
                    "created_at": "2025-11-13T03:53:48.000000Z",
                    "updated_at": "2025-11-13T05:54:23.000000Z",
                    "deleted_at": null
                }
                },
                {
                "id": 6,
                "name": "Test",
                "course_section_id": 4,
                "content": "<p>Halo halo</p>",
                "created_at": "2025-11-12T04:44:57.000000Z",
                "updated_at": "2025-11-12T04:44:57.000000Z",
                "deleted_at": null,
                "quiz": {
                    "id": 3,
                    "title": "QUIZ 2",
                    "course_content_id": 6,
                    "created_at": "2025-11-13T02:51:29.000000Z",
                    "updated_at": "2025-11-13T02:51:29.000000Z",
                    "deleted_at": null,
                    "questions": [
                    {
                        "id": 4,
                        "quiz_id": 3,
                        "question_text": "Bagaimana cara belut bereproduksi",
                        "created_at": "2025-11-13T03:50:39.000000Z",
                        "updated_at": "2025-11-13T03:50:39.000000Z",
                        "deleted_at": null,
                        "options": []
                    }
                    ],
                    "quiz_attempts": []
                },
                "attachment": null,
                "video": null
                },
                {
                "id": 7,
                "name": "Test",
                "course_section_id": 4,
                "content": "<p>Halo Halo Halo</p>",
                "created_at": "2025-11-12T04:57:17.000000Z",
                "updated_at": "2025-11-12T05:05:28.000000Z",
                "deleted_at": null,
                "quiz": {
                    "id": 4,
                    "title": "Quiz 3",
                    "course_content_id": 7,
                    "created_at": "2025-11-13T03:21:04.000000Z",
                    "updated_at": "2025-11-13T03:21:04.000000Z",
                    "deleted_at": null,
                    "questions": [],
                    "quiz_attempts": []
                },
                "attachment": null,
                "video": null
                }
            ]
            },
            {
            "id": 5,
            "name": "Bagian 2",
            "course_id": 9,
            "position": 2,
            "created_at": "2025-11-06T06:06:56.000000Z",
            "updated_at": "2025-11-06T06:06:56.000000Z",
            "deleted_at": null,
            "contents": []
            }
        ]
        }
    }
    ```

    ### **List Sections**

    - **Endpoint:** `GET /api/courses/{courseId}/sections`
    - **Deskripsi:** Melihat daftar semua section dalam sebuah kursus.
    - **Autentikasi:** Tidak perlu.
    - **Parameter URL:** `courseId` (integer, wajib).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "data": [
        {
            "id": 1,
            "name": "Pendahuluan",
            "course_id": 1,
            "position": 1,
            "created_at": "...",
            "updated_at": "..."
        }
        ]
    }
    ```

    ### **Show Section**

    - **Endpoint:** `GET /api/sections/{sectionId}`
    - **Deskripsi:** Melihat detail sebuah section beserta konten di dalamnya.
    - **Autentikasi:** Tidak perlu.
    - **Parameter URL:** `sectionId` (integer, wajib).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "data": {
        "id": 1,
        "name": "Pendahuluan",
        "course_id": 1,
        "position": 1,
        "contents": [
            {
            "id": 1,
            "name": "Pengenalan Course",
            "course_section_id": 1,
            "content": "Deskripsi singkat konten."
            }
        ]
        }
    }
    ```

    ### **List Contents**

    - **Endpoint:** `GET /api/sections/{sectionId}/contents`
    - **Deskripsi:** Melihat daftar semua konten dalam sebuah section.
    - **Autentikasi:** Tidak perlu.
    - **Parameter URL:** `sectionId` (integer, wajib).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "data": [
        {
            "id": 1,
            "name": "Pengenalan Course",
            "course_section_id": 1,
            "content": "Deskripsi singkat konten."
        }
        ]
    }
    ```

    ### **Show Content**

    - **Endpoint:** `GET /api/contents/{contentId}`
    - **Deskripsi:** Melihat detail sebuah konten.
    - **Autentikasi:** Tidak perlu.
    - **Parameter URL:** `contentId` (integer, wajib).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "data": {
        "id": 1,
        "name": "Pengenalan Course",
        "course_section_id": 1,
        "content": "Deskripsi singkat konten.",
        "video": null,
        "quiz": null,
        "attachment": null
        }
    }
    ```

    ### **Mark Content as Complete**

    - **Endpoint:** `POST /api/courses/{courseId}/contents/{contentId}/complete`
    - **Deskripsi:** Menandai sebuah konten sebagai telah diselesaikan oleh pengguna yang sedang login.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Parameter URL:**
    - `courseId` (integer, wajib): ID dari course.
    - `contentId` (integer, wajib): ID dari content yang akan ditandai selesai.
    - **Respons Sukses (200):**
    ```json
    {
        "message": "Content marked as complete"
    }
    ```
    - **Respons Error (403):** Jika user tidak terdaftar di course.
    ```json
    {
        "message": "You are not enrolled in this course"
    }
    ```
    - **Respons Error (404):** Jika course atau content tidak ditemukan.
    ```json
    {
        "message": "Course or content not found"
    }
    ```

    ### **Mark Content as Incomplete**

    - **Endpoint:** `DELETE /api/courses/{courseId}/contents/{contentId}/complete`
    - **Deskripsi:** Menghapus status selesai dari sebuah konten untuk pengguna yang sedang login.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Parameter URL:**
    - `courseId` (integer, wajib): ID dari course.
    - `contentId` (integer, wajib): ID dari content yang akan ditandai belum selesai.
    - **Respons Sukses (200):**
    ```json
    {
        "message": "Content marked as incomplete"
    }
    ```
    - **Respons Error (403):** Jika user tidak terdaftar di course.
    ```json
    {
        "message": "You are not enrolled in this course"
    }
    ```
    - **Respons Error (404):** Jika course atau content tidak ditemukan.
    ```json
    {
        "message": "Course or content not found"
    }
    ```

    ### **Create Section**

    - **Endpoint:** `POST /api/sections`
    - **Deskripsi:** Membuat section baru (Auth: `admin`/`mentor`).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Request Body:**
    ```json
    {
        "name": "Section Baru",
        "course_id": 1,
        "position": 2
    }
    ```
    - **Respons Sukses (201):**
    ```json
    {
        "status": "success",
        "data": {
        "name": "Section Baru",
        "course_id": "1",
        "position": "2",
        "updated_at": "...",
        "created_at": "...",
        "id": 2
        }
    }
    ```

    ### **Update Section**

    - **Endpoint:** `PUT /api/sections/{sectionId}`
    - **Deskripsi:** Memperbarui section (Auth: `admin`/`mentor`).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Parameter URL:** `sectionId` (integer, wajib).
    - **Request Body:** (Sama seperti Create Section)
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "data": {
        "id": 2,
        "name": "Section Baru (Updated)",
        "course_id": 1,
        "position": 2,
        "created_at": "...",
        "updated_at": "..."
        }
    }
    ```

    ### **Delete Section**

    - **Endpoint:** `DELETE /api/sections/{sectionId}`
    - **Deskripsi:** Menghapus section (Auth: `admin`/`mentor`).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Parameter URL:** `sectionId` (integer, wajib).
    - **Respons Sukses (204):** (No Content)

    ### **Create Content**

    - **Endpoint:** `POST /api/contents`
    - **Deskripsi:** Membuat konten baru (Auth: `admin`/`mentor`).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Request Body:**
    ```json
    {
        "name": "Konten Baru",
        "course_section_id": 1,
        "content": "Isi dari konten..."
    }
    ```
    - **Respons Sukses (201):**
    ```json
    {
        "status": "success",
        "data": {
        "name": "Konten Baru",
        "course_section_id": "1",
        "content": "Isi dari konten...",
        "updated_at": "...",
        "created_at": "...",
        "id": 2
        }
    }
    ```

    ### **Update Content**

    - **Endpoint:** `PUT /api/contents/{contentId}`
    - **Deskripsi:** Memperbarui konten (Auth: `admin`/`mentor`).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Parameter URL:** `contentId` (integer, wajib).
    - **Request Body:** (Sama seperti Create Content)
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "data": {
        "id": 2,
        "name": "Konten Baru (Updated)",
        "course_section_id": 1,
        "content": "Isi dari konten...",
        "created_at": "...",
        "updated_at": "..."
        }
    }
    ```

    ### **Delete Content**

    - **Endpoint:** `DELETE /api/contents/{contentId}`
    - **Deskripsi:** Menghapus konten (Auth: `admin`/`mentor`).
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Parameter URL:** `contentId` (integer, wajib).
    - **Respons Sukses (204):** (No Content)

    ---

    ## 4. Kategori

    - **Endpoint:** `GET /api/categories`
    - **Deskripsi:** Mengambil daftar semua kategori kelas.
    - **Autentikasi:** Tidak perlu.
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Categories retrieved successfully",
        "data": [
        {
            "id": 1,
            "name": "Web Development",
            "slug": "web-development",
            "courses_count": 5
        }
        ]
    }
    ```

    ---

    ## 5. Mentor

    ### **Get All Mentors**

    - **Endpoint:** `GET /api/mentors`
    - **Deskripsi:** Mengambil daftar semua mentor.
    - **Autentikasi:** Tidak perlu.
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Mentor list retrieved successfully",
        "data": [
        {
            "id": 2,
            "name": "Budi Mentor",
            "photo": null,
            "courses_taught_count": 3
        }
        ]
    }
    ```

    ### **Get Mentor Detail**

    - **Endpoint:** `GET /api/mentors/{userId}`
    - **Deskripsi:** Mengambil profil publik seorang mentor.
    - **Autentikasi:** Tidak perlu.
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Mentor profile retrieved successfully",
        "data": {
        "id": 2,
        "name": "Budi Mentor",
        "photo": null,
        "courses_taught": [{ "id": 1, "name": "Belajar Laravel dari Dasar" }]
        }
    }
    ```

    ### **Get Courses by Mentor**

    - **Endpoint:** `GET /api/mentors/{mentorId}/courses`
    - **Deskripsi:** Mengambil daftar kursus yang diajar oleh seorang mentor.
    - **Autentikasi:** Tidak perlu.
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Courses taught by mentor retrieved successfully",
        "count": 1,
        "data": [
        {
            "id": 1,
            "name": "Belajar Laravel dari Dasar",
            "slug": "belajar-laravel-dari-dasar",
            "thumbnail": "...",
            "about": "...",
            "category_id": 1,
            "is_popular": true
        }
        ]
    }
    ```

    ### **Get Mentors by Category**

    - **Endpoint:** `GET /api/mentors/category/{categorySlug}`
    - **Deskripsi:** Mengambil daftar mentor berdasarkan kategori kursus yang diajar.
    - **Autentikasi:** Tidak perlu.
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Mentors for category 'web-development' retrieved successfully",
        "count": 1,
        "data": [
        {
            "id": 2,
            "name": "Budi Mentor",
            "photo": null
        }
        ]
    }
    ```

    ---

    ## 6. Harga (Pricing)

    - **Endpoint:** `GET /api/courses/{courseId}/pricings`
    - **Deskripsi:** Mengambil daftar opsi harga (langganan) untuk sebuah kelas.
    - **Autentikasi:** Tidak perlu.
    - **Parameter URL:** `courseId` (integer, wajib).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "data": {
        "id": 1,
        "name": "Belajar Laravel dari Dasar",
        "pricings": [
            {
            "id": 1,
            "name": "30 Hari",
            "price": 100000,
            "duration_days": 30
            }
        ],
        "batches": [
            {
            "id": 1,
            "name": "Batch 1",
            "start_date": "...",
            "end_date": "..."
            }
        ]
        }
    }
    ```

    ---

    ## 7. Transaksi

    ### **Create Transaction**

    - **Endpoint:** `POST /api/transactions`
    - **Deskripsi:** Membuat transaksi baru untuk membeli kelas. Mendukung pembayaran manual dan Midtrans.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Request Body (Pembayaran Manual):**
    ```json
    {
        "course_id": 1,
        "pricing_id": 1,
        "course_batch_id": null,
        "payment_type": "manual_transfer",
        "proof": "url_bukti.jpg",
        "sub_total_amount": 100000,
        "total_tax_amount": 10000,
        "grand_total_amount": 110000
    }
    ```
    - **Request Body (Pembayaran Midtrans):**
    ```json
    {
        "course_id": 1,
        "pricing_id": 1,
        "payment_type": "midtrans"
    }
    ```
    - **Respons Sukses (201) - Pembayaran Manual:**
    ```json
    {
        "status": "success",
        "message": "Transaction created successfully. Waiting for payment.",
        "data": {
        "transaction_id": "INV-20251111-12345",
        "status": "pending",
        "grand_total_amount": 110000
        }
    }
    ```
    - **Respons Sukses (201) - Pembayaran Midtrans:**
    ```json
    {
        "status": "success",
        "message": "Midtrans payment initiated successfully.",
        "data": {
        "snap_token": "YOUR_MIDTRANS_SNAP_TOKEN"
        }
    }
    ```

    ### **Midtrans Webhook**

    - **Endpoint:** `POST /api/midtrans/webhook`
    - **Deskripsi:** Endpoint ini digunakan oleh Midtrans untuk mengirim notifikasi status pembayaran. **Tidak dipanggil langsung oleh frontend.**
    - **Autentikasi:** Tidak perlu (Midtrans akan mengirimkan notifikasi dengan mekanisme keamanannya sendiri).
    - **Request Body:** (Dikirim oleh Midtrans, contoh payload dapat dilihat di dokumentasi Midtrans)
    - **Respons Sukses (200):**
    ```json
    {
        "message": "Notification handled successfully"
    }
    ```
    - **Respons Error (404):** Jika transaksi tidak ditemukan.
    ```json
    {
        "message": "Transaction not found"
    }
    ```
    - **Respons Error (500):** Jika terjadi kesalahan server saat memproses notifikasi.
    ```json
    {
        "message": "Failed to handle notification: ..."
    }
    ```

    ### **Get My Transactions**

    - **Endpoint:** `GET /api/my-transactions`
    - **Deskripsi:** Mengambil daftar transaksi yang dimiliki oleh pengguna yang sedang login.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "My transactions retrieved successfully.",
        "count": 1,
        "data": [
        {
            "id": 1,
            "user_id": 1,
            "course_id": 1,
            "pricing_id": 1,
            "course_batch_id": 1,
            "booking_trx_id": "invd#001",
            "payment_type": "manual_transfer",
            "proof": "url_bukti.jpg",
            "sub_total_amount": 100000,
            "total_tax_amount": 10000,
            "grand_total_amount": 110000,
            "is_paid": false,
            "created_at": "...",
            "updated_at": "...",
            "midtrans_snap_token": null,
            "course": {
            "id": 1,
            "name": "Belajar Laravel dari Dasar",
            "slug": "belajar-laravel-dari-dasar"
            },
            "pricing": {
            "id": 1,
            "name": "30 Hari",
            "price": 100000
            }
        }
        ]
    }
    ```

    ### **Get Transaction Detail**

    - **Endpoint:** `GET /api/transactions/{bookingTrxId}`
    - **Deskripsi:** Mengambil detail transaksi tunggal berdasarkan kode transaksi (`booking_trx_id`). Hanya pengguna yang memiliki transaksi tersebut yang dapat mengaksesnya.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Parameter URL:** `bookingTrxId` (string, wajib) - Kode unik transaksi (misal: `invd#001`).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "Transaction details retrieved successfully.",
        "data": {
        "id": 1,
        "user_id": 1,
        "course_id": 1,
        "pricing_id": 1,
        "course_batch_id": 1,
        "booking_trx_id": "invd#001",
        "payment_type": "manual_transfer",
        "proof": "url_bukti.jpg",
        "sub_total_amount": 100000,
        "total_tax_amount": 10000,
        "grand_total_amount": 110000,
        "is_paid": false,
        "created_at": "...",
        "updated_at": "...",
        "midtrans_snap_token": null,
        "course": {
            "id": 1,
            "name": "Belajar Laravel dari Dasar",
            "slug": "belajar-laravel-dari-dasar"
        },
        "pricing": {
            "id": 1,
            "name": "30 Hari",
            "price": 100000
        }
        }
    }
    ```
    - **Respons Error (404):** Jika transaksi tidak ditemukan atau pengguna tidak memiliki akses.
    ```json
    {
        "status": "error",
        "message": "Transaction not found or you do not have access to it."
    }
    ```

    ---

    ## 8. Statistik

    - **Endpoint:** `GET /api/counts`
    - **Deskripsi:** Mengambil data statistik jumlah kelas, siswa, dan mentor.
    - **Autentikasi:** Tidak perlu.
    - **Respons Sukses (200):**
    ```json
    {
        "courses": 15,
        "students": 100,
        "mentors": 10
    }
    ```

    ---

    ## 9. Sertifikat

    ### **Get My Certificates**

    - **Endpoint:** `GET /api/my-certificates`
    - **Deskripsi:** Mengambil daftar sertifikat yang dimiliki oleh pengguna yang sedang login, lengkap dengan kode sertifikat, ID batch kelas, ID progres kelas, dan detail informasi kelas terkait.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Respons Sukses (200):**
    ```json
    {
        "status": "success",
        "message": "My certificates retrieved successfully.",
        "count": 1,
        "data": [
        {
            "id": 1,
            "user_id": 1,
            "code": "CERT-XYZ-123",
            "course_id": 1,
            "course_batch_id": null,
            "course_progress_id": 1,
            "sertificate_url": "http://localhost/storage/certificates/CERT-XYZ-123_1_1.pdf",
            "created_at": "...",
            "updated_at": "...",
            "course": {
            "id": 1,
            "name": "Belajar Laravel dari Dasar",
            "slug": "belajar-laravel-dari-dasar",
            "thumbnail": "...",
            "about": "...",
            "category_id": 1,
            "is_popular": true,
            "thumbnail_url": "http://localhost/storage/thumbnails/example.jpg",
            "creation_year": 2025
            }
        }
        ]
    }
    ```

    ### **Create Certificate**

    - **Endpoint:** `POST /api/certificates`
    - **Deskripsi:** Membuat record sertifikat baru. Pembuatan record ini akan secara otomatis memicu proses pembuatan file PDF sertifikat di backend. Endpoint ini sebaiknya dipanggil setelah pengguna dipastikan telah menyelesaikan sebuah kelas.
    - **Autentikasi:** **Wajib** (Bearer Token).
    - **Request Body:**
    ```json
    {
        "course_id": 1,
        "course_batch_id": 2,
        "course_progress_id": 5
    }
    ```
    - **Respons Sukses (201):**
    ```json
    {
        "status": "success",
        "message": "Certificate created successfully. The PDF is being generated.",
        "data": {
        "user_id": 1,
        "course_id": "1",
        "course_batch_id": "2",
        "course_progress_id": "5",
        "code": "CERT-A1B2C3D4E5",
        "updated_at": "2025-11-20T10:00:00.000000Z",
        "created_at": "2025-11-20T10:00:00.000000Z",
        "id": 2
        }
    }
    ```
    - **Respons Error (422):** Jika data validasi gagal (misal: ID tidak ada).
    ```json
    {
        "message": "The given data was invalid.",
        "errors": {
        "course_id": [
            "The selected course id is invalid."
        ]
        }
    }
    ```

    ---

    ## 10. Percobaan Kuis (Quiz Attempts)

    ### **Get All Quiz Attempts**

    -   **Endpoint:** `GET /api/quiz-attempts`
    -   **Deskripsi:** Mengambil semua percobaan kuis yang dilakukan oleh pengguna yang sedang login, beserta jawaban siswa.
    -   **Autentikasi:** **Wajib** (Bearer Token).
    -   **Respons Sukses (200):**
        ```json
        {
        "status": "success",
        "message": "Quiz attempts retrieved successfully",
        "data": [
            {
            "id": 1,
            "user_id": 1,
            "quiz_id": 1,
            "start_time": "2025-11-14T10:00:00.000000Z",
            "end_time": "2025-11-14T10:30:00.000000Z",
            "score": 95,
            "passed": true,
            "created_at": "2025-11-14T10:35:00.000000Z",
            "updated_at": "2025-11-14T10:35:00.000000Z",
            "student_answers": [
                {
                "id": 1,
                "quiz_attempt_id": 1,
                "question_id": 1,
                "question_option_id": 2,
                "created_at": "2025-11-14T10:35:00.000000Z",
                "updated_at": "2025-11-14T10:35:00.000000Z",
                "question_option": {
                    "id": 2,
                    "question_id": 1,
                    "option_text": "Jawaban Benar",
                    "is_correct": true
                }
                },
                {
                "id": 2,
                "quiz_attempt_id": 1,
                "question_id": 2,
                "question_option_id": 5,
                "created_at": "2025-11-14T10:35:00.000000Z",
                "updated_at": "2025-11-14T10:35:00.000000Z",
                "question_option": {
                    "id": 5,
                    "question_id": 2,
                    "option_text": "Jawaban Salah",
                    "is_correct": false
                }
                }
            ]
            }
        ]
        }
        ```
    -   **Respons Error (401):** Jika pengguna tidak terautentikasi.
        ```json
        {
        "message": "Unauthenticated."
        }
    ```
    -   **Respons Error (500):** Jika terjadi kesalahan server.
        ```json
        {
        "status": "error",
        "message": "Failed to retrieve quiz attempts: ...",
        }
        ```

    ### **Create Quiz Attempt**

    -   **Endpoint:** `POST /api/quiz-attempts`
    -   **Deskripsi:** Menyimpan data percobaan kuis yang telah diselesaikan oleh pengguna, termasuk semua jawabannya.
    -   **Autentikasi:** **Wajib** (Bearer Token).
    -   **Request Body:**
        ```json
        {
        "quiz_id": 1,
        "score": 95,
        "start_time": "2025-11-14 10:00:00",
        "end_time": "2025-11-14 10:30:00",
        "passed": true,
        "answers": [
            {
            "question_id": 1,
            "question_option_id": 2
            },
            {
            "question_id": 2,
            "question_option_id": 5
            }
        ]
        }
    ```
    -   **Respons Sukses (201):**
        ```json
        {
        "status": "success",
        "message": "Quiz attempt stored successfully",
        "data": {
            "user_id": 1,
            "quiz_id": 1,
            "score": 95,
            "start_time": "2025-11-14T10:00:00.000000Z",
            "end_time": "2025-11-14T10:30:00.000000Z",
            "passed": true,
            "updated_at": "2025-11-14T10:35:00.000000Z",
            "created_at": "2025-11-14T10:35:00.000000Z",
            "id": 1
        }
        }
        ```
    -   **Respons Error (422):** Jika data validasi gagal.
        ```json
        {
        "message": "The given data was invalid.",
        "errors": {
            "quiz_id": [
            "The selected quiz id is invalid."
            ],
            "answers.0.question_id": [
                "The selected answers.0.question_id is invalid."
            ]
        }
        }
        ```
    -   **Respons Error (500):** Jika terjadi kesalahan server.
        ```json
        {
        "status": "error",
        "message": "Failed to store quiz attempt: ...",
        }
        ```

    ---

    ## Transaksi (Transactions) - Khusus Frontend & Midtrans

    Bagian ini merinci endpoint-endpoint yang relevan untuk frontend dalam mengelola transaksi dan integrasi pembayaran Midtrans.

    ### 1. Membuat Transaksi Baru (Melalui Midtrans)

    *   **Endpoint:** `/transactions`
    *   **Metode:** `POST`
    *   **Deskripsi:** Membuat transaksi baru dan memulai proses pembayaran Midtrans. **Semua transaksi yang dibuat melalui API ini akan otomatis diarahkan ke Midtrans.**
    *   **Autentikasi:** Diperlukan (Bearer Token).
    *   **Request Body:**
        *   `course_id` (integer, required): ID kursus yang akan dibeli.
        *   `pricing_id` (integer, required): ID opsi harga yang dipilih untuk kursus.
        *   `course_batch_id` (integer, nullable): ID batch kursus, jika ada.
        *   `sub_total_amount` (integer, required): Jumlah sub-total transaksi (sebelum pajak).
        *   `total_tax_amount` (integer, required): Jumlah total pajak.
        *   `grand_total_amount` (integer, required): Jumlah total transaksi (sub-total + pajak).
        *   `payment_type` (string, optional): Meskipun opsional, backend akan secara otomatis mengaturnya ke `'midtrans'` untuk transaksi API. Anda bisa mengirimkannya sebagai `'midtrans'` atau tidak sama sekali.
    *   **Contoh Request Body:**
        ```json
        {
            "course_id": 1,
            "pricing_id": 1,
            "course_batch_id": null,
            "sub_total_amount": 500000,
            "total_tax_amount": 50000,
            "grand_total_amount": 550000
        }
        ```
    *   **Contoh Response (201 Created):**
        ```json
        {
            "status": "success",
            "message": "Midtrans payment initiated successfully.",
            "data": {
                "snap_token": "YOUR_MIDTRANS_SNAP_TOKEN_HERE"
            }
        }
        ```
        *   **`snap_token`**: Token ini adalah kunci yang akan Anda gunakan di frontend untuk memuat pop-up pembayaran Midtrans.

    ### 2. Midtrans Webhook

    *   **Endpoint:** `/midtrans/webhook`
    *   **Metode:** `POST`
    *   **Deskripsi:** Endpoint ini digunakan oleh Midtrans untuk mengirim notifikasi status pembayaran. **Endpoint ini tidak dipanggil langsung oleh frontend.** Backend Anda akan menerima notifikasi ini untuk memperbarui status transaksi.
    *   **Autentikasi:** Tidak diperlukan (Midtrans akan mengirimkan notifikasi dengan mekanisme keamanannya sendiri).
    *   **Request Body:** (Dikirim oleh Midtrans, contoh payload dapat dilihat di dokumentasi Midtrans)
    *   **Contoh Response (200 OK):**
        ```json
        {
            "message": "Notification handled successfully"
        }
        ```
    *   **Contoh Response (404 Not Found):** Jika transaksi tidak ditemukan.
        ```json
        {
            "message": "Transaction not found"
        }
    ```
    *   **Contoh Response (500 Internal Server Error):** Jika terjadi kesalahan server saat memproses notifikasi.
        ```json
        {
            "status": "error",
            "message": "Failed to handle notification: ..."
        }
        ```

    ### 3. Mendapatkan Transaksi Saya

    *   **Endpoint:** `/my-transactions`
    *   **Metode:** `GET`
    *   **Deskripsi:** Mengambil daftar semua transaksi yang dilakukan oleh pengguna yang sedang login.
    *   **Autentikasi:** Diperlukan (Bearer Token).
    *   **Contoh Response (200 OK):**
        ```json
        {
            "status": "success",
            "message": "My transactions retrieved successfully.",
            "count": 1,
            "data": [
                {
                    "id": 1,
                    "user_id": 1,
                    "course_id": 1,
                    "pricing_id": 1,
                    "course_batch_id": 1,
                    "booking_trx_id": "invd#001",
                    "payment_type": "midtrans",
                    "proof": null,
                    "sub_total_amount": 100000,
                    "total_tax_amount": 10000,
                    "grand_total_amount": 110000,
                    "is_paid": false,
                    "created_at": "...",
                    "updated_at": "...",
                    "midtrans_snap_token": "YOUR_MIDTRANS_SNAP_TOKEN_HERE",
                    "course": {
                    "id": 1,
                    "name": "Belajar Laravel dari Dasar",
                    "slug": "belajar-laravel-dari-dasar"
                    },
                    "pricing": {
                    "id": 1,
                    "name": "30 Hari",
                    "price": 100000
                    }
                }
            ]
        }
        ```

    ### 4. Mendapatkan Detail Transaksi Tunggal

    *   **Endpoint:** `/transactions/{bookingTrxId}`
    *   **Metode:** `GET`
    *   **Deskripsi:** Mengambil detail transaksi spesifik berdasarkan `booking_trx_id`. Hanya pengguna yang memiliki transaksi tersebut yang dapat mengaksesnya.
    *   **Autentikasi:** Diperlukan (Bearer Token).
    *   **Path Parameters:**
        *   `bookingTrxId` (string, required) - Kode unik transaksi (misal: `invd#001`).
    *   **Contoh Response (200 OK):**
        ```json
        {
            "status": "success",
            "message": "Transaction details retrieved successfully.",
            "data": {
                "id": 1,
                "user_id": 1,
                "course_id": 1,
                "pricing_id": 1,
                "course_batch_id": 1,
                "booking_trx_id": "invd#001",
                "payment_type": "midtrans",
                "proof": null,
                "sub_total_amount": 100000,
                "total_tax_amount": 10000,
                "grand_total_amount": 110000,
                "is_paid": false,
                "created_at": "...",
                "updated_at": "...",
                "midtrans_snap_token": "YOUR_MIDTRANS_SNAP_TOKEN_HERE",
                "course": {
                "id": 1,
                "name": "Belajar Laravel dari Dasar",
                "slug": "belajar-laravel-dari-dasar"
                },
                "pricing": {
                "id": 1,
                "name": "30 Hari",
                "price": 100000
                }
            }
        }
        ```
    *   **Contoh Response (404 Not Found):**
        ```json
        {
            "status": "error",
            "message": "Transaction not found or you do not have access to it."
        }
        ```
