# Development workflow

## lifecycle

```text
DISCOVER → DEFINE → DESIGN → PLAN → BUILD → VERIFY → REVIEW → RELEASE → OPERATE
```

## fase gate

### temukan

Output: repository inventaris, stakeholders, masalah pernyataan, batasan, asumsi.

keluar gate: masalah dan konteks adalah dipahami.

### definisikan

Output: PRD, scope, requirement, aturan bisnis, kriteria penerimaan.

keluar gate: requirement adalah disetujui dan dapat diuji.

### Design

Output: sistem design, module boundaries, data/API design, ADRs, keamanan implikasi.

keluar gate: implementasi pendekatan adalah koheren dan risiko adalah diketahui.

### rencana

Output: implementasi slice, dependensi, tasks, pengujian rencana, rollback rencana.

keluar gate: definisi of Ready adalah terpenuhi.

### Build

Output: kode dan pengujian untuk satu task/slice.

keluar gate: lokal task kriteria penerimaan lulus.

### Verify

Output: otomatis pemeriksaan, manual verifikasi, bukti, regresi hasil.

keluar gate: tidak ada belum diselesaikan kritis kegagalan.

### review

Output: lima-aspek review, keamanan/performa review di mana relevant, diselesaikan temuan.

keluar gate: gabungkan/rilis rekomendasi ada.

### rilis

Output: rilis catatan, deployment dan rollback bukti.

keluar gate: rilis adalah di-deploy atau secara eksplisit ditunda.

### Operate

Output: monitoring, insiden, runbook memperbarui, teknis utang teknis dan tindak lanjut tasks.

## Incremental aturan

setiap implementasi slice wajib leave repository di valid, dapat diuji status dan sebaiknya independently reviewable dan revertible.

## Emergent pekerjaan dan arsitektur evolusi

Development boleh mengungkap pekerjaan itu adalah tidak secara wajar diketahui selama bootstrap. Ini adalah diizinkan, tetapi itu dilarang menjadi tersembunyi di dalam aktif implementasi task.

ketika baru boundary, submodule family, kontrak, integrasi, data pemilik, atau arsitektural tanggung jawab adalah ditemukan:

1. Pause terdampak implementasi slice at safe status.
2. buat Arsitektur Penemuan catat.
3. Decide whether saat ini task dapat continue, wajib blocked, atau sebaiknya pecah.
4. jika arsitektur perubahan, buat lengkap Arsitektur perubahan paket.
5. Approve proposal, dampak penilaian, dan ADR.
6. perbarui baseline catalogs dan traceability.
7. buat baru siap tasks dan lanjutkan incremental implementasi.
8. selesai validasi dan penyelesaian laporan setelah coding.

 penemuan itu sendiri adalah tidak persetujuan ke implement.
