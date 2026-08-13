# Context Pack — DEP-HR-001

## Task Aktif

Tidak ada. Work item masih proposed/not-ready.

## Work Item Induk

`ARC-DDD-LITE-001`

## Fakta Repository

- Shell memiliki 23 file PHP, tanpa table/route/navigation.
- Consumer import di luar modul hanya ditemukan pada test.
- Module-owned integration surface sudah ada pada beberapa HR modules.
- Keputusan historis tentang versioning dan privacy tetap valid.
- Runtime verification terblokir oleh missing `MakeModuleCommand`.

## Area yang Diizinkan Sekarang

- dokumentasi `DEP-HR-001`, ADR-0002, dan katalog terkait;
- read-only inspection.

## Area yang Dilarang Sekarang

- seluruh file pada `app/Modules/HR/IntegrationContracts`;
- provider/contract modul target;
- test, config, route, migration, dan dependency.

## Risiko

- menganggap dua contract dengan nama mirip memiliki semantics sama;
- mengurangi privacy assertion;
- menghapus command sebelum operator impact dipahami;
- menyatakan zero consumer tanpa runtime evidence.
