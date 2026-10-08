{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — FootScreen Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&family=Space+Mono&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/screening.css') }}">
</head>

<body style="background:#080C10; min-height:100vh; display:flex; align-items:center; justify-content:center">

    <div class="w-full max-w-sm mx-4">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4"
                style="background:#00E676">⚽</div>
            <h1 style="font-family:'Bebas Neue',sans-serif; font-size:28px; letter-spacing:4px; color:#00E676">
                FootScreen Pro</h1>
            <p class="text-sm mt-1" style="color:#7A8D9E">Sistem Screening Fisioterapi Sepak Bola</p>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl p-8" style="background:#0E1419; border:1px solid rgba(0,230,118,0.2)">
            <h2 style="font-family:'Bebas Neue',sans-serif; font-size:22px; letter-spacing:2px; margin-bottom:24px">
                Masuk ke Akun
            </h2>

            @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl text-sm"
                    style="background:rgba(255,68,68,0.1); border:1px solid rgba(255,68,68,0.3); color:#FF4444">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Role Selector Dropdown --}}
                <div class="form-group mb-6">
                    <label for="role">Pilih Role (Opsional)</label>
                    <select id="role" onchange="fillLoginData(this.value)"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); color:#E8F0F7; width:100%; padding:12px; border-radius:8px; font-size:14px; cursor:pointer">
                        <option value="">-- Pilih untuk Quick Login --</option>
                        <option value="admin">👨‍💼 Admin</option>
                        <option value="fisio">🏥 Fisioterapis</option>
                        <option value="pelatih">⚽ Pelatih</option>
                    </select>
                    <p class="text-xs mt-2" style="color:#7A8D9E">💡 Pilih role untuk auto-fill email & password</p>
                </div>

                {{-- Divider --}}
                <div class="flex items-center gap-3 mb-6">
                    <div style="flex:1; height:1px; background:rgba(0,230,118,0.2)"></div>
                    <span style="color:#7A8D9E; font-size:12px">ATAU</span>
                    <div style="flex:1; height:1px; background:rgba(0,230,118,0.2)"></div>
                </div>

                <div class="form-group mb-4">
                    <label>Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        placeholder="email@example.com" required autofocus>
                </div>
                <div class="form-group mb-6">
                    <label>Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-sm cursor-pointer" style="color:#7A8D9E">
                        <input type="checkbox" name="remember" class="rounded"> Ingat saya
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm hover:text-white transition"
                        style="color:#00E676">
                        Lupa password?
                    </a>
                </div>
                <button type="submit" class="btn-primary w-full py-3">Masuk</button>
            </form>

            <p class="text-center text-sm mt-6" style="color:#7A8D9E">
                Belum punya akun?
                <a href="{{ route('register') }}" class="hover:text-white transition" style="color:#00E676">Daftar</a>
            </p>
        </div>
    </div>

    <script>
        const loginData = {
            admin: {
                email: 'admin@footscreen.com',
                password: 'password123',
                label: '👨‍💼 Admin'
            },
            fisio: {
                email: 'fisio@footscreen.com',
                password: 'password123',
                label: '🏥 Fisioterapis'
            },
            pelatih: {
                email: 'pelatih@footscreen.com',
                password: 'password123',
                label: '⚽ Pelatih'
            }
        };

        function fillLoginData(role) {
            if (!role) {
                // Clear if no role selected
                document.getElementById('email').value = '';
                document.getElementById('password').value = '';
                return;
            }

            const data = loginData[role];
            document.getElementById('email').value = data.email;
            document.getElementById('password').value = data.password;

            // Show toast notification
            showNotification(`✅ Siap login sebagai ${data.label}`);
        }

        function showNotification(message) {
            // Create and show notification
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #00E676;
                color: #080C10;
                padding: 12px 20px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);

            // Add animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(style);

            // Remove after 3 seconds
            setTimeout(() => toast.remove(), 3000);
        }
    </script>
</body>

</html>