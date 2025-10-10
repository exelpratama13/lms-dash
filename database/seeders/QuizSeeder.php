<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\CourseContent;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dapatkan ID CourseContent yang ingin Anda kaitkan dengan Kuis
        // Asumsi: Kita ingin mengaitkan kuis ini dengan CourseContent pertama yang ada.
        $courseContent = CourseContent::first();

        // Pastikan ada CourseContent sebelum mencoba membuat Quiz
        if (!$courseContent) {
            $this->command->warn('Tidak ada CourseContent ditemukan. Lewati QuizSeeder.');
            return;
        }

        // 2. Buat Quiz (terikat pada course_content_id)
        $quiz = Quiz::create([
            'title' => 'Kuis Akhir Modul: UI/UX Foundations',
            'course_content_id' => $courseContent->id,
        ]);

        // 3. Data Pertanyaan dan Opsi Jawaban (Terstruktur)
        $questionsData = [
            [
                'question_text' => 'Apakah kepanjangan dari istilah UI dalam desain produk digital?',
                'options' => [
                    ['option_text' => 'User Index', 'is_correct' => false],
                    ['option_text' => 'Universal Icon', 'is_correct' => false],
                    ['option_text' => 'User Interface', 'is_correct' => true],
                    ['option_text' => 'Underlying Information', 'is_correct' => false],
                ],
            ],
            [
                'question_text' => 'Manakah yang merupakan fokus utama dari User Experience (UX)?',
                'options' => [
                    ['option_text' => 'Warna dan Tipografi visual.', 'is_correct' => false],
                    ['option_text' => 'Ikon yang konsisten.', 'is_correct' => false],
                    ['option_text' => 'Kemudahan dan kepuasan pengguna saat menggunakan produk.', 'is_correct' => true],
                    ['option_text' => 'Kecepatan loading halaman.', 'is_correct' => false],
                ],
            ],
        ];

        // 4. Loop untuk membuat Pertanyaan dan Opsi
        foreach ($questionsData as $questionData) {
            
            // Buat Question (terikat pada quiz_id)
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => $questionData['question_text'],
            ]);

            // Buat QuestionOptions (terikat pada question_id)
            foreach ($questionData['options'] as $optionData) {
                $question->options()->create($optionData);
            }
        }
    }
}