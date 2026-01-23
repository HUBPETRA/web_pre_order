<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; }
        .header { background: #dc2626; color: white; padding: 20px; text-align: center; } /* Merah */
        .content { padding: 20px; color: #333; line-height: 1.6; }
        .warning-box { background: #fef2f2; border-left: 5px solid #dc2626; padding: 15px; margin: 15px 0; }
        .footer { background: #f9fafb; padding: 15px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">⚠️ Target Belum Tercapai</h2>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $quota->fungsio->name }}</strong>,</p>
            <p>Sistem mendeteksi bahwa kuota penjualan Anda untuk PO <strong>{{ $batchName }}</strong> masih di bawah target.</p>

            <div class="warning-box">
                <p style="margin:0;"><strong>Target Anda:</strong> {{ $quota->target_qty }} Pcs</p>
                <p style="margin:0;"><strong>Terjual:</strong> {{ $quota->achieved_qty ?? 0 }} Pcs</p>
                <hr style="border:0; border-top:1px dashed #ccc; margin:10px 0;">
                <p style="margin:0; color: #dc2626; font-weight: bold;">Kekurangan: {{ $quota->target_qty - ($quota->achieved_qty ?? 0) }} Pcs</p>
            </div>

            <p>Segera penuhi target sebelum PO ditutup untuk menghindari denda</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PO Genta<br>
        </div>
    </div>
</body>
</html>