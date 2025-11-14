<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\User;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizAttemptApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_quiz_attempts_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $quiz = Quiz::factory()->create();
        QuizAttempt::factory()->count(3)->create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
        ]);

        $response = $this->getJson('/api/quiz-attempts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_quiz_attempt()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $quiz = Quiz::factory()->create();

        $data = [
            'quiz_id' => $quiz->id,
            'score' => 90,
        ];

        $response = $this->postJson('/api/quiz-attempts', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 90,
        ]);
    }
}
