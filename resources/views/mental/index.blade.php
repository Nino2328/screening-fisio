@extends('layouts.app')
@section('title', 'Mental Assessment')
@section('page-title', 'Mental Assessment')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manajemen Mental Assessment</h1>
        <a href="{{ route('mental.create') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all"
            style="background:#00E676; color:#0E1419">
            + Tambah Assessment
        </a>
    </div>

    @if ($assessments->count())
        <div class="overflow-x-auto rounded-xl" style="border: 1px solid rgba(0,230,118,0.15)">
            <table class="w-full text-sm">
                <thead style="background:rgba(0,230,118,0.05); border-bottom: 1px solid rgba(0,230,118,0.15)">
                    <tr>
                        <th class="px-4 py-3 text-left">Atlet</th>
                        <th class="px-4 py-3 text-left">Tanggal Assessment</th>
                        <th class="px-4 py-3 text-center">Rata-rata Skor</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assessments as $assessment)
                        <tr style="border-bottom: 1px solid rgba(0,230,118,0.1)">
                            <td class="px-4 py-3">
                                <div class="font-semibold">{{ $assessment->name }}</div>
                                <div class="text-xs" style="color:#7A8D9E">{{ $assessment->user->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $assessment->assessment_date->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                    style="background:rgba(0,230,118,0.1); color:#00E676">
                                    {{ $assessment->average_score }}/5.0
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('mental.show', $assessment->id) }}"
                                    class="text-xs px-2 py-1 rounded hover:underline" style="color:#00E676">
                                    Detail
                                </a>
                                <a href="{{ route('mental.edit', $assessment->id) }}"
                                    class="text-xs px-2 py-1 rounded hover:underline" style="color:#FFB700">
                                    Edit
                                </a>
                                <form action="{{ route('mental.destroy', $assessment->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus assessment ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs px-2 py-1 rounded hover:underline" style="color:#FF6B6B">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $assessments->links() }}
        </div>
    @else
        <div class="text-center py-12 rounded-xl"
            style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
            <p class="text-sm mb-4" style="color:#7A8D9E">Belum ada mental assessment</p>
            <a href="{{ route('mental.create') }}"
                class="inline-block px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                style="background:#00E676; color:#0E1419">
                Buat Assessment Pertama
            </a>
        </div>
    @endif

@endsection