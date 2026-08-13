# 03 Penilaian Dampak

## Metadata

```yaml
work_item: ARC-DDD-LITE-001
status: reviewed
owner: unassigned
last_updated: 2026-08-13
```

## Dampak Utama

| Area | Dampak | Risiko | Kontrol |
|---|---|---|---|
| Namespace/autoload | Semua class yang dipindah berubah namespace | tinggi | slice kecil, search import lama, dump-autoload |
| Service provider | Binding dan registrasi route/command berubah | tinggi | provider test dan artisan bootstrap |
| HTTP | Path file route/controller berubah | tinggi bila URI/name ikut berubah | snapshot route sebelum/sesudah |
| Test | Test berpindah ownership/lokasi | sedang | pastikan phpunit discovery dan assertion tidak berkurang |
| Contract | Namespace dan owner integration berubah | tinggi | compatibility test dan deprecation window |
| Data | Tidak ada schema change dalam ARC | rendah bila disiplin scope | migration dilarang pada task struktur |
| Security/permission | Policy dan permission registration dapat terputus | tinggi | authorization regression test |
| Dokumentasi | Banyak baseline lama tidak akurat | tinggi | sync matrix dan evidence manifest |

## Dampak `HR/IntegrationContracts`

- Empat provider mempunyai behavior berbeda yang harus dipertahankan.
- `EmployeeContractSnapshotProvider` lama memiliki fallback ke kontrak terakhir, sedangkan `EmployeeContractSnapshotReader` module-owned memilih kontrak effective-dated berstatus tertentu. Keduanya tidak boleh dianggap ekuivalen tanpa test.
- Assignment snapshot menerima tanggal tetapi membaca assignment aktif dari `hr_employees`; historical replay tidak tersedia.
- Privacy guard dan payload minimum merupakan keputusan lama yang tetap dipertahankan.
- Test adalah consumer namespace lama yang pasti harus dimigrasikan.

## Dampak Historis

Dokumen lama tidak dipulihkan sebagai baseline aktif. Snapshot `BL-2026-001-pre-seos` menjaga commit sumber, daftar keputusan, dan alasan supersession sehingga riwayat tidak hilang atau ditulis ulang.

## Dampak ULID

Tidak ada dampak data dalam ARC. Semua perubahan identifier dipindahkan ke `MIG-ID-001`; work item tersebut `deferred` dan tidak memblokir struktur DDD-Lite.

## Risiko Working Tree

Deletion `MakeModuleCommand.php` telah diselesaikan melalui restore exact versi commit sumber setelah approval Pemilik proyek. Diff, syntax check, test generator, dan `module:validate` kemudian lulus; limitation verifikasi dicatat pada evidence manifest.
