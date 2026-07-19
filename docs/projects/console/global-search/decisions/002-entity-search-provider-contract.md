# ADR-002: Entity search provider contract

## Status

Accepted

## Date

2026-07-19

## Context

Checkpoint B sudah membuat command palette aman untuk navigasi menu. Setelah itu ada kebutuhan menyiapkan jalan masa depan agar command palette bisa mencari entity seperti `Users` atau `Employees`.

Risiko entity search lebih tinggi daripada navigation search karena provider dapat membaca data domain yang berisi PII, operational identifiers, audit data, document metadata, atau konfigurasi sensitif. Jika kontrak tidak dibatasi dari awal, setiap module bisa mengirim result dengan bentuk berbeda dan berpotensi membocorkan field seperti token, password, API key, private storage path, signed URL, raw audit payload, atau isi dokumen.

## Decision

Entity search masa depan wajib lewat **provider contract read-only** yang permission-aware, rate-limited, dan privacy-safe.

Task 05 hanya mendokumentasikan kontrak. Tidak ada backend route, service, provider aktif, query database entity, migration, atau perubahan runtime.

Kontrak provider masa depan harus mengikuti batas berikut:

- provider hanya boleh mengembalikan result user-facing yang sudah di-allowlist;
- provider wajib menerima context user/request dan query yang sudah divalidasi;
- provider wajib melakukan permission check sendiri;
- provider wajib membatasi jumlah result;
- provider wajib menolak atau menyaring field sensitif sebelum result keluar;
- provider tidak boleh melakukan mutation, audit payload expansion, document content read, atau storage path exposure.

## Contract shape

Draft kontrak PHP masa depan:

```php
interface EntitySearchProvider
{
    public function key(): string;

    public function label(): string;

    public function canSearch(SearchContext $context): bool;

    /**
     * @return list<SearchResult>
     */
    public function search(SearchQuery $query, SearchContext $context): array;
}
```

Draft DTO result:

```php
final readonly class SearchResult
{
    public function __construct(
        public string $id,
        public string $provider,
        public string $type,
        public string $title,
        public string $group,
        public string $url,
        public ?string $description = null,
        public array $badges = [],
        public array $meta = [],
    ) {}
}
```

Draft query/context:

```php
final readonly class SearchQuery
{
    public function __construct(
        public string $term,
        public int $limit = 10,
    ) {}
}

final readonly class SearchContext
{
    public function __construct(
        public int $userId,
        public array $permissions,
        public string $guard = 'web',
    ) {}
}
```

## Forbidden fields

Provider dan aggregator wajib menolak result yang memuat key atau value berikut:

- `password`
- `password_hash`
- `remember_token`
- `token`
- `secret`
- `api_key`
- `private_key`
- `signed_url`
- `storage_path`
- `backup_path`
- `backup_content`
- `document_content`
- `document_number_raw`
- `audit_old_values`
- `audit_new_values`
- `email_body`
- `mail_payload`

Allowlist output lebih diutamakan daripada denylist. Denylist tetap dipakai sebagai guard tambahan saat aggregator menerima result dari provider.

## Permission, auth, and rate-limit boundary

Jika endpoint entity search nanti dibuat:

- route wajib `auth`;
- route wajib rate limit kecil, misalnya `30 request / menit / user`;
- query wajib divalidasi:
    - term minimal 2 karakter setelah trim;
    - term maksimal 80 karakter;
    - limit maksimal 10 per provider dan maksimal 20 total;
- provider wajib permission-aware;
- response untuk provider tanpa permission adalah result kosong, bukan error yang membocorkan nama provider;
- backend permission/policy tetap menjadi security boundary final di route tujuan result.

## Error semantics

Endpoint masa depan wajib memakai error shape konsisten:

```json
{
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Query search tidak valid."
    }
}
```

Status code:

- `401` untuk unauthenticated;
- `403` untuk authenticated tetapi tidak boleh memakai global search;
- `422` untuk query invalid;
- `429` untuk rate limit;
- `500` untuk error internal tanpa detail sensitif.

Provider failure tidak boleh menjatuhkan seluruh search bila provider lain masih aman dipakai; failure dicatat secara server-side tanpa memuat query sensitif berlebihan.

## Alternatives considered

### Endpoint search generik tanpa provider contract

Pros:

- cepat dibuat;
- satu controller bisa langsung query beberapa table.

Cons:

- sulit diuji per module;
- raw query rawan melebar;
- permission dan privacy guard mudah tidak konsisten.

Rejected.

### Frontend-only entity search dari shared props

Pros:

- cepat dan tidak perlu endpoint.

Cons:

- memperbesar shared props;
- berisiko membawa PII ke semua halaman;
- tidak cocok untuk data besar.

Rejected.

### Search engine/index external

Pros:

- kuat untuk full-text dan ranking besar.

Cons:

- terlalu berat untuk MVP;
- menambah dependency dan sinkronisasi index;
- privacy boundary lebih kompleks.

Deferred.

## Consequences

- Entity search dapat dikembangkan incremental tanpa mengganggu navigation search.
- Setiap module harus eksplisit menentukan field yang boleh tampil.
- Search tetap read-only dan permission-aware.
- Implementasi Task 06 harus kembali meminta approval karena akan membuka backend endpoint/query entity pertama.

## Follow-up

- Task 06 boleh memilih satu provider kecil: `Users` atau `Employees`.
- Sebelum Task 06, tentukan field allowlist per provider.
- Tambahkan backend tests untuk auth, permission denial, forbidden-field guard, rate limit, dan result limit.
