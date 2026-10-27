{{-- Modal Upload & Lengkapi Berkas Mandiri Pegawai --}}
<div x-show="uploadModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 text-left border border-slate-200"
         @click.outside="uploadModalOpen = false">

        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    📤
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800" x-text="'Unggah / Lengkapi ' + modalTitle"></h3>
                    <p class="text-[11px] text-slate-500">Berkas akan tersimpan langsung di profil data kepegawaian Anda</p>
                </div>
            </div>
            <button type="button" @click="uploadModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1">✕</button>
        </div>

        <form action="{{ route('pengajuan-karir.upload-syarat') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="jenis_berkas" :value="modalField">
            <input type="hidden" name="redirect_tab" :value="modalRedirectTab">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Pilih File Dokumen <span class="text-red-500">*</span>
                </label>
                <input type="file" name="berkas_file" required accept=".pdf,.jpg,.jpeg,.png"
                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl p-2 focus:outline-hidden focus:ring-2 focus:ring-indigo-500">
                <p class="text-[10px] text-slate-400 mt-1">Format didukung: PDF, JPG, PNG (Maksimal 5 MB)</p>
            </div>

            <div class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-[11px] text-amber-800 flex items-start gap-2">
                <span class="text-base shrink-0">💡</span>
                <div>
                    <strong>Tips:</strong> Pastikan hasil scan dokumen terbaca jelas, tidak terpotong, dan memuat tanda tangan/cap stempel sah dari pejabat yang berwenang.
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 transition active:scale-95">
                    <span>💾 Simpan & Perbarui Dokumen</span>
                </button>
            </div>
        </form>
    </div>
</div>
