<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengerjaan Kuis Interaktif</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 p-6 font-sans">
    <div class="max-w-2xl mx-auto space-y-6">

        <!-- KOTAK 1: Form Pengerjaan Kuis -->
        <div id="quiz-box" class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <div>
                    <h1 class="text-xl font-bold text-slate-900" id="quiz-title">Memuat Kuis...</h1>
                    <p class="text-sm text-slate-500">Pilih opsi jawaban yang tersedia.</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-semibold text-slate-400 uppercase">Sisa Waktu</span>
                    <div id="timer" class="text-2xl font-mono font-bold text-red-600">--:--</div>
                </div>
            </div>

            <form id="quiz-form">
                <div id="questions-container" class="space-y-6"></div>
                <div class="mt-8 pt-4 border-t flex justify-end">
                    <button type="button" id="btn-submit" onclick="submitQuiz()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-lg transition shadow-sm">
                        Kirim Jawaban
                    </button>
                </div>
            </form>
        </div>

        <!-- KOTAK 2: Review Hasil & Rekap Jawaban (Muncul Setelah Submit) -->
        <div id="result-box" class="hidden bg-white p-8 rounded-xl shadow-sm border border-slate-200 space-y-6">
            <div class="text-center pb-6 border-b">
                <span class="text-xs font-semibold tracking-wider uppercase text-slate-400">Hasil Evaluasi Kuis</span>
                <div id="result-score" class="text-6xl font-extrabold my-2 text-blue-600">0</div>
                <div id="result-status" class="inline-block px-4 py-1.5 rounded-full text-sm font-bold"></div>
            </div>

            <div>
                <h2 class="text-lg font-bold text-slate-900 mb-4">Rekapitulasi & Pembahasan Soal</h2>
                <div id="review-container" class="space-y-4"></div>
            </div>

            <div class="pt-4 border-t flex justify-center">
                <button onclick="window.location.reload()" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-lg font-medium text-sm transition">
                    Ulangi Tes Kuis
                </button>
            </div>
        </div>

    </div>

    <script>
        let attemptId = null;
        let timeLeft = 0;
        let timerInterval = null;

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        async function initQuiz() {
            try {
                const response = await fetch('/api/v1/quizzes/1/start', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ user_id: 1 })
                });

                const resData = await response.json();
                attemptId = resData.data.attempt_id;
                document.getElementById('quiz-title').innerText = resData.data.quiz.title;
                timeLeft = resData.data.quiz.duration_minutes * 60;
                
                startTimer();
                renderQuestions(resData.data.quiz.questions);
            } catch (error) {
                console.error("Gagal load kuis:", error);
            }
        }

        function renderQuestions(questions) {
            const container = document.getElementById('questions-container');
            container.innerHTML = '';
            questions.forEach((q, index) => {
                let optionsHtml = '';
                q.options.forEach(opt => {
                    optionsHtml += `
                        <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-slate-50 cursor-pointer transition">
                            <input type="radio" name="question_${q.id}" value="${opt.id}" 
                                onchange="saveAnswer(${q.id}, ${opt.id})" class="w-4 h-4 text-blue-600">
                            <span class="text-sm text-slate-700">${opt.option_text}</span>
                        </label>
                    `;
                });
                container.innerHTML += `
                    <div class="space-y-3">
                        <p class="font-semibold text-slate-800">${index + 1}. ${q.question_text} <span class="text-xs text-blue-600 font-normal">(${q.score_weight} poin)</span></p>
                        <div class="space-y-2">${optionsHtml}</div>
                    </div>
                `;
            });
        }

        async function saveAnswer(questionId, optionId) {
            try {
                await fetch(`/api/v1/attempts/${attemptId}/save-answer`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ question_id: questionId, selected_option_id: optionId })
                });
            } catch (error) {
                console.error("Gagal simpan jawaban:", error);
            }
        }

        function startTimer() {
            const timerEl = document.getElementById('timer');
            timerInterval = setInterval(() => {
                timeLeft--;
                let m = Math.floor(timeLeft / 60);
                let s = timeLeft % 60;
                timerEl.innerText = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    alert('Waktu pengerjaan habis!');
                    submitQuiz();
                }
            }, 1000);
        }

        async function submitQuiz() {
            clearInterval(timerInterval);
            document.getElementById('btn-submit').disabled = true;
            document.getElementById('btn-submit').innerText = 'Mengoreksi...';

            try {
                const response = await fetch(`/api/v1/attempts/${attemptId}/submit`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                });
                const result = await response.json();

                if (result.status === 'success') {
                    // Sembunyikan form kuis, tampilkan hasil review
                    document.getElementById('quiz-box').classList.add('hidden');
                    document.getElementById('result-box').classList.remove('hidden');

                    document.getElementById('result-score').innerText = result.data.total_score;
                    const statusEl = document.getElementById('result-status');
                    
                    if (result.data.is_passed) {
                        statusEl.innerText = 'LULUS (MEMENUHI PASSING GRADE)';
                        statusEl.className = 'inline-block px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700';
                    } else {
                        statusEl.innerText = 'TIDAK LULUS';
                        statusEl.className = 'inline-block px-4 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700';
                    }

                    // Render pembahasan soal satu per satu
                    const reviewContainer = document.getElementById('review-container');
                    reviewContainer.innerHTML = '';

                    result.data.review.forEach((item, idx) => {
                        const isCorrect = item.is_correct;
                        const borderColor = isCorrect ? 'border-emerald-300 bg-emerald-50/50' : 'border-rose-300 bg-rose-50/50';
                        const badgeColor = isCorrect ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800';
                        const badgeText = isCorrect ? `✓ Benar (+${item.score_weight})` : '✗ Salah (+0)';

                        reviewContainer.innerHTML += `
                            <div class="p-4 rounded-xl border ${borderColor} space-y-2">
                                <div class="flex justify-between items-start">
                                    <p class="font-semibold text-slate-900 text-sm">${idx + 1}. ${item.question_text}</p>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-md ${badgeColor}">${badgeText}</span>
                                </div>
                                <div class="text-xs space-y-1 pt-1">
                                    <p class="text-slate-600">Jawaban Kamu: <strong class="${isCorrect ? 'text-emerald-700' : 'text-rose-700'}">${item.student_answer}</strong></p>
                                    ${!isCorrect ? `<p class="text-slate-500">Kunci Jawaban Benar: <strong class="text-slate-800">${item.correct_answer}</strong></p>` : ''}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error("Gagal submit kuis:", error);
            }
        }

        window.onload = initQuiz;
    </script>
</body>
</html>