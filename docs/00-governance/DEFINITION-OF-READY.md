# definisi of Ready

 feature/task adalah siap hanya ketika semua berlaku item adalah checked.

## fitur readiness

- [ ] masalah dan desired hasil adalah eksplisit.
- [ ] Ruang Lingkup dan non-scope adalah didefinisikan.
- [ ] Stakeholders dan pengguna adalah identified.
- [ ] Fungsional dan non-fungsional requirement have ID.
- [ ] bisnis aturan dan edge case adalah terdokumentasi.
- [ ] Kriteria penerimaan adalah dapat diuji.
- [ ] dependensi dan module impacts adalah diketahui.
- [ ] data, API, keamanan, privasi, dan authorization impacts adalah reviewed.
- [ ] Arsitektur keputusan adalah disetujui atau tidak wajib.
- [ ] Risiko, asumsi, dan pertanyaan terbuka adalah diselesaikan enough ke proceed.
- [ ] rollout dan rollback expectations adalah didefinisikan.

## Task readiness

- [ ] Task has satu terbatas tujuan.
- [ ] Referensi point ke disetujui specifications.
- [ ] diizinkan dan dilarang perubahan areas adalah identified.
- [ ] diharapkan file/components adalah listed ketika diketahui.
- [ ] Kriteria penerimaan dan verifikasi perintah adalah present.
- [ ] wajib pengujian adalah ditunjuk.
- [ ] dependensi adalah selesai atau secara eksplisit tersedia.
- [ ] Task dapat menjadi selesai tanpa inventing missing requirement.

## Putusan

```yaml
readiness: READY | NOT_READY
approved_by: <human>
date: YYYY-MM-DD
notes: <text>
```
