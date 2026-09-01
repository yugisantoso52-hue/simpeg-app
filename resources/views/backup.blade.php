<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    💾 {{ __('Backup & Restore Database') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola salinan cadangan (*dump*) dan pemulihan data sistem kepegawaian SIMPEG secara penuh.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    {{-- Bootstrap 5 CSS & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <div class="py-6">
        <div class="container" style="max-width: 1140px;">

            {{-- Flash Message: Success --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                    <div class="flex-grow-1">
                        <strong class="d-block">Berhasil!</strong>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Flash Message: Error --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                    <div class="flex-grow-1">
                        <strong class="d-block">Terjadi Kesalahan!</strong>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-circle-fill fs-5 me-2 text-warning"></i>
                        <strong class="text-dark">Mohon periksa data yang diunggah:</strong>
                    </div>
                    <ul class="mb-0 ps-4 text-secondary small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Database Status Card --}}
            <div class="card shadow-sm border-0 mb-4 rounded-3">
                <div class="card-body bg-light rounded-3 p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="bi bi-database fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Status Koneksi Database</small>
                                <div class="fw-bold text-dark fs-6">{{ $dbInfo['database'] ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border px-3 py-2">
                                <i class="bi bi-hdd-network me-1"></i> Host: <strong>{{ $dbInfo['host'] ?? '127.0.0.1' }}:{{ $dbInfo['port'] ?? '3306' }}</strong>
                            </span>
                            <span class="badge bg-info-subtle text-info-emphasis border px-3 py-2">
                                <i class="bi bi-cpu me-1"></i> Driver: <strong>{{ strtoupper($dbInfo['driver'] ?? 'MYSQL') }}</strong>
                            </span>
                            <span class="badge bg-primary-subtle text-primary-emphasis border px-3 py-2">
                                <i class="bi bi-table me-1"></i> Total Tabel: <strong>{{ $dbInfo['table_count'] ?? 0 }}</strong>
                            </span>
                            <span class="badge bg-success-subtle text-success-emphasis border px-3 py-2">
                                <i class="bi bi-pie-chart me-1"></i> Est. Ukuran: <strong>{{ $dbInfo['size_mb'] ?? 0 }} MB</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row: 2 Cards (Backup & Restore) --}}
            <div class="row g-4">

                {{-- CARD 1: BACKUP DATABASE --}}
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-header bg-white border-bottom border-light py-3 px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-subtle text-primary rounded-3 p-2 me-3">
                                    <i class="bi bi-cloud-arrow-down-fill fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="card-title fw-bold mb-0 text-dark">Download Backup Database</h5>
                                    <small class="text-muted">Unduh salinan penuh skema & isi tabel database</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <p class="text-secondary small leading-relaxed mb-3">
                                    Fitur ini mengekstrak seluruh tabel, struktur indeks, relasi, dan data kepegawaian (termasuk riwayat SKP, mutasi, KGB, KP, dan e-cuti) ke dalam satu berkas berekstensi <strong>.sql</strong>.
                                </p>

                                <div class="bg-blue-50 border border-blue-200 rounded-3 p-3 mb-4">
                                    <div class="d-flex">
                                        <i class="bi bi-shield-check text-primary fs-5 me-2 mt-n1"></i>
                                        <div class="small text-slate-700">
                                            <strong>Rekomendasi Praktik Baik:</strong>
                                            <p class="mb-0 mt-1 text-muted" style="font-size: 0.8rem;">
                                                Lakukan pencadangan secara berkala sebelum pembaruan sistem besar atau proses sinkronisasi massal data pegawai.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <a href="{{ route('backup.export') }}" id="btnDownloadBackup" class="btn btn-primary btn-lg w-100 fw-semibold d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="bi bi-download me-2" id="iconDownload"></i>
                                    <span id="textDownload">Download Backup Database (.sql)</span>
                                </a>
                                <div id="backupProgressText" class="text-center text-muted small mt-2 d-none">
                                    <span class="spinner-border spinner-border-sm text-primary me-1" role="status" aria-hidden="true"></span>
                                    Sedang memproses dump database, unduhan akan dimulai otomatis...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: RESTORE DATA --}}
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-header bg-white border-bottom border-light py-3 px-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger-subtle text-danger rounded-3 p-2 me-3">
                                    <i class="bi bi-arrow-counterclockwise fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="card-title fw-bold mb-0 text-dark">Restore Data Database</h5>
                                    <small class="text-muted">Pulihkan struktur dan data dari berkas .sql</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <form id="formRestoreDatabase" action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <p class="text-secondary small leading-relaxed mb-3">
                                    Unggah file cadangan berekstensi <strong>.sql</strong> untuk memulihkan seluruh basis data ke kondisi file tersebut.
                                </p>

                                <div class="mb-3">
                                    <label for="backupFileInput" class="form-label fw-semibold small text-dark">
                                        Pilih File Backup (.sql) <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="file" 
                                        name="backup_file" 
                                        id="backupFileInput" 
                                        class="form-control form-control-lg @error('backup_file') is-invalid @enderror" 
                                        accept=".sql" 
                                        required>
                                    <div class="form-text text-muted" style="font-size: 0.78rem;">
                                        Format berkas: <strong>.sql</strong> | Maksimal berkas: <strong>100 MB</strong>
                                    </div>
                                    @error('backup_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="bg-amber-50 border border-amber-200 rounded-3 p-3 mb-4">
                                    <div class="d-flex">
                                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5 me-2 mt-n1"></i>
                                        <div class="small text-slate-700">
                                            <strong class="text-danger">Peringatan Kritis:</strong>
                                            <p class="mb-0 mt-1 text-muted" style="font-size: 0.8rem;">
                                                Proses restore akan menimpa seluruh tabel yang ada. Pastikan Anda telah membuat backup data terbaru sebelum melanjutkan.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="btnTriggerRestoreModal" class="btn btn-danger btn-lg w-100 fw-semibold d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="bi bi-upload me-2"></i>
                                    <span>Restore Data</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footnote Information --}}
            <div class="text-center text-muted small mt-4 pt-2">
                <i class="bi bi-info-circle me-1"></i>
                Halaman ini hanya dapat diakses oleh Administrator Sistem Informasi Kepegawaian (SIKAP).
            </div>

        </div>
    </div>

    {{-- MODAL KONFIRMASI RESTORE (BOOTSTRAP 5 MURNI TANPA JQUERY) --}}
    <div class="modal fade" id="restoreConfirmModal" tabindex="-1" aria-labelledby="restoreConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white py-3 px-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-octagon-fill fs-4 me-2"></i>
                        <h5 class="modal-title fw-bold" id="restoreConfirmModalLabel">Konfirmasi Pemulihan Database</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" id="modalCloseX" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 72px; height: 72px;">
                            <i class="bi bi-trash3-fill fs-1"></i>
                        </div>
                        <h6 class="fw-bold text-danger fs-5 mb-2">Peringatan Penting!</h6>
                    </div>

                    <div class="alert alert-danger border-danger-subtle small mb-3">
                        <p class="mb-0 fw-semibold text-center">
                            Peringatan: Seluruh data pegawai saat ini akan dihapus dan ditimpa oleh file backup. Lanjutkan?
                        </p>
                    </div>

                    <div class="bg-light rounded-3 p-3 border small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">File yang dipilih:</span>
                            <strong id="selectedFileNameDisplay" class="text-primary text-break">-</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Ukuran file:</span>
                            <span id="selectedFileSizeDisplay" class="text-secondary">-</span>
                        </div>
                    </div>

                    <p class="text-muted small text-center mt-3 mb-0">
                        Proses ini memerlukan waktu beberapa detik hingga menit tergantung ukuran file. Jangan menutup atau merefresh halaman selama proses berlangsung.
                    </p>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary px-4 fw-semibold" id="btnCancelRestore" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-danger px-4 fw-semibold d-flex align-items-center" id="btnConfirmRestore">
                        <span id="btnConfirmRestoreSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <i class="bi bi-check-lg me-1" id="btnConfirmRestoreIcon"></i>
                        <span id="btnConfirmRestoreText">Ya, Lanjutkan Restore</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap 5 JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    {{-- Logika JavaScript Murni (Vanilla JS - Tanpa jQuery) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formRestore = document.getElementById('formRestoreDatabase');
            const fileInput = document.getElementById('backupFileInput');
            const btnTrigger = document.getElementById('btnTriggerRestoreModal');
            const modalElement = document.getElementById('restoreConfirmModal');
            const restoreModal = new bootstrap.Modal(modalElement);

            const btnConfirmRestore = document.getElementById('btnConfirmRestore');
            const btnCancelRestore = document.getElementById('btnCancelRestore');
            const modalCloseX = document.getElementById('modalCloseX');
            const btnConfirmRestoreSpinner = document.getElementById('btnConfirmRestoreSpinner');
            const btnConfirmRestoreIcon = document.getElementById('btnConfirmRestoreIcon');
            const btnConfirmRestoreText = document.getElementById('btnConfirmRestoreText');

            const selectedFileNameDisplay = document.getElementById('selectedFileNameDisplay');
            const selectedFileSizeDisplay = document.getElementById('selectedFileSizeDisplay');

            // 1. Validasi saat tombol Restore ditekan untuk membuka modal
            btnTrigger.addEventListener('click', function(e) {
                e.preventDefault();

                if (!fileInput.files || fileInput.files.length === 0) {
                    fileInput.classList.add('is-invalid');
                    fileInput.focus();
                    alert('Silakan pilih file backup (.sql) terlebih dahulu.');
                    return;
                }

                const file = fileInput.files[0];
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();

                if (fileExt !== 'sql') {
                    fileInput.classList.add('is-invalid');
                    alert('Format berkas tidak valid! Berkas harus berekstensi .sql');
                    return;
                }

                fileInput.classList.remove('is-invalid');

                // Tampilkan info file ke modal
                selectedFileNameDisplay.textContent = fileName;
                const sizeKb = (file.size / 1024).toFixed(2);
                if (file.size > 1024 * 1024) {
                    selectedFileSizeDisplay.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                } else {
                    selectedFileSizeDisplay.textContent = sizeKb + ' KB';
                }

                // Tampilkan modal Bootstrap 5 murni
                restoreModal.show();
            });

            // Hilangkan status invalid jika file baru dipilih
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    this.classList.remove('is-invalid');
                }
            });

            // 2. Eksekusi Restore dari Modal (Mencegah Klik Ganda & Menampilkan Spinner)
            btnConfirmRestore.addEventListener('click', function() {
                // Nonaktifkan tombol agar tidak ada double click
                btnConfirmRestore.disabled = true;
                btnCancelRestore.disabled = true;
                modalCloseX.disabled = true;

                // Tampilkan spinner loading
                btnConfirmRestoreSpinner.classList.remove('d-none');
                btnConfirmRestoreIcon.classList.add('d-none');
                btnConfirmRestoreText.textContent = 'Sedang Merestore Database...';

                // Submit form
                formRestore.submit();
            });

            // 3. Efek Loading pada Tombol Download Backup
            const btnDownloadBackup = document.getElementById('btnDownloadBackup');
            const iconDownload = document.getElementById('iconDownload');
            const textDownload = document.getElementById('textDownload');
            const backupProgressText = document.getElementById('backupProgressText');

            btnDownloadBackup.addEventListener('click', function() {
                backupProgressText.classList.remove('d-none');
                iconDownload.className = 'spinner-border spinner-border-sm me-2';
                textDownload.textContent = 'Menyiapkan File Backup...';

                // Kembalikan status tombol setelah 5 detik agar pengguna dapat mendownload lagi jika diperlukan
                setTimeout(function() {
                    iconDownload.className = 'bi bi-download me-2';
                    textDownload.textContent = 'Download Backup Database (.sql)';
                    backupProgressText.classList.add('d-none');
                }, 5000);
            });
        });
    </script>
</x-app-layout>
