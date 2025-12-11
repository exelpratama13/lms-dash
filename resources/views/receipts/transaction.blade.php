<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $transaction->transaction_code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .meta {
            margin-bottom: 12px;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .items th,
        .items td {
            border: 1px solid #ddd;
            padding: 8px
        }
    </style>
</head>

<body>
<div class="logo-background" style="
        position: fixed; /* Use fixed for full page background */
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url('{{ public_path('images/logo.png') }}');
        background-size: 50% auto; /* Adjust size as needed */
        background-repeat: no-repeat;
        background-position: center;
        opacity: 0.05; /* Adjust transparency as needed */
        z-index: -1; /* Place it behind content */
    "></div>

    <div style="position: relative; z-index: 0;">
        <div class="header">
            <h2>Receipt Pembayaran</h2>
            <div>Order ID: {{ $transaction->booking_trx_id }}</div>
            <div>Tanggal: {{ $transaction->created_at->format('Y-m-d H:i') }}</div>
        </div>

    <div class="meta">
        <strong>Pelanggan:</strong> {{ $transaction->user->name }} ({{ $transaction->user->email }})<br>
        <strong>Kursus:</strong> {{ optional($transaction->course)->name }}<br>
        <strong>Pricing:</strong> {{ optional($transaction->pricing)->name }} -
        Rp{{ number_format($transaction->sub_total_amount, 0, ',', '.') }}
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ optional($transaction->course)->name }}</td>
                <td>Rp{{ number_format($transaction->sub_total_amount, 0, ',', '.') }}</td>
                <td>1</td>
                <td>Rp{{ number_format($transaction->sub_total_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pajak</td>
                <td>Rp{{ number_format($transaction->total_tax_amount, 0, ',', '.') }}</td>
                <td>1</td>
                <td>Rp{{ number_format($transaction->total_tax_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align:right"><strong>Grand Total</strong></td>
                <td><strong>Rp{{ number_format($transaction->grand_total_amount, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:20px;">
        Terima kasih telah bertransaksi.
    </div>
</body>

</html>
