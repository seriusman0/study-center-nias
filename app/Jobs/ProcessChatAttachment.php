<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessChatAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public int $messageId) {}

    public function handle(): void
    {
        $message = Message::find($this->messageId);

        if (! $message || ! $message->attachment_path) {
            return;
        }

        try {
            $sourcePath  = Storage::disk('public')->path($message->attachment_path);

            if (! file_exists($sourcePath)) {
                return;
            }

            $thumbName   = Str::uuid() . '_thumb.jpg';
            $thumbFolder = 'chat/thumbs';
            $thumbPath   = $thumbFolder . '/' . $thumbName;
            $thumbFsPath = Storage::disk('public')->path($thumbPath);

            // Pastikan direktori ada
            if (! file_exists(dirname($thumbFsPath))) {
                mkdir(dirname($thumbFsPath), 0755, true);
            }

            // Baca gambar dan resize (menggunakan GD driver — built-in PHP)
            $image = imagecreatefromstring(file_get_contents($sourcePath));

            if (! $image) {
                return;
            }

            $origW = imagesx($image);
            $origH = imagesy($image);

            $maxW = 400;
            if ($origW > $maxW) {
                $ratio  = $maxW / $origW;
                $newW   = $maxW;
                $newH   = (int) ($origH * $ratio);
            } else {
                $newW = $origW;
                $newH = $origH;
            }

            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

            imagejpeg($resized, $thumbFsPath, 80);

            imagedestroy($image);
            imagedestroy($resized);

            $message->update(['thumbnail_path' => $thumbPath]);

        } catch (\Throwable $e) {
            // Gagal generate thumbnail — biarkan, foto tetap tampil via attachment_url
            \Log::warning('ProcessChatAttachment failed: ' . $e->getMessage(), [
                'message_id' => $this->messageId,
            ]);
        }
    }
}
