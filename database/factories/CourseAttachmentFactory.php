<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseAttachment>
 */
class CourseAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file' => $this->faker->randomElement(['attachments/file1.pdf', 'attachments/file2.zip', 'attachments/file3.docx']),
            'course_content_id' => \App\Models\CourseContent::inRandomOrder()->first()->id,
        ];
    }
}
