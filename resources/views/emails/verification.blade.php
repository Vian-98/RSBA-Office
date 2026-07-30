<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Aktivasi Email</title>
</head>
<body style="font-family: sans-serif; background-color: #f8fafc; padding: 40px; margin: 0;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <h2 style="color: #4f46e5; margin-top: 0; font-size: 20px; font-weight: 800;">RS BINTANG AMIN</h2>
        <p style="font-size: 14px; color: #475569; line-height: 1.6;">Halo <strong>{{ $name }}</strong>,</p>
        <p style="font-size: 14px; color: #475569; line-height: 1.6;">Anda menerima email ini karena akun Anda memerlukan aktivasi email untuk mengaktifkan fitur penerimaan slip gaji otomatis, notifikasi cuti, dan pemulihan akun.</p>
        
        <div style="background-color: #f1f5f9; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0;">
            <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin: 0 0 8px 0; font-weight: bold;">Kode Aktivasi Anda</p>
            <span style="font-size: 32px; font-weight: 900; letter-spacing: 0.1em; color: #1e1b4b; display: block;">{{ $code }}</span>
        </div>

        <p style="font-size: 12px; color: #94a3b8; line-height: 1.6; margin: 24px 0 0 0; border-top: 1px solid #f1f5f9; padding-top: 16px;">Kode ini hanya berlaku selama 15 menit. Mohon tidak membagikan kode ini kepada siapa pun.</p>
    </div>
</body>
</html>
