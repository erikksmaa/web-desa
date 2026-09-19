<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class FileUploads
{
    public static function documentRules(int $kilobytes): array
    {
        return ['file', 'extensions:pdf,doc,docx,xls,xlsx', 'mimes:pdf,doc,docx,xls,xlsx', 'max:'.$kilobytes];
    }

    public static function imageRules(): array
    {
        return ['image', 'extensions:jpg,jpeg,png,webp', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
    }

    public static function store(UploadedFile $file, string $disk, string $directory, string $field): string
    {
        $path = $file->store($directory, $disk);
        if (! $path) {
            throw ValidationException::withMessages([$field => 'File tidak dapat disimpan. Silakan coba lagi.']);
        }

        return $path;
    }

    public static function remove(string $disk, array $paths): void
    {
        $paths = array_values(array_unique(array_filter($paths)));
        if ($paths && ! Storage::disk($disk)->delete($paths)) {
            report(new RuntimeException('File cleanup failed on disk '.$disk));
        }
    }

    public static function originalName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F"%]/u', '', $name) ?: 'unduhan';

        return mb_strcut($name, 0, 240, 'UTF-8');
    }
}
