<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class AuthBackgroundImageService
{
    /**
     * Path, relative to the public directory, holding the auth background images.
     */
    protected const DIRECTORY = 'image/background';

    /**
     * Image extensions considered usable as a background.
     *
     * @var array<int, string>
     */
    protected const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Get the URL of a random background image, or null when none are available.
     */
    public function randomUrl(): ?string
    {
        $images = $this->images();

        if ($images === []) {
            return null;
        }

        return asset($images[array_rand($images)]);
    }

    /**
     * Get every background image path relative to the public directory.
     *
     * @return array<int, string>
     */
    public function images(): array
    {
        $directory = public_path(static::DIRECTORY);

        if (! File::isDirectory($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(fn (SplFileInfo $file): bool => in_array(strtolower($file->getExtension()), static::EXTENSIONS, true))
            ->map(fn (SplFileInfo $file): string => static::DIRECTORY.'/'.$file->getFilename())
            ->values()
            ->all();
    }
}
