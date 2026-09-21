<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Soal Kuis</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 p-8 font-sans">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="flex justify-between items-center mb-6 pb-2 border-b">
            <h1 class="text-xl font-bold text-slate-900">Edit Butir Soal</h1>
            <a href="/admin" class="text-sm text-slate-500 hover:text-slate-800">← Kembali ke Dashboard</a>
        </div>

        <form action="/admin/questions/{{ $question->id }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Pertanyaan</label>
                <textarea name="question_text" rows="3" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">{{ $question->question_text }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Bobot Nilai</label>
                <input type="number" name="score_weight" value="{{ $question->score_weight }}" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="space-y-2 pt-2">
                <label class="block text-xs font-semibold uppercase text-slate-500">Pilihan Jawaban (Tandai Radio untuk Kunci yang Benar)</label>
                
                @foreach($question->options as $index => $opt)
                    <div class="flex items-center gap-2">
                        <input type="radio" name="correct_option" value="{{ $index }}" {{ $opt->is_correct ? 'checked' : '' }} class="w-4 h-4 text-blue-600">
                        <input type="text" name="options[]" value="{{ $opt->option_text }}" required class="flex-1 text-sm border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                @endforeach
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg text-sm transition">
                    Simpan Perubahan
                </button>
                <a href="/admin" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-sm transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</body>
</html>