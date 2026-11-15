# AGENTS.md — Coding Standards, Architecture & Refactoring Rules
**CMS ORMAWA ITI** — Multi-Tenant College Organization Content Management System

Dokumen ini adalah pedoman baku dan aturan pengembangan bagi coding agent dan developer dalam memelihara, menulis, dan merefaktor codebase CMS ORMAWA ITI.

---

## 1. Core Principles (Ponytail Principles)

Setiap modifikasi kode wajib mematuhi 8 prinsip penulisan kode teruji:

1. **YAGNI (You Aren't Gonna Need It):** Jangan membuat abstraksi, layer, atau konfigurasi untuk use-case yang belum ada atau tidak diminta.
2. **Reuse Before Rebuild:** Cari komponen, helper, store, composable, atau method yang sudah ada sebelum menulis implementasi baru.
3. **Prefer Existing Platform Features:** Utamakan fitur native PHP 8.4, Laravel 13, Vue 3 (Composition API `<script setup>`), Tailwind CSS v4, dan Browser Web APIs sebelum membuat wrapper.
4. **Minimum Necessary Code:** Tulis kode seringkas mungkin tanpa mengorbankan keamanan, type safety, dan keterbacaan.
5. **Avoid Overengineering:** Jangan membuat repository layer, interface berlebih, abstract factory, atau helper wrapper untuk CRUD sederhana.
6. **Preserve Safety:** Dilarang mengurangi atau menghapus validasi, sanitasi input, autentikasi Sanctum, otorisasi Spatie RBAC, isolasi multi-tenant `BelongsToOrganization`, database transactions, atau error handling demi menyingkat kode.
7. **Read Before Changing:** Pahami arsitektur menyeluruh, alur data, dan relasi dependensi sebelum memodifikasi file.
8. **Small & Verifiable Changes:** Setiap perubahan harus bertahap, memiliki cakupan terbatas, serta lulus uji build frontend (`npm run build-only`) dan backend test (`php artisan test`).

---

## 2. General Rules

* **Naming Conventions:**
  * Vue Components: PascalCase (misal: `MemberTable.vue`, `CommitteeFormModal.vue`).
  * Vue Composables: camelCase diawali `use` (misal: `useMemberFilter.ts`).
  * Laravel Models: Singular PascalCase (`Member.php`, `Committee.php`).
  * Laravel Controllers: Singular PascalCase berakhiran Controller (`MemberController.php`).
  * Database Columns: snake_case (`organization_id`, `status_mode`).
* **Single Responsibility:** Satu file, kelas, atau komponen hanya menangani satu tanggung jawab utama.
* **No Deep Nesting:** Maksimal 3 tingkat indentasi/nested conditional (`if/else`, loops). Gunakan *early return* (*guard clauses*).
* **Eliminate Magic Values:** Hindari magic number atau magic string; gunakan konstanta, enum, atau tipe data yang jelas.
* **Keep Business Logic in Backend:** Jangan membebankan business logic kritis (misal: evaluasi periode, hashing, otorisasi) ke browser/UI.
* **Meaningful Comments Only:** Tulis komentar hanya untuk menjelaskan *alasan* teknis atau aturan bisnis khusus, bukan mengulang apa yang sudah jelas dari kode.

---

## 3. Thresholds for Review & Refactoring

Gunakan batasan ini sebagai sinyal peninjauan kualitas:

| Komponen / Artefak | Batas Tinjau | Tindakan Jika Melebihi Batas |
| :--- | :---: | :--- |
| **Komponen Vue (`.vue`)** | > 200 baris | Pisahkan menjadi pola **Parent–Child** atau ekstrak reusable child modal/table. |
| **Controller Laravel (`.php`)** | > 150 baris | Pindahkan validasi ke **Form Request**, logika alur ke **Action/Service**, query ke **Model Scope**. |
| **Fungsi / Method** | > 40 baris | Pecah menjadi sub-metode privat yang berfokus pada satu langkah operasi. |
| **Parameter Fungsi** | > 4 parameter | Gunakan Data Transfer Object (DTO), Typed Array, atau Request Object. |
| **Tingkat Nested Conditional** | > 3 level | Refactor menggunakan *guard clause* (early exit) atau *match/switch* expression. |

---

## 4. Frontend Parent–Child Architecture (Vue 3)

### A. Parent Component Responsibilities:
* Mengelola state halaman (loading, list items, active tab, selected filter, pagination).
* Mengambil data dari API via service/store.
* Mengatur alur komunikasi antar-child components.
* Mendengarkan *event emits* dari child dan memperbarui state utama.

### B. Child Component Responsibilities:
* Menerima data melalui **Typed Props**.
* Menangani interaksi lokal spesifik (misal: toggle dropdown lokal, input visual form).
* Mengirim aksi ke parent melalui **Typed Emits** (`@submit`, `@cancel`, `@edit`, `@delete`).
* Tidak melakukan direct API mutation yang mengubah state di luar tanggung jawabnya.

### C. Standard Component Splitting Pattern:
```text
FeaturePage.vue (Parent: State, API coordinator, Tabs, Filter)
├── FeatureHeader.vue (Action buttons, title, summary stats)
├── FeatureFilterBar.vue (Search input, dropdown filters)
├── FeatureTable.vue (Presentation of data rows, status badges)
│   └── FeatureTableRow.vue (Single record display & action buttons)
├── FeatureFormModal.vue (Create/Edit modal container)
│   └── FeatureForm.vue (Form inputs & local client validation)
└── CommonPagination.vue (Shared reusable pagination bar)
```

---

## 5. Backend Architecture & Clean Separation (Laravel 13)

### A. Controller
* Bertanggung jawab terhadap HTTP request flow: terima request -> otorisasi -> validasi Form Request -> panggil Model/Action -> kembalikan JSON Response.
* Tidak boleh berisi raw CSV parsing 200 baris, logika kalkulasi bisnis berantai, atau raw SQL panjang.

### B. Form Request
* Gunakan class Form Request (`app/Http/Requests/...`) untuk validasi form atau request berulang dengan pesan kustom bahasa Indonesia.

### C. Model & Eloquent Scopes
* Wajib menerapkan `BelongsToOrganization` trait untuk entitas tenant.
* Tempatkan filter query kompleks pada Local Scopes (misal: `scopeEffectiveActive`, `scopeInPeriod`).

### D. Actions / Services
* Hanya dibuat jika proses bisnis memiliki multi-step flow yang perlu diuji secara independen atau dipakai ulang di banyak endpoint (misal: `ImportMembersAction`, `EvaluateCommitteeStatusService`).
* Hindari membuat service wrapper untuk CRUD sederhana yang cukup ditangani Eloquent.

### E. Security & Multi-Tenancy
* **Multi-tenant isolation:** Setiap operasi query wajib menyertakan `organization_id` yang divalidasi dari user login (bukan dari inputan bebas frontend).
* **Sanctum & RBAC:** Cek izin menggunakan `$this->authorize()` atau middleware `permission:xxx`.
* **Database Transactions:** Wajib membungkus operasi multi-tabel dalam `DB::transaction()`.

---

## 6. Testing & Regression Verification Checklist

Sebelum menyelesaikan refactoring pada file atau modul apa pun:
1. `npm run build-only` wajib berhasil tanpa error modul.
2. `npm run type-check` wajib lulus verifikasi tipe TypeScript.
3. `php artisan test` pada modul terkait wajib 100% hijau (*all assertions pass*).
4. Kontrak JSON API (`status`, `data`, `meta`, `message`) tidak boleh berubah.
5. Catat setiap modifikasi pada file `docs/audit/refactoring-log.md`.
