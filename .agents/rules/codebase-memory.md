# Aturan Penggunaan codebase-memory-mcp (SIMPEG Project)

Aturan ini WAJIB dipatuhi oleh agent ketika melakukan tugas apa pun pada proyek SIMPEG, termasuk analisis bug, debugging, refaktorisasi/perbaikan kode, maupun penambahan fitur/menu baru.

---

## 1. Prinsip Utama: Graph-First Investigation

Sebelum membaca file secara penuh (`view_file` dalam jumlah baris besar) atau mengubah kode:
1. **WAJIB Menggunakan Tool `codebase-memory-mcp` Terlebih Dahulu**:
   - `search_graph`: Cari simbol yang relevan (nama Controller, Model, Service, Method, Function, Route, dsb.).
   - `trace_path`: Telusuri alur pemanggilan (call graph) dan relasi dependensi antar-komponen untuk memahami alur data dan dampak perubahan.
   - `get_code_snippet`: Ambil definisi kode simbol/method spesifik yang menjadi target investigasi atau perbaikan tanpa perlu membaca seluruh isi file.
   - `query_graph` & `get_architecture`: Gunakan untuk memahami relasi multi-hop dan arsitektur umum modul bila cakupan fitur cukup luas.
2. **Efisiensi Konteks & Pembacaan File**:
   - Hindari membaca file source code utuh secara langsung jika informasi yang dicari dapat diperoleh dari simbol graph.
   - Gunakan `view_file` hanya dengan rentang baris (`StartLine`, `EndLine`) yang spesifik setelah target dipastikan melalui graph, atau untuk file non-kode/template yang tidak terindeks.

---

## 2. Alur Kerja Standar (Workflow)

### Fase 1: Investigasi & Perencanaan
1. Identifikasi simbol/komponen awal menggunakan `search_graph(query=...)`.
2. Analisis dampak dan alur keterkaitan fungsi dengan `trace_path(from_symbol=..., to_symbol=...)` atau pengecekan relasi pemanggil/pemanggil balik.
3. Telaah implementasi fungsi dengan `get_code_snippet(symbol=..., repo_path="c:\\laragon\\www\\simpeg")`.

### Fase 2: Implementasi Perubahan Kode
- Lakukan perbaikan atau penambahan fitur dengan `replace_file_content`, `multi_replace_file_content`, atau `write_to_file`.
- Pastikan kode mengikuti struktur dan konvensi yang sudah ada di proyek SIMPEG.

### Fase 3: Pembaruan Memori (Re-indexing Wajib)
- Setiap kali selesai memodifikasi, menambah, atau menghapus kode:
  - **WAJIB** menjalankan kembali `index_repository`:
    ```json
    {
      "repo_path": "c:\\laragon\\www\\simpeg",
      "mode": "full",
      "persistence": true
    }
    ```
  - Pastikan proses indexing selesai dengan status sukses agar knowledge graph `codebase-memory-mcp` selalu mutakhir (up-to-date) untuk instruksi berikutnya.
