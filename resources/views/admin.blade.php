<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quiz Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 p-8 font-sans">
    <div class="max-w-6xl mx-auto space-y-8">
        
        <!-- Header Dashboard -->
        <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard Manajemen Kuis</h1>
                <p class="text-sm text-slate-500">Kelola butir soal dan pantau rekapitulasi nilai peserta.</p>
            </div>
            <div>
                <a href="/quiz" target="_blank" class="bg-blue-50 text-blue-600 hover:bg-blue-100 font-medium px-4 py-2 rounded-lg text-sm border border-blue-200 transition">
                    Pratinjau Kuis Siswa ↗
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
             <!-- Form Pengaturan Durasi & Passing Grade -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 h-fit mb-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4 pb-2 border-b flex items-center justify-between">
                    <span>Pengaturan Kuis</span>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-mono">Aktif: {{ $quiz->duration_minutes }} Menit</span>
                </h2>

                <form action="/admin/quizzes/{{ $quiz->id }}/settings" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Durasi Pengerjaan (Menit)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="duration_minutes" value="{{ $quiz->duration_minutes }}" min="1" max="180" required 
                                class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                            <span class="text-sm text-slate-500 font-medium">Menit</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Standar Kelulusan (Passing Grade)</label>
                        <input type="number" name="passing_grade" value="{{ (int)$quiz->passing_grade }}" min="0" max="100" required 
                            class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg text-sm transition">
                        Simpan Pengaturan Waktu
                    </button>
                </form>
            </div>
            <!-- Form Tambah Soal Baru -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 h-fit">
                <h2 class="text-lg font-bold text-slate-900 mb-4 pb-2 border-b">Tambah Soal Baru</h2>
                
                <form action="/admin/questions" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Pertanyaan</label>
                        <textarea name="question_text" rows="3" required placeholder="Tuliskan butir pertanyaan..." class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Bobot Nilai</label>
                        <input type="number" name="score_weight" value="50" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="space-y-2 pt-2">
                        <label class="block text-xs font-semibold uppercase text-slate-500">Pilihan Jawaban (Pilih Radio untuk Kunci)</label>
                        
                        <div class="flex items-center gap-2">
                            <input type="radio" name="correct_option" value="0" checked class="w-4 h-4 text-blue-600">
                            <input type="text" name="options[]" required placeholder="Opsi A (Kunci Jawaban)" class="flex-1 text-sm border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="radio" name="correct_option" value="1" class="w-4 h-4 text-blue-600">
                            <input type="text" name="options[]" required placeholder="Opsi B" class="flex-1 text-sm border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="radio" name="correct_option" value="2" class="w-4 h-4 text-blue-600">
                            <input type="text" name="options[]" placeholder="Opsi C (Opsional)" class="flex-1 text-sm border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 rounded-lg text-sm transition mt-4">
                        Simpan Soal ke Database
                    </button>
                </form>
            </div>

            <!-- Tabel Rekapitulasi Nilai & Daftar Soal Aktif -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b">
                        <h2 class="text-lg font-bold text-slate-900">Rekapitulasi Nilai Siswa</h2>
                        <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-semibold">
                            Total Selesai: {{ $attempts->count() }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500 font-semibold border-b text-xs uppercase">
                                <tr>
                                    <th class="p-3">Attempt ID</th>
                                    <th class="p-3">Waktu Submit</th>
                                    <th class="p-3">Total Skor</th>
                                    <th class="p-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($attempts as $att)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="p-3 font-mono font-medium text-slate-600">#ATT-{{ $att->id }}</td>
                                        <td class="p-3 text-slate-500">{{ $att->submitted_at ? $att->submitted_at->format('d M Y, H:i:s') : '-' }}</td>
                                        <td class="p-3 font-bold text-slate-800">{{ $att->total_score }} / 100</td>
                                        <td class="p-3">
                                            @if($att->total_score >= 70)
                                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">Lulus</span>
                                            @else
                                                <span class="px-2.5 py-1 bg-rose-100 text-rose-700 text-xs font-semibold rounded-full">Tidak Lulus</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-4 text-center text-slate-400">Belum ada siswa yang menyelesaikan kuis.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-md font-bold text-slate-900 mb-3">Daftar Soal Aktif di Kuis ({{ $quiz ? $quiz->questions->count() : 0 }} Soal)</h3>
                    <div class="space-y-3">
                        @if($quiz)
                            @foreach($quiz->questions as $i => $q)
                                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg text-sm border border-slate-200">
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $i+1 }}. {{ $q->question_text }} <span class="text-xs text-blue-600 font-semibold">({{ $q->score_weight }} Poin)</span></p>
                                    </div>
                                    <div>
                                        <a href="/admin/questions/{{ $q->id }}/edit" class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-800 font-semibold px-3 py-1.5 rounded transition">
                                            Edit Soal
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>