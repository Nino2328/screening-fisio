@extends('layouts.app')
@section('title', 'Data Atlet')
@section('page-title', 'Data Atlet')

@section('content')

    {{-- Header dengan tombol tambah --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm" style="color:#7A8D9E">Kelola data atlet yang mengikuti screening fisioterapi</p>
        </div>
        @if(auth()->user()->role !== 'pelatih')
        <button onclick="openAddAthlete()"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px"
            style="background:#00E676; color:#080C10">
            + Tambah Atlet
        </button>
        @endif
    </div>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <x-stat-card icon="👥" value="{{ $athletes->total() }}" label="Total Atlet" delta="+" color="green" />
        <x-stat-card icon="🧪" value="{{ rand(15, 45) }}" label="Tes Selesai" delta="Bulan Ini" color="blue" />
        <x-stat-card icon="📈" value="{{ rand(70, 95) }}%" label="Compliance Rate" delta="7%" deltaUp="1" color="green" />
    </div>

    {{-- Athletes Table --}}
    <div class="rounded-xl p-6" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <h3 class="font-display text-lg tracking-widest mb-5 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full inline-block" style="background:#00E676"></span>
            Daftar Atlet
        </h3>

        @if($athletes->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="border-bottom:2px solid rgba(0,230,118,0.15)">
                            <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Nama</th>
                            <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Posisi</th>
                            <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Usia</th>
                            <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Tinggi/Berat</th>
                            <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Nomor Telepon</th>
                            <th class="text-center py-3 px-4 font-semibold" style="color:#7A8D9E">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($athletes as $athlete)
                            <tr style="border-bottom:1px solid rgba(0,230,118,0.08)">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                            style="background:rgba(0,230,118,0.2); color:#00E676">
                                            {{ strtoupper(substr($athlete->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-white">{{ $athlete->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold"
                                        style="background:rgba(79,195,247,0.1); color:#4FC3F7">
                                        {{ $athlete->position ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">{{ $athlete->age ?? '-' }} thn</td>
                                <td class="py-4 px-4">
                                    {{ $athlete->height ?? '-' }} cm / {{ $athlete->weight ?? '-' }} kg
                                </td>
                                <td class="py-4 px-4">{{ $athlete->phone ?? '-' }}</td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('athletes.show', $athlete->id) }}"
                                            class="px-3 py-1 rounded text-xs font-semibold transition hover:bg-opacity-80"
                                            style="background:rgba(0,230,118,0.2); color:#00E676">
                                            Lihat
                                        </a>
                                        @if(auth()->user()->role !== 'pelatih')
                                         <button onclick="editAthlete({{ $athlete->id }})"
                                            class="px-3 py-1 rounded text-xs font-semibold transition hover:bg-opacity-80"
                                            style="background:rgba(255,152,0,0.2); color:#FFB74D">
                                            Edit
                                        </button>
                                        <button onclick="deleteAthlete({{ $athlete->id }})"
                                            class="px-3 py-1 rounded text-xs font-semibold transition hover:bg-opacity-80"
                                            style="background:rgba(255,68,68,0.2); color:#FF4444">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6 flex items-center justify-between">
                <p class="text-xs" style="color:#7A8D9E">
                    Menampilkan {{ $athletes->firstItem() }} hingga {{ $athletes->lastItem() }} dari {{ $athletes->total() }}
                    data
                </p>
                <div class="flex gap-2">
                    {{ $athletes->links('pagination::tailwind') }}
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-lg mb-2" style="color:#7A8D9E">Belum ada data atlet</p>
                <button onclick="openAddAthlete()"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px"
                    style="background:#00E676; color:#080C10">
                    Tambah Atlet Pertama
                </button>
            </div>
        @endif
    </div>

    {{-- Modal Tambah/Edit Atlet --}}
    <div id="athleteModal"
        class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 hidden transition-opacity duration-300"
        onclick="closeModal(event)">
        <div class="bg-brand-surf3 rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto"
            style="border:1px solid rgba(0,230,118,0.15)" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-display text-xl tracking-widest" id="modalTitle">Tambah Atlet Baru</h3>
                <button onclick="closeModal()" class="text-xl leading-none" style="color:#7A8D9E">✕</button>
            </div>

            {{-- Validation Errors --}}
            <div id="errorAlert" class="hidden mb-4 px-4 py-3 rounded-xl text-sm"
                style="background:rgba(255,68,68,0.1); border:1px solid rgba(255,68,68,0.3); color:#FF4444">
                <ul id="errorList" class="list-disc list-inside"></ul>
            </div>

            <form id="athleteForm" method="POST" action="{{ route('athletes.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="athleteMethod" name="_method" value="POST">
                <input type="hidden" id="athleteId" name="athlete_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nama --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-2">Nama Lengkap *</label>
                        <input type="text" id="fieldName" name="name" required
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676"
                            placeholder="Contoh: Aldo Pratama">
                    </div>

                    {{-- Posisi --}}
                    <div>
                        <label class="block text-sm font-semibold mb-2">Posisi</label>
                        <select id="fieldPosition" name="position"
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676">
                            <option value="">Pilih Posisi</option>
                            <option value="Kiper">Kiper</option>
                            <option value="Bek">Bek</option>
                            <option value="Gelandang">Gelandang</option>
                            <option value="Penyerang">Penyerang</option>
                        </select>
                    </div>

                    {{-- Tempat Lahir --}}
                    <div>
                        <label class="block text-sm font-semibold mb-2">Tempat Lahir</label>
                        <input type="text" id="fieldBirthPlace" name="birth_place"
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676"
                            placeholder="Yogyakarta">
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div class="mt-4">
                        <label class="block text-sm font-semibold mb-2">Tanggal Lahir</label>
                        <input type="date" id="fieldBirthDate" name="birth_date"
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676">
                    </div>

                    {{-- Usia --}}
                    <div>
                        <label class="block text-sm font-semibold mb-2">Usia (tahun)</label>
                        <input type="number" id="fieldAge" name="age" min="1" max="100"
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676"
                            placeholder="25">
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div>
                        <label class="block text-sm font-semibold mb-2">Jenis Kelamin</label>
                        <select id="fieldGender" name="gender"
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="M">Laki-laki</option>
                            <option value="F">Perempuan</option>
                        </select>
                    </div>

                    {{-- Tinggi --}}
                    <div>
                        <label class="block text-sm font-semibold mb-2">Tinggi (cm)</label>
                        <input type="number" id="fieldHeight" name="height" step="0.1" min="0"
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676"
                            placeholder="180">
                    </div>

                    {{-- Berat --}}
                    <div>
                        <label class="block text-sm font-semibold mb-2">Berat (kg)</label>
                        <input type="number" id="fieldWeight" name="weight" step="0.1" min="0"
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676"
                            placeholder="75">
                    </div>

                    {{-- Telepon --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-2">Nomor Telepon</label>
                        <input type="tel" id="fieldPhone" name="phone"
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676"
                            placeholder="08123456789">
                    </div>

                    {{-- Foto --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold mb-2">Foto Atlet</label>
                        <input type="file" id="fieldPhoto" name="photo" accept="image/*"
                            class="w-full px-4 py-2 rounded-lg outline-none focus:ring-1 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#00E676] file:text-[#080C10] hover:file:bg-opacity-80"
                            style="background:#141B22; border:1px solid rgba(0,230,118,0.15); --tw-ring-color:#00E676">
                        <p class="text-xs mt-1" style="color:#7A8D9E">Format: JPG, PNG, GIF. Maksimal 2MB.</p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" id="submitBtn"
                        class="flex-1 px-4 py-2 rounded-lg font-semibold transition hover:-translate-y-px"
                        style="background:#00E676; color:#080C10">
                        Simpan
                    </button>
                    <button type="button" onclick="closeModal()"
                        class="flex-1 px-4 py-2 rounded-lg font-semibold transition hover:bg-opacity-80"
                        style="background:#141B22; color:#7A8D9E">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openAddAthlete() {
            resetForm();
            document.getElementById('athleteForm').action = "{{ route('athletes.store') }}";
            document.getElementById('athleteMethod').value = "POST";
            document.getElementById('modalTitle').textContent = 'Tambah Atlet Baru';
            document.getElementById('submitBtn').textContent = 'Tambah Atlet';
            document.getElementById('athleteModal').classList.remove('hidden');
        }

        function editAthlete(id) {
            // Fetch athlete data untuk populate form
            fetch(`/athletes/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                }
            })
                .then(response => response.json())
                .then(athlete => {
                    document.getElementById('fieldName').value = athlete.name || '';
                    document.getElementById('fieldBirthPlace').value = athlete.birth_place || '';
                    document.getElementById('fieldBirthDate').value = athlete.birth_date || '';
                    document.getElementById('fieldPosition').value = athlete.position || '';
                    document.getElementById('fieldAge').value = athlete.age || '';
                    document.getElementById('fieldGender').value = athlete.gender || '';
                    document.getElementById('fieldHeight').value = athlete.height || '';
                    document.getElementById('fieldWeight').value = athlete.weight || '';
                    document.getElementById('fieldPhone').value = athlete.phone || '';

                    // Handle photo preview
                    const photoField = document.getElementById('fieldPhoto');
                    if (athlete.photo) {
                        // Show current photo info
                        photoField.setAttribute('data-current-photo', athlete.photo);
                        const photoLabel = photoField.nextElementSibling;
                        if (photoLabel) {
                            photoLabel.innerHTML = `<span style="color:#00E676">Foto saat ini: ${athlete.photo.split('/').pop()}</span><br>Format: JPG, PNG, GIF. Maksimal 2MB.`;
                        }
                    } else {
                        photoField.removeAttribute('data-current-photo');
                        const photoLabel = photoField.nextElementSibling;
                        if (photoLabel) {
                            photoLabel.innerHTML = 'Format: JPG, PNG, GIF. Maksimal 2MB.';
                        }
                    }

                    document.getElementById('athleteForm').action = `/athletes/${id}`;
                    document.getElementById('athleteMethod').value = "PUT";
                    document.getElementById('modalTitle').textContent = 'Edit Data Atlet';
                    document.getElementById('submitBtn').textContent = 'Update Atlet';
                    document.getElementById('athleteModal').classList.remove('hidden');
                })
                .catch(err => {
                    showError('Gagal memuat data atlet');
                    console.error(err);
                });
        }

        function deleteAthlete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data atlet ini? Data tidak dapat dipulihkan!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/athletes/${id}`;
                form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function resetForm() {
            document.getElementById('athleteForm').reset();
            document.getElementById('errorAlert').classList.add('hidden');
            document.getElementById('errorList').innerHTML = '';

            // Reset photo field
            const photoField = document.getElementById('fieldPhoto');
            photoField.removeAttribute('data-current-photo');
            const photoLabel = photoField.nextElementSibling;
            if (photoLabel) {
                photoLabel.innerHTML = 'Format: JPG, PNG, GIF. Maksimal 2MB.';
            }
        }

        function closeModal(event) {
            if (event && event.target.id !== 'athleteModal') return;
            document.getElementById('athleteModal').classList.add('hidden');
            resetForm();
        }

        function showError(message) {
            const errorList = document.getElementById('errorList');
            errorList.innerHTML = `<li>${message}</li>`;
            document.getElementById('errorAlert').classList.remove('hidden');
        }

        // Form submission dengan fallback ke regular form submission
        const form = document.getElementById('athleteForm');
        form.addEventListener('submit', function (e) {
            // Biarkan form submit secara normal; Laravel akan kembali ke daftar atlet.
            // Ini lebih simple dan reliable dibanding AJAX
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
@endpush