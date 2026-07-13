<?php

namespace App\Modules\DocumentManagement\Foundation\Upload\Services;

use RuntimeException;

class UploadStreamHasher
{
    public function sha256(mixed $stream, int $expectedByteSize): string
    {
        $position = ftell($stream);
        if ($position === false || ! rewind($stream)) {
            throw new RuntimeException('Unable to rewind upload stream for checksum.');
        }

        $context = hash_init('sha256');
        $observedByteSize = 0;
        try {
            while (! feof($stream)) {
                $chunk = fread($stream, 8192);
                if ($chunk === false || ($chunk === '' && ! feof($stream))) {
                    throw new RuntimeException('Unable to checksum upload stream.');
                }
                if ($chunk === '') {
                    break;
                }

                $observedByteSize += strlen($chunk);
                if ($observedByteSize > $expectedByteSize) {
                    throw new RuntimeException('Upload stream changed after validation.');
                }
                hash_update($context, $chunk);
            }
        } finally {
            fseek($stream, $position);
        }

        if ($observedByteSize !== $expectedByteSize) {
            throw new RuntimeException('Upload stream changed after validation.');
        }

        return hash_final($context);
    }
}
