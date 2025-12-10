<div style="font-family: Arial, sans-serif; font-size:14px;">
    <p>Halo {{ $transaction->user->name }},</p>

    <p>Terima kasih telah melakukan pembayaran. Terlampir bukti pembayaran untuk pesanan Anda.</p>

    <p>
        <strong>Order ID:</strong> {{ $transaction->booking_trx_id }}<br>
        <strong>Jumlah:</strong> Rp{{ number_format($transaction->grand_total_amount, 0, ',', '.') }}<br>
        <strong>Tanggal:</strong> {{ $transaction->created_at->format('Y-m-d H:i') }}
    </p>

    <p>Jika Anda memiliki pertanyaan, balas email ini atau hubungi tim support.</p>

    <p>Salam,<br>Tim LMS</p>
</div>
