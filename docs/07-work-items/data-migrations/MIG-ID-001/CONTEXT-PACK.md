# Context Pack — MIG-ID-001

## Task Aktif

Tidak ada; work item deferred.

## Fakta

- schema kode memakai bigint auto-increment;
- beberapa storage object key menggunakan ULID, tetapi bukan primary key relasional;
- tidak ada alasan bisnis, mapping, volume, rehearsal, rollback, atau approval migrasi;
- work item ini tidak termasuk scope ARC-DDD-LITE-001.

## Area yang Dilarang

Seluruh model, migration, generator, factory, seeder, test, DTO, event, route binding, dan data production sampai work item dibuka kembali dan approved.
