<!DOCTYPE html>
<html>

<head>
    <title>Certificate</title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            margin: 0;
            padding: 50px;
            background-color: #f0f0f0;
        }

        .certificate-container {
            max-width: 700px;
            min-height: 500px;
            margin: auto;
            border: 10px solid #3b82f6; /* Changed to blue */
            padding: 40px;
            box-sizing: border-box;
            background-color: #fff;
            position: relative;
            /* background-image is handled by .logo-background div */
            background-size: cover;
            background-position: center;
            overflow: hidden; /* Ensure background doesn't overflow */
        }

        .certificate-header {
            font-size: 2em;
            margin-bottom: 20px;
            color: #16a34a; /* Changed to green */
        }

        .certificate-title {
            font-size: 3em;
            color: #3b82f6; /* Changed to blue */
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .certificate-recipient {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #000;
            font-weight: bold;
        }

        .certificate-text {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 10px;
        }

        .certificate-course {
            font-size: 1.8em;
            color: #3b82f6; /* Changed to blue */
            margin-bottom: 40px;
            font-weight: bold;
        }

        .certificate-date {
            font-size: 1.1em;
            color: #777;
            margin-top: 30px;
        }

        .certificate-code {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 0.9em;
            color: #aaa;
        }

        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
        }

        .signature-block {
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #3b82f6; /* Changed to blue */
            width: 150px;
            margin: 10px auto 5px auto;
        }

        .signature-name {
            font-weight: bold;
        }

        .signature-title {
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="certificate-container">
        <div class="logo-background" style="
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ public_path('images/logo.png') }}');
            background-size: 80% auto; /* Adjust size as needed, 'auto' maintains aspect ratio */
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.1; /* Adjust transparency as needed */
            z-index: 0;
        "></div>
        <div style="position: relative; z-index: 1;">
            <div class="certificate-header">CERTIFICATE OF COMPLETION</div>
            <div class="certificate-title">This Certifies That</div>
            <div class="certificate-recipient">{{ $recipientName ?? 'Nama Peserta' }}</div>
            <div class="certificate-text">has successfully completed the course</div>
            <div class="certificate-course">{{ $course->name ?? 'Nama Kursus' }}</div>

            @if (isset($certificate->courseBatch) && $certificate->courseBatch->name)
                <div class="certificate-text" style="font-size: 1.2em; margin-top: -30px; margin-bottom: 30px;">
                    Batch: {{ $certificate->courseBatch->name }}
                </div>
            @endif

            <div class="certificate-text">on</div>
            <div class="certificate-date">{{ $completion_date ?? \Carbon\Carbon::now()->format('F d, Y') }}</div>

            <div class="signature">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-name">Admin Name</div>
                    <div class="signature-title">Administrator</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $mentor->name ?? 'Nama Mentor' }}</div>
                    <div class="signature-title">Course Instructor</div>
                </div>
            </div>

            <div class="certificate-code">Certificate ID: {{ $certificate->code ?? 'N/A' }}</div>
        </div>
    </div>
</body>

</html>
