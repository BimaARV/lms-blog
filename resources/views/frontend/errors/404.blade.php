<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waduh! Nyasar Lu, Bos!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .float-anim { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="bg-slate-900 text-white flex items-center justify-center min-h-screen overflow-hidden">
    <div class="text-center px-4">
        <h1 class="text-9xl font-black text-indigo-500 float-anim">404</h1>
        <p class="text-2xl font-bold mt-4">KAGAK ADA HALAMANNYA, PEKOK!</p>
        <p class="text-slate-400 mt-2 max-w-md mx-auto">
            Lu nyari apa sih? Harta karun? Atau mau nyari jalan pulang? <br> 
            Halaman yang lu cari udah ilang, mungkin dibawa kabur sama mantan lu.
        </p>
        <div class="mt-8">
            <a href="/" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full transition-all transform hover:scale-110 inline-block">
                Balik ke Home, Bangsat!
            </a>
        </div>
    </div>
</body>
</html>
