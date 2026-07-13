<?php

namespace App\Modules\DocumentManagement\Foundation\Upload\Policies;

use App\Modules\DocumentManagement\Foundation\Upload\Contracts\FileSignatureDetector;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\ValidatedUploadV1;
use App\Modules\DocumentManagement\Foundation\Upload\Exceptions\UploadPolicyViolation;

class DocumentUploadPolicy
{
    private const DANGEROUS_INNER_EXTENSIONS = [
        'bat', 'cmd', 'com', 'dll', 'exe', 'html', 'htm', 'jar', 'js', 'msi',
        'pdf', 'jpg', 'jpeg', 'png', 'phar', 'php', 'php3', 'php4', 'php5', 'phtml', 'sh', 'svg',
    ];

    public function __construct(private FileSignatureDetector $detector) {}

    public function validate(UploadIntentV1 $intent, mixed $stream): ValidatedUploadV1
    {
        $maxBytes = (int) config('document-management.upload.max_bytes');
        $allowedTypes = (array) config('document-management.upload.allowed_types');
        $scanStatus = (string) config('document-management.upload.scan_status');

        [$filename, $extension] = $this->validateFilename($intent->filename);
        $declaredMediaType = strtolower(trim($intent->declaredMediaType));

        if (trim($intent->idempotencyKey) === '' || mb_strlen($intent->idempotencyKey) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $intent->idempotencyKey) === 1) {
            throw new UploadPolicyViolation('Idempotency key must contain 1 through 255 safe characters.');
        }

        if ($maxBytes < 1 || $intent->declaredByteSize < 1 || $intent->declaredByteSize > $maxBytes) {
            throw new UploadPolicyViolation('Declared file size is outside the upload limit.');
        }

        if (! isset($allowedTypes[$extension]) || $allowedTypes[$extension] !== $declaredMediaType) {
            throw new UploadPolicyViolation('Filename extension and declared media type are not allowed together.');
        }

        $detected = $this->detector->inspect($stream, $maxBytes);
        if ($detected->byteSize !== $intent->declaredByteSize) {
            throw new UploadPolicyViolation('Declared and observed file sizes do not match.');
        }
        if ($detected->mediaType !== $declaredMediaType) {
            throw new UploadPolicyViolation('Declared media type does not match the detected file signature.');
        }
        if ($scanStatus !== 'NOT_CONFIGURED') {
            throw new UploadPolicyViolation('MVP scan status configuration is invalid.');
        }

        return new ValidatedUploadV1(
            $filename,
            $extension,
            $declaredMediaType,
            $detected->mediaType,
            $detected->byteSize,
            $scanStatus,
        );
    }

    /** @return array{string, string} */
    private function validateFilename(string $filename): array
    {
        $filename = trim($filename);
        if ($filename === '' || mb_strlen($filename) > 255
            || preg_match('#[\x00-\x1F\x7F/\\\\]#', $filename) === 1) {
            throw new UploadPolicyViolation('Filename is empty, unbounded, or path-like.');
        }

        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        $stem = (string) pathinfo($filename, PATHINFO_FILENAME);
        if ($stem === '' || $extension === '') {
            throw new UploadPolicyViolation('Filename must have a safe basename and extension.');
        }

        $innerExtensions = array_slice(explode('.', strtolower($filename)), 1, -1);
        if (array_intersect($innerExtensions, self::DANGEROUS_INNER_EXTENSIONS) !== []) {
            throw new UploadPolicyViolation('Suspicious double extension is not allowed.');
        }

        return [substr($filename, 0, -strlen($extension)).$extension, $extension];
    }
}
