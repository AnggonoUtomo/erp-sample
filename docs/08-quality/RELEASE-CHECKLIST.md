# rilis Checklist

## Ruang Lingkup

- [ ] rilis ID/version assigned.
- [ ] disertakan tasks/features adalah selesai dan traceable.
- [ ] tidak ada unapproved scope adalah disertakan.

## kualitas

- [ ] otomatis pemeriksaan lulus.
- [ ] review temuan adalah diselesaikan.
- [ ] keamanan pemeriksaan selesai.
- [ ] performa pemeriksaan selesai ketika berlaku.

## data dan kompatibilitas

- [ ] Migrations reviewed dan diuji.
- [ ] Backup/restore considerations diverifikasi.
- [ ] API/event kompatibilitas diverifikasi.

## operasional

- [ ] deployment langkah diverifikasi.
- [ ] rollback langkah diverifikasi.
- [ ] Monitoring/alerts siap.
- [ ] dukung/insiden pemilik identified.

## Komunikasi

- [ ] rilis catatan selesai.
- [ ] diketahui issues terdokumentasi.
- [ ] Stakeholders informed ketika wajib.

## Keputusan

```yaml
release_verdict: GO | NO_GO | CONDITIONAL_GO
approved_by:
date:
conditions: []
```
