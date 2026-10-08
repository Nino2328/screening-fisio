<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FootScreen Pro') — Screening Fisioterapi</title>

    {{-- Tailwind CSS CDN (ganti dengan Vite jika sudah setup npm) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            green: '#00E676',
                            gdark: '#00C853',
                            bg: '#080C10',
                            surf: '#0E1419',
                            surf2: '#141B22',
                            surf3: '#1C252F',
                            border: 'rgba(0,230,118,0.15)',
                        }
                    },
                    fontFamily: {
                        display: ['Bebas Neue', 'sans-serif'],
                        mono2: ['Space Mono', 'monospace'],
                        body: ['DM Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    {{-- Google Fonts --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/screening.css') }}">

    @stack('styles')
</head>

<body class="bg-brand-bg text-white font-body" style="background:#080C10">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside id="sidebar" class="fixed left-0 top-0 bottom-0 w-64 flex flex-col z-50 transition-transform duration-300"
        style="background:#0E1419; border-right:1px solid rgba(0,230,118,0.15)">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-6" style="border-bottom:1px solid rgba(0,230,118,0.15)">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                style="background:#00E676">⚽</div>
            <div>
                <h1 class="font-display text-xl tracking-widest" style="color:#00E676">FootScreen</h1>
                <span class="text-xs tracking-widest uppercase" style="color:#7A8D9E">Physiotherapy Pro</span>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-3 px-3">

            {{-- Overview --}}
            <div class="mb-1">
                <p class="text-xs uppercase tracking-widest px-2 mb-2 font-mono2" style="color:#7A8D9E">Overview</p>
                <x-nav-item route="dashboard.index" icon="📊" label="Dashboard" />
                <x-nav-item route="athletes.index" icon="👥" label="Data Atlet" :badge="$incompleteAthletes ?? 0" />
            </div>

            {{-- MENU FISIOTERAPI (Fisio & Admin) --}}
            @if(in_array(auth()->user()->role, ['admin', 'fisio'], true))
                {{-- Tes Fisik --}}
                <div class="mt-4 mb-1">
                    <p class="text-xs uppercase tracking-widest px-2 mb-2 font-mono2" style="color:#00E676">Screening
                        Fisioterapi</p>
                    <x-nav-item route="tests.index" icon="🧪" label="Semua Tes" />
                    <x-nav-item route="forms.endurance" icon="🏃" label="Endurance (YoYo)" />
                    <x-nav-item route="forms.speed" icon="⚡" label="Speed 50M" />
                    <x-nav-item route="forms.agility" icon="🔄" label="Agility Test" />
                    <x-nav-item route="forms.strength" icon="💪" label="Strength" />
                    <x-nav-item route="forms.power" icon="🦵" label="Power Jump" />
                </div>

                {{-- Tes Fungsional --}}
                <div class="mt-4 mb-1">
                    <p class="text-xs uppercase tracking-widest px-2 mb-2 font-mono2" style="color:#00E676">Tes
                        Fungsional</p>
                    <x-nav-item route="forms.accuracy" icon="🎯" label="Akurasi Tendangan" />
                    <x-nav-item route="forms.reaction" icon="👁️" label="Kecepatan Reaksi" />
                    <x-nav-item route="forms.coordination" icon="🌀" label="Koordinasi" />
                    <x-nav-item route="forms.flexibility" icon="🦶" label="Fleksibilitas Ankle" />
                    <x-nav-item route="forms.balance" icon="⚖️" label="Y Balance Test" />
                </div>

                {{-- Output Laporan Fisio --}}
                <div class="mt-4 mb-1">
                    <p class="text-xs uppercase tracking-widest px-2 mb-2 font-mono2" style="color:#7A8D9E">Output</p>
                    <x-nav-item route="report.index" icon="📋" label="Laporan Fisioterapi" />
                </div>
            @endif

            {{-- MENU PELATIH (Pelatih & Admin) --}}
            @if(in_array(auth()->user()->role, ['admin', 'pelatih'], true))
                {{-- Assessment Mental --}}
                <div class="mt-4 mb-1">
                    <p class="text-xs uppercase tracking-widest px-2 mb-2 font-mono2" style="color:#FFB700">Assessment
                        Mental</p>
                    <x-nav-item route="mental.index" icon="🧠" label="Mental Assessment" />
                    <x-nav-item route="mental.create" icon="➕" label="Tambah Assessment" />
                </div>

                {{-- Assessment Taktik --}}
                <div class="mt-4 mb-1">
                    <p class="text-xs uppercase tracking-widest px-2 mb-2 font-mono2" style="color:#FF6B6B">Assessment
                        Taktik</p>
                    <x-nav-item route="taktik.index" icon="⚽" label="Taktik Assessment" />
                    <x-nav-item route="taktik.create" icon="➕" label="Tambah Assessment" />
                </div>

                {{-- Output Laporan Pelatih --}}
                <div class="mt-4 mb-1">
                    <p class="text-xs uppercase tracking-widest px-2 mb-2 font-mono2" style="color:#7A8D9E">Output</p>
                    <x-nav-item route="reports.index" icon="📋" label="Laporan Assessment" />
                </div>
            @endif
        </nav>

        {{-- User Info --}}
        <div class="p-4" style="border-top:1px solid rgba(0,230,118,0.15)">
            <div class="flex items-center gap-3 rounded-xl p-3" style="background:#141B22">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                    style="background:linear-gradient(135deg,#00E676,#4FC3F7)">
                    {{ strtoupper(substr(auth()->user()->name ?? 'FT', 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold truncate">{{ auth()->user()->name ?? 'Fisioterapis' }}</p>
                    <p class="text-xs" style="color:#7A8D9E">{{ auth()->user()->role ?? 'Admin' }}</p>
                </div>
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="ml-auto text-xs hover:text-red-400 transition-colors" style="color:#7A8D9E"
                    title="Logout">↩</a>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </aside>

    {{-- ===================== MAIN CONTENT ===================== --}}
    <div class="ml-64 min-h-screen flex flex-col" id="main-content">

        {{-- Topbar --}}
        <header class="sticky top-0 z-40 flex items-center justify-between px-7 h-16"
            style="background:#0E1419; border-bottom:1px solid rgba(0,230,118,0.15)">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="md:hidden p-2 rounded-lg border text-sm"
                    style="border-color:rgba(0,230,118,0.2);color:#7A8D9E">☰</button>
                <h2 class="font-display text-xl tracking-widest">@yield('page-title', 'Dashboard')</h2>
            </div>
            <div class="flex items-center gap-3">
                <!-- {{-- Search --}}
                <div class="relative hidden md:block">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm">🔍</span>
                    <input type="text" placeholder="Cari atlet..."
                        class="pl-9 pr-4 py-2 text-sm rounded-lg w-52 outline-none focus:ring-1" style="background:#141B22; border:1px solid rgba(0,230,118,0.15); color:#E8F0F7;
                              --tw-ring-color:#00E676">
                </div> -->
                @if(in_array(auth()->user()->role, ['admin', 'fisio'], true))
                    <a href="{{ route('forms.endurance') }}"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px"
                        style="background:#00E676; color:#080C10">
                        + Input Tes Fisik
                    </a>
                @elseif(in_array(auth()->user()->role, ['admin', 'pelatih'], true))
                    <div class="flex gap-2">
                        <a href="{{ route('mental.create') }}"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px"
                            style="background:#FFB700; color:#080C10">
                            + Mental
                        </a>
                        <a href="{{ route('taktik.create') }}"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px"
                            style="background:#FF6B6B; color:#fff">
                            + Taktik
                        </a>
                    </div>
                @endif
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mx-7 mt-5 px-4 py-3 rounded-xl text-sm flex items-center gap-2"
                style="background:rgba(0,230,118,0.1); border:1px solid rgba(0,230,118,0.3); color:#00E676">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-7 mt-5 px-4 py-3 rounded-xl text-sm"
                style="background:rgba(255,68,68,0.1); border:1px solid rgba(255,68,68,0.3); color:#FF4444">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 p-7">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="px-7 py-4 text-xs text-center" style="color:#7A8D9E; border-top:1px solid rgba(0,230,118,0.08)">
            FootScreen Pro © {{ date('Y') }} — Sistem Screening Fisioterapi Sepak Bola
        </footer>
    </div>

    {{-- Toast Notification --}}
    <div id="toast" class="fixed bottom-6 right-6 px-5 py-3 rounded-xl text-sm flex items-center gap-2 z-50
                        translate-y-20 opacity-0 transition-all duration-300 pointer-events-none"
        style="background:#0E1419; border:1px solid #00E676; box-shadow:0 8px 24px rgba(0,0,0,0.5)">
        <span id="toast-icon">✅</span>
        <span id="toast-msg">Data berhasil disimpan!</span>
    </div>

    {{-- Global JS --}}
    <script src="{{ asset('js/screening.js') }}"></script>
    @stack('scripts')
</body>

</html>