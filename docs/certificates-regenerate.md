# Regenerate Certificate PDF

This document describes the API endpoint and Filament action to regenerate a certificate PDF from an existing `Sertificate` record. The regenerate operation only recreates the PDF file based on the stored certificate data (including `recipient_name`) and replaces the existing file path saved in the record.

**Location:** `POST /api/certificates/{certificate}/regenerate`

## Summary

-   Purpose: Re-generate the PDF file for an existing certificate using the stored data.
-   Affects: `Sertificate` record's `sertificate_url` (file path stored) and generated file at `storage/app/public/...`.
-   PDF template: `resources/views/certificates/certificate.blade.php`.
-   Data source: fields on the `Sertificate` model — importantly `recipient_name` (if present) or fallback to the related `user->name`.

## API: Regenerate Endpoint

-   Method: `POST`
-   URL: `/api/certificates/{certificate}/regenerate`
-   Auth: `auth:api` (bearer token). The current controller implementation restricts this endpoint to the certificate owner only (HTTP 403 if not owner). Filament actions are available to admins (see Filament section).
-   Body: none required.

### Success Response (200)

```json
{
    "status": "success",
    "message": "Certificate regenerated",
    "data": {
        "sertificate_url": "http://localhost/storage/certificates/CERT-XYZ-123_1_1.pdf"
    }
}
```

### Possible Error Responses

-   401 Unauthorized: user not authenticated.
-   403 Forbidden: authenticated user is not the owner (unless controller changed to allow admin access).
-   500 Server Error: generation failed (check logs, dependencies, storage write permissions).

## Filament: Regenerate Action

-   Location: `Sertificate` resource table in Filament (`app/Filament/Resources/SertificateResource.php`).
-   Action name: `Regenerate` (requires confirmation).
-   Behavior:
    -   Calls `CertificateGeneratorService::generatePdf($record)`.
    -   On success: shows a Filament notification "Certificate regenerated" and refreshes the record so the `sertificate_url` accessor returns the updated URL.
    -   On failure: shows a danger notification.
-   Visibility: Filament resource access is restricted to admins via `canViewAny()` in the resource; admins can regenerate any certificate.

## PDF content and the `recipient_name` column

-   The PDF template uses `{{ $certificate->recipient_name ?? ($user->name ?? 'Nama Peserta') }}`.
    -   If `recipient_name` is set on the `Sertificate` record, the regenerated PDF will use that value.
    -   If `recipient_name` is null/empty, the generator will display the related `user->name` as a fallback.
-   There is a backfill command (`php artisan certificates:backfill-recipient-names`) to populate `recipient_name` for existing records from the user's name.
-   The Filament edit form includes a `recipient_name` field so admins can correct spelling or set a different display name before regenerating.

## Implementation notes & prerequisites

-   `CertificateGeneratorService::generatePdf(Sertificate $certificate)` is responsible for rendering the Blade view and storing the generated PDF under `storage/app/public/certificates/...`.
-   Ensure the following before use:
    -   `composer install` has been run and `barryvdh/laravel-dompdf` is installed (used for PDF generation).
    -   `php artisan storage:link` so `Storage::disk('public')->url()` returns a web-accessible `/storage/...` URL.
    -   DB migrations have been run so `recipient_name` and `sertificate_url` columns exist.
    -   Storage folder has write permission for the web server.

## CLI / Quick test examples

-   Regenerate via API (owner token):

```bash
curl -X POST "https://your-app.test/api/certificates/123/regenerate" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

-   Regenerate via Filament UI:
    -   Login to Filament as admin, open `Sertificate` list, click `Regenerate` on a record, confirm the action, observe success notification.

## Recommended improvements (optional)

1. Queue the PDF generation
    - For large PDFs or to avoid UI/API timeouts, dispatch a queued Job that calls `CertificateGeneratorService::generatePdf()`.
    - Endpoint can return `202 Accepted` and the Job can send a notification or email when done.
2. Allow admins to call API regenerate
    - Update `CertificateController::regenerate()` to authorize admins (via policy or role-check) so admins can regenerate on behalf of users.
3. Email on regenerate
    - Optionally send the regenerated PDF to the certificate owner via Mailable (queued).
4. Bulk regenerate
    - Add a Filament bulk action to regenerate multiple certificate PDFs and dispatch a queued Job for each.

## Troubleshooting

-   If generation fails with a 500 error:
    -   Check `storage/logs/laravel.log` for stack traces.
    -   Verify `barryvdh/laravel-dompdf` is installed and PHP extension dependencies (ext-gd or ext-imagick if used by templates) are present.
    -   Verify `storage/app/public` is writable.
-   If `sertificate_url` is null after regenerate:
    -   Ensure `CertificateGeneratorService` saved the file path to `sertificate_url` and called `$certificate->save()`.
    -   Run `php artisan storage:link` and reload the record.

## Example: Make admins allowed to regenerate via API (suggestion)

In `CertificateController::regenerate()` replace the simple owner check with a policy or role check, e.g.:

```php
if (!auth()->user()->hasRole('admin') && $certificate->user_id !== auth()->id()) {
    return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
}
```

---

File: `docs/certificates-regenerate.md`
Created: Documentation for regenerate endpoint and Filament action.

If you want, I can:

-   Implement queued generation and return 202 + job status.
-   Add admin authorization to the API regenerate endpoint.
-   Add an email send after regenerate (queued).

Which of these would you like me to implement next?
