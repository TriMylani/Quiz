<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use App\Models\Option;
use Carbon\Carbon;

class QuizApiController extends Controller
{
    public function startQuiz(Request $request, $quizId)
    {
        $quiz = Quiz::with(['questions.options' => function ($q) {
            $q->select('id', 'question_id', 'option_text'); // Sembunyikan kunci jawaban
        }])->findOrFail($quizId);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $request->user_id ?? 1,
            'started_at' => Carbon::now(),
            'status' => 'in_progress',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'attempt_id' => $attempt->id,
                'quiz' => $quiz
            ]
        ], 201);
    }

    public function saveAnswer(Request $request, $attemptId)
    {
        $answer = StudentAnswer::updateOrCreate(
            ['attempt_id' => $attemptId, 'question_id' => $request->question_id],
            ['selected_option_id' => $request->selected_option_id]
        );

        return response()->json(['status' => 'success', 'data' => $answer], 200);
    }

    public function submitQuiz(Request $request, $attemptId)
    {
        $attempt = QuizAttempt::with('quiz.questions.options')->findOrFail($attemptId);

        if ($attempt->status === 'completed') {
            return response()->json(['status' => 'error', 'message' => 'Kuis sudah pernah dikirimkan sebelumnya.'], 409);
        }

        $totalScore = 0;
        $studentAnswers = StudentAnswer::where('attempt_id', $attempt->id)->get();
        $review = [];

        foreach ($attempt->quiz->questions as $question) {
            $ans = $studentAnswers->firstWhere('question_id', $question->id);
            $selectedOptId = $ans ? $ans->selected_option_id : null;
            
            // Ambil data opsi yang dipilih siswa
            $selectedOption = $question->options->firstWhere('id', $selectedOptId);
            // Ambil data opsi yang merupakan kunci jawaban benar
            $correctOption = $question->options->firstWhere('is_correct', true);

            $isCorrect = ($selectedOption && $selectedOption->is_correct);

            if ($isCorrect) {
                $totalScore += $question->score_weight;
            }

            // Susun detail rekap untuk dikirim ke frontend
            $review[] = [
                'question_text' => $question->question_text,
                'score_weight' => $question->score_weight,
                'student_answer' => $selectedOption ? $selectedOption->option_text : 'Tidak dijawab',
                'correct_answer' => $correctOption ? $correctOption->option_text : '-',
                'is_correct' => $isCorrect
            ];
        }

        $attempt->update([
            'submitted_at' => Carbon::now(),
            'total_score' => $totalScore,
            'status' => 'completed',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_score' => $totalScore,
                'is_passed' => $totalScore >= $attempt->quiz->passing_grade,
                'review' => $review
            ]
        ], 200);
    }

    // Tampilan Dashboard Admin
    public function adminDashboard()
    {
        $quiz = \App\Models\Quiz::with(['questions.options'])->find(1);
        $attempts = \App\Models\QuizAttempt::where('quiz_id', 1)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin', compact('quiz', 'attempts'));
    }

    // Aksi Simpan Soal Baru dari Admin
    public function storeQuestion(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'question_text' => 'required|string',
            'score_weight' => 'required|numeric|min:1',
            'options' => 'required|array|min:2',
            'correct_option' => 'required|numeric'
        ]);

        $question = \App\Models\Question::create([
            'quiz_id' => 1,
            'question_text' => $request->question_text,
            'score_weight' => $request->score_weight,
        ]);

        foreach ($request->options as $index => $optText) {
            if (!empty($optText)) {
                \App\Models\Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optText,
                    'is_correct' => ($index == $request->correct_option),
                ]);
            }
        }

        return redirect('/admin')->with('success', 'Soal baru berhasil ditambahkan!');
    }

    // Form / Halaman Edit Soal
    public function editQuestion($id)
    {
        $question = \App\Models\Question::with('options')->findOrFail($id);
        return view('edit_question', compact('question'));
    }

    // Aksi Update Soal ke Database
    public function updateQuestion(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'question_text' => 'required|string',
            'score_weight' => 'required|numeric|min:1',
            'options' => 'required|array|min:2',
            'correct_option' => 'required'
        ]);

        $question = \App\Models\Question::findOrFail($id);
        $question->update([
            'question_text' => $request->question_text,
            'score_weight' => $request->score_weight,
        ]);

        // Hapus opsi lama lalu masukkan opsi yang baru diedit
        $question->options()->delete();

        foreach ($request->options as $index => $optText) {
            if (!empty($optText)) {
                \App\Models\Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optText,
                    'is_correct' => ($index == $request->correct_option),
                ]);
            }
        }

        return redirect('/admin')->with('success', 'Soal berhasil diperbarui!');
    }
    // Update Durasi Waktu & Passing Grade Kuis oleh Admin
    public function updateQuizSettings(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'duration_minutes' => 'required|numeric|min:1|max:180',
            'passing_grade' => 'required|numeric|min:0|max:100',
        ]);

        $quiz = \App\Models\Quiz::findOrFail($id);
        $quiz->update([
            'duration_minutes' => $request->duration_minutes,
            'passing_grade' => $request->passing_grade,
        ]);

        return redirect('/admin')->with('success', 'Pengaturan waktu & passing grade berhasil diperbarui!');
    }
}