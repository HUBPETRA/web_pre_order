<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; }
        .header { background: #991b1b; color: white; padding: 20px; text-align: center; } /* Merah Gelap */
        .content { padding: 20px; color: #333; line-height: 1.6; }
        .bill-box { background: #fef2f2; border: 2px dashed #991b1b; padding: 20px; margin: 20px 0; text-align: center; border-radius: 8px; }
        .amount { font-size: 24px; font-weight: bold; color: #991b1b; margin: 10px 0; }
        .bank-info { background: #f3f4f6; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 14px; }
        .footer { background: #f9fafb; padding: 15px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">TAGIHAN DENDA BELUM DIBAYAR</h2>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $quota->fungsio->name }}</strong>,</p>
            <p>PO <strong>{{ $batch->name }}</strong> telah ditutup dan Anda belum memenuhi target kuota penjualan.</p>

            <p>Sesuai peraturan, denda dikenakan sebesar <strong>Rp {{ number_format($multiplier, 0, ',', '.') }}</strong> per item (progresif mingguan).</p>

            <div class="bill-box">
                <p style="margin:0; font-size: 14px; color: #555;">Kekurangan Target:</p>
                <h3 style="margin: 5px 0;">{{ $quota->deficit }} Item</h3>
                
                <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 15px 0;">
                
                <p style="margin:0; font-size: 14px; color: #555;">Total Denda Harus Dibayar:</p>
                <div class="amount">Rp {{ number_format($currentFineTotal, 0, ',', '.') }}</div>
            </div>

            <div class="bank-info">
                <strong>Silakan transfer ke:</strong><br>
                {{ $batch->bank_name }} - {{ $batch->bank_account_number }}<br>
                a.n {{ $batch->bank_account_name }}
            </div>

            <p style="margin-top: 15px; font-size: 13px; color: #666;">
                <em>Mohon segera lakukan pembayaran dan konfirmasi ke Admin agar status denda Anda menjadi <strong>LUNAS</strong> dan notifikasi ini berhenti dikirim.</em>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PO Genta<br>
        </div>
    </div>
</body>
</html>