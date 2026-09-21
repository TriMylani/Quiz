<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\Hash;

class QuizSeeder extends Seeder {
    public function run(): void {
        $user = User::firstOrCreate(
            ['email' => 'student@test.com'],
            ['name' => 'Siswa Penguji', 'password' => Hash::make('password123'), 'role' => 'student']
        );

        $quiz = Quiz::create([
            'title' => 'Kuis Pengujian Software QA',
            'description' => 'Evaluasi konsep dasar software testing.',
            'duration_minutes' => 5, // Durasi 5 menit
            'passing_grade' => 70.00,
            'is_published' => true,
        ]);

        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Metode pengujian yang menguji batas nilai ekstrem minimum dan maksimum disebut...',
            'score_weight' => 50,
        ]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Boundary Value Analysis', 'is_correct' => true]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Equivalence Partitioning', 'is_correct' => false]);

        $q2 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Status kode HTTP yang menunjukkan request berhasil dan data baru terbuat adalah...',
            'score_weight' => 50,
        ]);
        Option::create(['question_id' => $q2->id, 'option_text' => '200 OK', 'is_correct' => false]);
        Option::create(['question_id' => $q2->id, 'option_text' => '201 Created', 'is_correct' => true]);
    }
}