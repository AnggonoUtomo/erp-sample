# ADR-003: Remediasi Risk-first Bertahap

## Status
Accepted; implemented through CP-3, CP-4 pending verification

## Context
Ada test nondeterministic, contract drift, dan destructive restore boundary. Rewrite penuh berisiko tinggi.

## Decision
Urutan: stabilkan test → enforce contract/gates → harden restore/authorization → dekomposisi maintainability.

## Alternatives
Rewrite ditolak karena regression surface; refactor-first ditolak karena baseline belum dapat dipercaya.

## Consequences
Progress lebih terukur dan rollback-friendly, tetapi perbaikan struktural besar ditunda sampai evidence stabil.
