<?php

namespace Tests\Feature;

use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use App\Modules\DocumentManagement\Foundation\Upload\Exceptions\UploadPolicyViolation;
use App\Modules\DocumentManagement\Foundation\Upload\Policies\DocumentUploadPolicy;
use Tests\TestCase;

class DocumentUploadPolicyTest extends TestCase
{
    public function test_pdf_jpeg_and_png_matching_policy_are_accepted_as_not_configured_scan(): void
    {
        foreach ([
            ['report.PDF', 'application/pdf', "%PDF-1.7\nbody\n%%EOF\n", 'pdf'],
            ['photo.jpeg', 'image/jpeg', "\xFF\xD8\xFF\xE0jpeg-data\xFF\xD9", 'jpeg'],
            ['image.PNG', 'image/png', "\x89PNG\r\n\x1A\npng-data\x00\x00\x00\x00IEND\xAE\x42\x60\x82", 'png'],
        ] as [$filename, $mime, $contents, $extension]) {
            $stream = $this->stream($contents);
            $result = app(DocumentUploadPolicy::class)->validate(
                new UploadIntentV1($filename, $mime, strlen($contents), 'upload-intent-1'),
                $stream,
            );

            $this->assertSame([
                'schemaVersion' => 1,
                'filename' => preg_replace('/\.[^.]+$/', ".{$extension}", $filename),
                'extension' => $extension,
                'declaredMediaType' => $mime,
                'detectedMediaType' => $mime,
                'byteSize' => strlen($contents),
                'scanStatus' => 'NOT_CONFIGURED',
            ], $result->toArray());
            $this->assertSame(0, ftell($stream));
            $this->assertEmpty(array_intersect(
                ['path', 'url', 'disk', 'objectKey', 'storageKey'],
                array_keys($result->toArray()),
            ));
            fclose($stream);
        }
    }

    public function test_extension_declared_mime_and_detected_signature_must_match(): void
    {
        foreach ([
            ['report.jpg', 'application/pdf', "%PDF-1.7\nbody\n%%EOF\n"],
            ['report.pdf', 'image/png', "\x89PNG\r\n\x1A\npng\x00\x00\x00\x00IEND\xAE\x42\x60\x82"],
            ['report.pdf', 'application/pdf', "\x89PNG\r\n\x1A\npng\x00\x00\x00\x00IEND\xAE\x42\x60\x82"],
        ] as [$filename, $mime, $contents]) {
            $intent = new UploadIntentV1($filename, $mime, strlen($contents), 'upload-intent-1');
            $stream = $this->stream($contents);

            try {
                app(DocumentUploadPolicy::class)->validate($intent, $stream);
                $this->fail('Mismatched upload type was accepted.');
            } catch (UploadPolicyViolation) {
                $this->addToAssertionCount(1);
            } finally {
                fclose($stream);
            }
        }
    }

    public function test_declared_and_observed_size_are_bounded_and_must_match(): void
    {
        $this->assertSame(20 * 1024 * 1024, config('document-management.upload.max_bytes'));

        config(['document-management.upload.max_bytes' => 16]);

        foreach ([
            [new UploadIntentV1('report.pdf', 'application/pdf', 17, 'upload-intent-1'), "%PDF-1.7\n%%EOF"],
            [new UploadIntentV1('report.pdf', 'application/pdf', 16, 'upload-intent-1'), "%PDF-1.7\ncontent-too-large\n%%EOF"],
            [new UploadIntentV1('report.pdf', 'application/pdf', 15, 'upload-intent-1'), "%PDF-1.7\n%%EOF"],
        ] as [$intent, $contents]) {
            $stream = $this->stream($contents);
            try {
                app(DocumentUploadPolicy::class)->validate($intent, $stream);
                $this->fail('Invalid upload size was accepted.');
            } catch (UploadPolicyViolation) {
                $this->addToAssertionCount(1);
            } finally {
                fclose($stream);
            }
        }
    }

    public function test_unsafe_filename_unsupported_type_and_empty_stream_are_rejected(): void
    {
        foreach ([
            [new UploadIntentV1('../report.pdf', 'application/pdf', 15, 'upload-intent-1'), "%PDF-1.7\n%%EOF"],
            [new UploadIntentV1('report.php.pdf', 'application/pdf', 15, 'upload-intent-1'), "%PDF-1.7\n%%EOF"],
            [new UploadIntentV1('report.php.final.pdf', 'application/pdf', 15, 'upload-intent-1'), "%PDF-1.7\n%%EOF"],
            [new UploadIntentV1('report.pdf.jpg', 'image/jpeg', 15, 'upload-intent-1'), "\xFF\xD8\xFFjpeg-data\xFF\xD9"],
            [new UploadIntentV1('report.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 15, 'upload-intent-1'), 'unsupported-file'],
            [new UploadIntentV1('report.pdf', 'application/pdf', 0, 'upload-intent-1'), ''],
            [new UploadIntentV1('report.pdf', 'application/pdf', 15, ''), "%PDF-1.7\n%%EOF"],
        ] as [$intent, $contents]) {
            $stream = $this->stream($contents);
            try {
                app(DocumentUploadPolicy::class)->validate($intent, $stream);
                $this->fail('Unsafe upload was accepted.');
            } catch (UploadPolicyViolation) {
                $this->addToAssertionCount(1);
            } finally {
                fclose($stream);
            }
        }
    }

    public function test_appended_or_ambiguous_polyglot_content_is_rejected(): void
    {
        foreach ([
            ['polyglot.pdf', 'application/pdf', "%PDF-1.7\n%%EOF\n<script>payload</script>"],
            ['polyglot.jpg', 'image/jpeg', "\xFF\xD8\xFF\xE0jpeg\xFF\xD9trailing"],
            ['polyglot.png', 'image/png', "\x89PNG\r\n\x1A\npng\x00\x00\x00\x00IEND\xAE\x42\x60\x82trailing"],
        ] as [$filename, $mime, $contents]) {
            $stream = $this->stream($contents);
            try {
                app(DocumentUploadPolicy::class)->validate(
                    new UploadIntentV1($filename, $mime, strlen($contents), 'upload-intent-1'),
                    $stream,
                );
                $this->fail('Appended/polyglot content was accepted.');
            } catch (UploadPolicyViolation) {
                $this->addToAssertionCount(1);
            } finally {
                fclose($stream);
            }
        }
    }

    /** @return resource */
    private function stream(string $contents)
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }
}
