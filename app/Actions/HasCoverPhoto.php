<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasCoverPhoto
{
    /**
     * Update the article's cover photo.
     *
     * @param  \Illuminate\Http\UploadedFile  $photo
     * @return void
     */
    public function updateCoverPhoto(UploadedFile $photo): void
    {
        tap($this->cover_path, function ($previous) use ($photo) {
            $this->forceFill([
                'cover_path' => $photo->storePublicly(
                    'covers', ['disk' => $this->coverPhotoDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->coverPhotoDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete the article's cover photo.
     *
     * @return void
     */
    public function deleteCoverPhoto(): void
    {
        if (is_null($this->cover_path)) {
            return;
        }

        Storage::disk($this->coverPhotoDisk())->delete($this->cover_path);

        $this->forceFill([
            'cover_path' => null,
        ])->save();
    }

    /**
     * Get the URL to the user's cover photo.
     *
     * @return string
     */
    public function getCoverUrlAttribute(): string
    {
        return $this->cover_path
                    ? Storage::disk($this->coverPhotoDisk())->url($this->cover_path)
                    : $this->defaultCoverUrl();
    }

    /**
     * Get the default cover photo URL if no cover photo has been uploaded.
     *
     * @return string
     */
    protected function defaultCoverUrl(): string
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the disk that cover photos should be stored on.
     *
     * @return string
     */
    protected function coverPhotoDisk(): string
    {
        return 'public';
    }
}
