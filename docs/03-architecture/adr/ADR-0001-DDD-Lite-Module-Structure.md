---
id: ADR-0001
title: Restrukturisasi Struktur Modul ke DDD-Lite Layered Structure
status: proposed
created: 2026-08-12
updated: 2026-08-12
deciders: []
related: [ARC-DDD-LITE-001]
---

# ADR-0001: Restrukturisasi Struktur Modul ke DDD-Lite Layered Structure

## Konteks

Project ini menggunakan struktur modul flat di mana semua folder (DTO, Events, Http, Models, Services, dll) berada di root setiap modul. Pendekatan ini menyulitkan penelusuran kode dan tidak mengikuti pola DDD-lite yang telah ditetapkan dalam acuan arsitektur.

Faktor yang memerlukan keputusan:
1. Struktur flat tidak memisahkan concern dengan jelas
2. Sulit membedakan antara Application, Domain, Infrastructure, dan Presentation layer
3. Module generator saat ini generate struktur flat
4. Tests dan routes berada di luar modul, menyulitkan penelusuran

## Pendorong Keputusan

1. Konsistensi dengan acuan DDD-Lite Modular Monolith
2. Kemudahan maintenance dan penelusuran kode
3. Skalabilitas untuk modul baru
4. Separation of concerns yang lebih baik

## Opsi yang Dipertimbangkan

### Opsi A: Big Bang Migration

- Deskripsi: Konversi semua modul sekaligus dalam satu PR besar
- Manfaat: Cepat selesai, tidak ada struktur hybrid
- Biaya/risiko: High risk, sulit rollback, testing kompleks

### Opsi B: Incremental Migration (Dipilih)

- Deskripsi: Konversi per modul secara bertahap dengan 10 phases
- Manfaat: Low risk per phase, mudah rollback, testing bertahap
- Biaya/risiko: Butuh waktu lebih lama, ada periode struktur hybrid

### Opsi C: Parallel Structure

- Deskripsi: Maintain struktur lama dan baru secara paralel dengan backward compatibility layer
- Manfaat: Zero downtime, backward compatible
- Biaya/risiko: Kompleksitas tinggi, technical debt

## Keputusan

**Opsi B: Incremental Migration** dipilih karena:
1. Risiko per phase dapat dikelola
2. Rollback per phase memungkinkan
3. Testing bisa dilakukan bertahap
4. Tim bisa belajar dari setiap phase

## Konsekuensi

### Positif

1. Struktur lebih jelas dan mudah ditelusuri
2. Konsisten dengan acuan DDD-Lite
3. Module generator menghasilkan struktur yang benar
4. Tests dan routes dalam modul memudahkan maintenance

### Negatif

1. Butuh 10 phases untuk selesai
2. Periode struktur hybrid selama migrasi
3. Namespace changes di banyak file
4. Perlu update dokumentasi

### Netral / Tindak Lanjut

1. Update MODULE-CATALOG.md setelah semua modul dikonversi
2. Update DEPENDENCY-RULES.md dengan aturan baru
3. Update EVENT-CATALOG.md dengan lokasi event baru
4. Baseline snapshot di docs/11-baselines/

## Validasi

Keputusan akan diuji dengan:
1. Module generator test - generate modul baru dengan struktur DDD-Lite
2. WorkLocations sebagai proof of concept (Phase 3)
3. Full test suite harus pass setelah setiap phase
4. Aplikasi harus bisa diakses di browser

## Penggantian

ADR ini menggantikan struktur flat yang ada saat ini. Kondisi yang akan memicu review ulang:
1. Ditemukan pola yang lebih baik untuk DDD-lite di Laravel
2. Kebutuhan untuk microservices di masa depan
3. Perubahan fundamental dalam arsitektur Laravel

</contents>