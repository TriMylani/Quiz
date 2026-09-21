<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model {
    protected $fillable = ['title', 'description', 'duration_minutes', 'passing_grade', 'is_published'];
    public function questions() { return $this->hasMany(Question::class); }
    public function attempts() { return $this->hasMany(QuizAttempt::class); }
}