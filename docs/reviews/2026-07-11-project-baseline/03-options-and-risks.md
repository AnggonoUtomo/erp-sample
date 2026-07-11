# 03 — Idea Refine: Opsi, Risiko, dan Batasan

## Problem statement

Bagaimana menjadikan repository ini baseline ERP modular yang dapat dipercaya programmer baru tanpa melakukan rewrite besar atau membekukan delivery fitur?

## Opsi

### A. Dokumentasi-first saja

Cepat dan murah, tetapi drift tetap terjadi karena aturan tidak dieksekusi mesin. Ditolak sebagai bentuk final.

### B. Rewrite “clean architecture” dari nol

Memberi konsistensi teoritis, tetapi membuang perilaku yang sudah teruji, memperbesar risiko regresi, dan tidak proporsional. Ditolak.

### C. Baseline bertahap, risk-first dan contract-driven — rekomendasi

Stabilkan test; formalkan contract modul; buat quality gate non-mutating; harden boundary berisiko; baru dekomposisi file besar. Ini mempertahankan nilai implementasi saat ini sambil menjadikan setiap koreksi kecil dan terverifikasi.

## Asumsi yang harus divalidasi

- Modul HR adalah pola bisnis canonical, sedangkan Console adalah pola operasional canonical.
- CI kelak boleh menjalankan test paralel.
- Full restore memang requirement produk, bukan tool lokal sementara.
- `navigation.php` boleh opsional jika manifest menyatakan navigation tidak diekspor.

## Risiko utama

- Perbaikan formatting massal menutupi perubahan behavior: pisahkan commit/PR.
- Validator contract terlalu kaku menghambat modul khusus: dukung explicit opt-out di manifest.
- Refactor service sebelum characterization test dapat mengubah restore/settings secara diam-diam.
- Dokumentasi menjadi stale: semua aturan penting harus memiliki check otomatis.

## Bentuk final

Baseline final adalah: contract modul tervalidasi, quality gates hijau dan non-mutating, test parallel-safe, boundary restore mempunyai threat model dan rollback, page/service mempunyai batas tanggung jawab, serta docs menyediakan urutan baca dan link ke task/ADR.

## Non-goals ideasi

Tidak mengganti Laravel/Inertia/React, tidak memperkenalkan microservices, tidak membuat package modular baru, dan tidak mengubah domain HR pada audit ini.

## Keputusan dokumentasi tahap ini

Arah C dipilih sebagai proposed direction. Lihat [ADR-003](decisions/003-risk-first-remediation.md).

