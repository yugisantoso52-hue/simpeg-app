# Aturan Otomatisasi codebase-memory-mcp (SIMPEG)

Aturan ini bersifat **MANDATORI / WAJIB** dipatuhi oleh AI Agent dalam setiap interaksi dan pengerjaan tugas pada proyek SIMPEG.

---

## 1. Wajib Gunakan `codebase-memory-mcp` di Awal Tugas (Analisis & Koding)

Setiap kali memulai tugas analisis, penelusuran bug, debugging, refaktorisasi, maupun penambahan fitur/koding baru:

- **DILARANG** langsung membaca file kode secara manual (`view_file` penuh) atau menebak struktur kode.
- **WAJIB** terlebih dahulu memanfaatkan tool `codebase-memory-mcp` via `call_mcp_tool`:
  1. **`search_graph`**: Mencari simbol terkait (Controller, Model, Service, Function, Route, dsb.).
  2. **`trace_path`**: Menelusuri call graph, dependensi, dan alur relasi antar-komponen.
  3. **`get_code_snippet`**: Mengambil definisi kode simbol atau method spesifik yang menjadi target investigasi tanpa perlu membaca seluruh isi file.

> **Tujuan**: Memastikan pemahaman konteks kode akurat, efisien dalam penggunaan token, dan meminimalkan regresi dependensi.

---

## 2. Wajib Re-Indexing Otomatis di Akhir Pekerjaan

Setiap kali kamu selesai melakukan modifikasi kode (membuat file baru, mengedit file yang ada, atau menghapus file kode apa pun):

- **WAJIB OTOMATIS** menjalankan tool `index_repository` pada akhir pengerjaan sebelum memberikan respons final kepada user.
- **TIDAK PERLU MENUNGGU** instruksi atau permintaan dari user.
- Pemanggilan dilakukan via `call_mcp_tool`:
  - **ServerName**: `codebase-memory-mcp`
  - **ToolName**: `index_repository`
  - **Arguments**:
    ```json
    {
      "repo_path": "c:\\laragon\\www\\simpeg",
      "mode": "full",
      "persistence": true
    }
    ```

> **Tujuan**: Memastikan database knowledge graph (`.codebase-memory/graph.db.zst`) selalu mutakhir (up-to-date) sehingga sesi investigasi selanjutnya selalu mencerminkan kondisi codebase terbaru.
