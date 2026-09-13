<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;

test('temporary uploads use the application endpoint when permanent storage is R2', function () {
    config(['filesystems.default' => 'r2']);

    expect(FileUploadConfiguration::isUsingS3())->toBeFalse();

    $url = app(GenerateSignedUploadUrl::class)->forLocal();

    expect(parse_url($url, PHP_URL_HOST))->toBe(parse_url(url('/'), PHP_URL_HOST));
    Storage::fake('tmp-for-tests');
    $this->post($url, ['files' => [UploadedFile::fake()->image('avatar.jpg')]])
        ->assertOk()
        ->assertJsonCount(1, 'paths');

    expect(collect(Storage::disk('tmp-for-tests')->allFiles('livewire-tmp'))
        ->filter(fn (string $path): bool => str_ends_with($path, '.jpg')))->toHaveCount(1);
});
