<!DOCTYPE html>
<html>
<head>
    <title>Certificate</title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            margin: 0;
            padding: 50px; /* Added page margin */
            background-color: #f0f0f0;
        }
        .certificate-container {
            /* Removed fixed width and height */
            max-width: 700px; /* Set a max-width to prevent overflow */
            min-height: 500px; /* Ensure a minimum height */
            margin: auto; /* Center horizontally */
            border: 10px solid #ffd700;
            padding: 40px; /* Increased internal padding */
            box-sizing: border-box;
            background-color: #fff;
            position: relative;
            background-image: url('{{ $background_image ?? '' }}'); /* Optional background image */
            background-size: cover;
            background-position: center;
        }
        .certificate-header {
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
        }
        .certificate-title {
            font-size: 3em;
            color: #0056b3;
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
            color: #007bff;
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
            border-top: 1px solid #000;
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
        <div class="certificate-header">CERTIFICATE OF COMPLETION</div>
        <div class="certificate-title">This Certifies That</div>
        <div class="certificate-recipient">{{ $user->name ?? 'Nama Peserta' }}</div>
        <div class="certificate-text">has successfully completed the course</div>
        <div class="certificate-course">{{ $course->name ?? 'Nama Kursus' }}</div>

        @if(isset($certificate->courseBatch) && $certificate->courseBatch->name)
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
</body>
</html>
