---
name: web-performance-addyosmani
description: Addy Osmani web performance engineering, Core Web Vitals (LCP, INP, CLS), JavaScript bundle budgeting, image optimization, and web design patterns. Use when optimizing web applications, reducing page load times, improving Core Web Vitals, or auditing front-end performance.
---

# Addy Osmani Web Performance & Engineering Skill

**Document ID:** SKL001  
**Version:** 1.0  
**Status:** ACTIVE  
**Category:** Agent Skill Extension  
**Reference:** UUD001 - AIOS Unified Architecture v1.0

---

Skill ini mengadopsi standar rekayasa performa web modern dan panduan *Core Web Vitals* yang dipelopori oleh **Addy Osmani** (Chief Engineering Leader Chrome / Author of *Learning JavaScript Design Patterns*).

---

# 1. Core Web Vitals Optimization Rules

Setiap kali melakukan audit, pembuatan UI, atau refactoring frontend, AI Agent WAJIB menerapkan batasan performa berikut:

### A. LCP (Largest Contentful Paint) - SLA < 2.5s
- **Hero Resource Preload**: Elemen gambar atau font terbesar di atas lipatan (*above-the-fold*) wajib menggunakan `<link rel="preload" as="image" href="...">` dan `fetchpriority="high"`.
- **No Lazy-Loading on Hero**: DILARANG menambahkan `loading="lazy"` pada gambar hero LCP.
- **Server-Timing Header**: Optimalkan TTFT (Time to First Byte) dengan pencadangan CDN dan caching kueri backend.

### B. INP (Interaction to Next Paint) - SLA < 200ms
- **Break Long Tasks**: Pecah eksekusi JavaScript yang berjalan > 50ms menjadi mikro-tugas menggunakan `yieldToMain()` atau `requestIdleCallback()`.
- **Non-Blocking Event Listeners**: Gunakan `passive: true` pada event listener scroll dan touch.
- **Debounce & Throttle**: Terapkan debounce (250ms) pada event listener input pencarian dan window resize.

### C. CLS (Cumulative Layout Shift) - SLA < 0.1
- **Explicit Dimensions**: Selalu sertakan atribut `width` dan `height` eksplisit pada seluruh elemen `<img>`, `<svg>`, dan `<iframe>`.
- **Aspect-Ratio CSS**: Gunakan `aspect-ratio: 16 / 9` pada kontainer media untuk mengamankan tata letak ruang sebelum aset selesai diunduh.
- **Font Display Swap**: Gunakan `font-display: swap` pada Google Fonts untuk mencegah FLAS (Flash of Unstyled Text).

---

# 2. JavaScript Delivery & Budgeting

1. **Bundle Size Budget**: Batasi ukuran berkas JavaScript awal (*initial bundle*) maksimal **170 KB gzipped**.
2. **Route-Based Code Splitting**: Gunakan `import()` dinamis untuk memuat rute dan modul berat secara *lazy-load*.
3. **Tree-Shaking Enforcement**: Gunakan pengimporan modul spesifik (`import debounce from 'lodash/debounce'`) bukan impor monolitik (`import { debounce } from 'lodash'`).

---

# 3. Image & Media Asset Engineering

1. **Next-Gen Image Formats**: Utamakan format WebP atau AVIF dengan tag `<picture>` fallback.
2. **Responsive Image Srcset**: Selalu sertakan `srcset` dan `sizes` untuk menyajikan ukuran gambar yang sesuai dengan resolusi layar perangkat pengguna.
3. **Native Lazy Loading**: Tambahkan `loading="lazy"` pada seluruh gambar di bawah lipatan (*below-the-fold*).

---

# 4. JavaScript Design Patterns (Addy Osmani Standard)

- **Module Pattern & Encapsulation**: Isolasi variabel privat dan hanya ekspos API publik yang dibutuhkan.
- **Observer Pattern**: Gunakan `IntersectionObserver` untuk lazy-loading dan `ResizeObserver` untuk komponen UI responsif alih-alih polling event listener window.
- **Command & Factory Pattern**: Gunakan factory pattern untuk instansiasi komponen UI kompleks.
