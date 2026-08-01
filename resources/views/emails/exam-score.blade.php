<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { width: 80%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        .header { text-align: center; color: #4f46e5; }
        .score-box { font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0; padding: 10px; background: #f3f4f6; border-radius: 5px; }
        .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hasil Ujian</h1>
        </div>
        <p>Halo, Murid Tolol!</p>
        <p>Lu baru aja nyelesein ujian: <strong>{{ $examName }}</strong></p>
        <div class="score-box">
            Skor Lu: {{ $score }} / {{ $maxScore }}
        </div>
        <p>Cek detail lengkapnya di file PDF yang terlampir. Jangan curang lagi lu!</p>
        <div class="footer">
            Kirim otomatis oleh Sistem LMS-Blog Bos Bima yang Galak.
        </div>
    </div>
</body>
</html>
