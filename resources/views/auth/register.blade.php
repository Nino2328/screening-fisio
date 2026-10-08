<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — FootScreen Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/screening.css') }}">
</head>

<body style="background:#080C10; min-height:100vh; display:flex; align-items:center; justify-content:center">
    <div class="w-full max-w-sm mx-4">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4"
                style="background:#00E676">⚽</div>
            <h1 style="font-family:'Bebas Neue',sans-serif; font-size:28px; letter-spacing:4px; color:#00E676">
                FootScreen Pro</h1>
            <p class="text-sm mt-1" style="color:#7A8D9E">Buat akun fisioterapis baru</p>
        </div>
        <div class="rounded-2xl p-8" style="background:#0E1419; border:1px solid rgba(0,230,118,0.2)">
            <h2 style="font-family:'Bebas Neue',sans-serif; font-size:22px; letter-spacing:2px; margin-bottom:24px">
                Daftar Akun</h2>
            @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl text-sm"
                    style="background:rgba(255,68,68,0.1); border:1px solid rgba(255,68,68,0.3); color:#FF4444">
                    {{ $errors->first() }}
                </div>
            @endif
            <form method="POST" action="{{ route('register') }}">@csrf
                <div class="form-group mb-4"><label>Nama Lengkap</label><input type="text" name="name"
                        value="{{ old('name') }}" placeholder="Nama fisioterapis" required autofocus></div>
                <div class="form-group mb-4"><label>Email</label><input type="email" name="email"
                        value="{{ old('email') }}" placeholder="email@example.com" required></div>
                <div class="form-group mb-4"><label>Password</label><input type="password" name="password"
                        placeholder="Min. 8 karakter" required></div>
                <div class="form-group mb-6"><label>Konfirmasi Password</label><input type="password"
                        name="password_confirmation" placeholder="Ulangi password" required></div>
                <button type="submit" class="btn-primary w-full py-3">Buat Akun</button>
            </form>
            <p class="text-center text-sm mt-6" style="color:#7A8D9E">
                Sudah punya akun? <a href="{{ route('login') }}" class="hover:text-white transition"
                    style="color:#00E676">Masuk</a>
            </p>
        </div>
    </div>
</body>

</html>