<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use Illuminate\Support\Facades\File;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        // Prefer a specific storage thumbnail file if present
        $preferredFilename = '01K6CA6M7PY3G08KXSDBSDVGYD.png';
        $storageRelative = 'thumbnails/' . $preferredFilename; // relative to storage/app/public
        $storageFullPath = storage_path('app/public/' . $storageRelative);

        $default = 'https://via.placeholder.com/640x480.png';
        $thumbnailUrl = $default;

        if (File::exists($storageFullPath)) {
            // ensure public/storage symlink exists (php artisan storage:link)
            $thumbnailUrl = url('storage/' . $storageRelative);
        } else {
            // fallback: try to pick a random local image from public/thumbnail
            $relativePath = null;
            $dir = public_path('thumbnail');

            if (File::isDirectory($dir)) {
                $files = collect(File::files($dir))->map(fn($f) => 'thumbnail/' . $f->getFilename())->all();
                if (!empty($files)) {
                    // build absolute URL using app url helper
                    $relativePath = $this->faker->randomElement($files);
                }
            }

            $thumbnailUrl = $relativePath ? url($relativePath) : $default;
        }

        return [
            'name' => $this->faker->sentence(3),
            'slug' => $this->faker->unique()->slug(),
            'thumbnail' => $thumbnailUrl,
            'about' => $this->faker->paragraph(),
            'is_popular' => $this->faker->boolean(),
            'category_id' => Category::inRandomOrder()->first()->id ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
