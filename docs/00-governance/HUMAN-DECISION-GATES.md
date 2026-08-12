# manusia Keputusan gate

AI boleh investigasi, draft, bandingkan opsi, dan siapkan bukti. AI dilarang menyetujui sendiri berikut:

- baru atau dihapus arsitektural boundary/module
- breaking API atau kontrak perubahan
- Authentication, authorization, identitas, secret, atau kriptografi perubahan
- destruktif atau irreversible data migrasi
- data ownership atau retensi perubahan
- baru produksi dependensi, eksternal vendor, berbayar layanan, atau material biaya kenaikan
- kepatuhan, legal, privasi, atau audit kontrol perubahan
- produksi rollout dengan signifikan gangguan layanan atau rollback risiko
- Relaxation dari kualitas, keamanan, atau kriteria penerimaan

## gate catat

catat:

```yaml
gate: architecture-approval
decision: approved | rejected | conditional
approver: <human name or role>
date: YYYY-MM-DD
conditions: []
evidence: []
```

Absence dari persetujuan means `not approved`.
