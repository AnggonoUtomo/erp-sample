<?php

namespace App\Modules\DocumentManagement\Foundation\Upload\Detectors;

use App\Modules\DocumentManagement\Foundation\Upload\Contracts\FileSignatureDetector;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\DetectedFileTypeV1;
use App\Modules\DocumentManagement\Foundation\Upload\Exceptions\UploadPolicyViolation;

class BoundedMagicByteDetector implements FileSignatureDetector
{
    private const PNG_SIGNATURE = "\x89PNG\r\n\x1A\n";

    private const PNG_END = "\x00\x00\x00\x00IEND\xAE\x42\x60\x82";

    public function inspect(mixed $stream, int $maxBytes): DetectedFileTypeV1
    {
        $position = $this->assertReadableSeekableStream($stream);
        rewind($stream);

        $head = '';
        $tail = '';
        $byteSize = 0;

        try {
            while (! feof($stream)) {
                $chunk = fread($stream, 8192);
                if ($chunk === false) {
                    throw new UploadPolicyViolation('Unable to read upload stream.');
                }
                if ($chunk === '') {
                    throw new UploadPolicyViolation('Upload stream ended unexpectedly.');
                }

                $byteSize += strlen($chunk);
                if ($byteSize > $maxBytes) {
                    throw new UploadPolicyViolation('Observed file size exceeds the upload limit.');
                }

                if (strlen($head) < 8) {
                    $head = substr($head.$chunk, 0, 8);
                }
                $tail = substr($tail.$chunk, -2048);
            }
        } finally {
            fseek($stream, $position);
        }

        if ($byteSize === 0) {
            throw new UploadPolicyViolation('Empty files are not allowed.');
        }

        if (str_starts_with($head, '%PDF-')) {
            if (preg_match('/%%EOF[\x09\x0A\x0C\x0D\x20]*\z/', $tail) !== 1) {
                throw new UploadPolicyViolation('PDF terminal marker is missing or has appended content.');
            }

            return new DetectedFileTypeV1('pdf', 'application/pdf', $byteSize);
        }

        if (str_starts_with($head, "\xFF\xD8\xFF")) {
            if (! str_ends_with($tail, "\xFF\xD9")) {
                throw new UploadPolicyViolation('JPEG terminal marker is missing or has appended content.');
            }

            return new DetectedFileTypeV1('jpg', 'image/jpeg', $byteSize);
        }

        if (str_starts_with($head, self::PNG_SIGNATURE)) {
            if (! str_ends_with($tail, self::PNG_END)) {
                throw new UploadPolicyViolation('PNG terminal marker is missing or has appended content.');
            }

            return new DetectedFileTypeV1('png', 'image/png', $byteSize);
        }

        throw new UploadPolicyViolation('File signature is unsupported or ambiguous.');
    }

    private function assertReadableSeekableStream(mixed $stream): int
    {
        if (! is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new UploadPolicyViolation('Upload input must be a readable seekable stream.');
        }

        $metadata = stream_get_meta_data($stream);
        $mode = (string) ($metadata['mode'] ?? '');
        $position = ftell($stream);
        if ((! str_contains($mode, 'r') && ! str_contains($mode, '+'))
            || ($metadata['seekable'] ?? false) !== true
            || $position === false) {
            throw new UploadPolicyViolation('Upload input must be a readable seekable stream.');
        }

        return $position;
    }
}
