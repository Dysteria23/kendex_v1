<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/** Guarda comprobantes: valida MIME real, sanea el nombre y comprime imágenes (RNF-05, checklist 11). */
class ReceiptStorage
{
    protected const MIMES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    public function disk(): string
    {
        return config('kendex.receipts.disk');
    }

    /** @param  UploadedFile[]  $files */
    public function storeMany(array $files): array
    {
        return array_map(fn ($file) => $this->store($file), array_values($files));
    }

    public function store(UploadedFile $file): array
    {
        $mime = $file->getMimeType(); // detectado por contenido, no por la extensión enviada
        $ext = self::MIMES[$mime] ?? null;

        if (! $ext) {
            throw ValidationException::withMessages(['uploads' => 'Solo se permiten comprobantes PDF, JPG o PNG.']);
        }

        if ($file->getSize() > config('kendex.receipts.max_kb') * 1024) {
            throw ValidationException::withMessages(['uploads' => 'Cada comprobante puede pesar máximo 10 MB.']);
        }

        $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safe = Str::limit(Str::slug($original) ?: 'comprobante', 40, '');
        $name = now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '-' . $safe . '.' . $ext;
        $path = 'receipts/' . now()->format('Y/m') . '/' . $name;

        if ($ext === 'pdf') {
            Storage::disk($this->disk())->putFileAs(dirname($path), $file, $name);
        } else {
            $max = config('kendex.receipts.image_max_px');
            $image = (new ImageManager(new Driver()))->read($file->getRealPath())->scaleDown($max, $max);
            $encoded = $ext === 'png' ? $image->toPng() : $image->toJpeg(config('kendex.receipts.image_quality'));
            Storage::disk($this->disk())->put($path, (string) $encoded);
        }

        return [
            'path' => $path,
            'name' => $safe . '.' . $ext,
            'mime' => $mime,
            'size' => Storage::disk($this->disk())->size($path),
        ];
    }

    public function delete(array $items): void
    {
        foreach ($items as $item) {
            Storage::disk($this->disk())->delete($item['path']);
        }
    }
}
