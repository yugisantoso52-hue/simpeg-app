<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <span>🛡️</span> {{ __('Audit Log & Rekam Aktivitas System') }}
            </h2>
            <form action="{{ route('audit-logs.prune') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membersihkan log aktivitas yang sudah berusia lebih dari 90 hari?');" class="inline">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300 flex items-center gap-1">
                    🧹 Bersihkan Log (>90 Hari)
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            {{-- 6 KARTU STATISTIK LOG --}}
            <x-enterprise.stat-grid :cols="6">
                <x-enterprise.stat-card
                    title="Total Log"
                    :value="$stats['total'] ?? 0"
                    icon="history"
                    color="slate"
                />
                <x-enterprise.stat-card
                    title="Keamanan"
                    :value="$stats['security'] ?? 0"
                    icon="shield-check"
                    color="red"
                />
                <x-enterprise.stat-card
                    title="Aktivitas Login"
                    :value="$stats['login'] ?? 0"
                    icon="user-check"
                    color="blue"
                />
                <x-enterprise.stat-card
                    title="Tambah Data"
                    :value="$stats['create'] ?? 0"
                    icon="user-plus"
                    color="emerald"
                />
                <x-enterprise.stat-card
                    title="Edit Data"
                    :value="$stats['update'] ?? 0"
                    icon="file-text"
                    color="amber"
                />
                <x-enterprise.stat-card
                    title="Upload Berkas"
                    :value="$stats['upload'] ?? 0"
                    icon="inbox"
                    color="sky"
                />
            </x-enterprise.stat-grid>

            {{-- FILTER & PENCARIAN --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <span class="text-xs font-bold text-slate-500 shrink-0">Filter Tipe:</span>
                        <select name="type" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="ALL" {{ $type === 'ALL' || !$type ? 'selected' : '' }}>Semua Aktivitas</option>
                            <option value="SECURITY" {{ $type === 'SECURITY' ? 'selected' : '' }}>🛡️ PERINGATAN KEAMANAN</option>
                            <option value="LOGIN" {{ $type === 'LOGIN' ? 'selected' : '' }}>🔑 LOGIN</option>
                            <option value="LOGOUT" {{ $type === 'LOGOUT' ? 'selected' : '' }}>🚪 LOGOUT</option>
                            <option value="CREATE" {{ $type === 'CREATE' ? 'selected' : '' }}>➕ CREATE (Tambah Data)</option>
                            <option value="UPDATE" {{ $type === 'UPDATE' ? 'selected' : '' }}>📝 UPDATE (Edit Data)</option>
                            <option value="DELETE" {{ $type === 'DELETE' ? 'selected' : '' }}>🗑️ DELETE (Hapus Data)</option>
                            <option value="UPLOAD_FILE" {{ $type === 'UPLOAD_FILE' ? 'selected' : '' }}>📎 UPLOAD (Unggah File)</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-80">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, IP, deskripsi..." class="w-full text-xs rounded-xl border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                            Cari
                        </button>
                        @if($search || ($type && $type !== 'ALL'))
                            <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-300 transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- TABEL AUDIT LOGS --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">Waktu Kejadian</th>
                                <th class="px-4 py-3">Pengguna (User)</th>
                                <th class="px-4 py-3">Jenis Aktivitas</th>
                                <th class="px-4 py-3">Modul</th>
                                <th class="px-4 py-3">Rincian Aktivitas</th>
                                <th class="px-4 py-3">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-mono font-bold text-slate-900">{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i:s') }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-900">{{ $log->user_name ?? 'Sistem' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $log->user_email ?? '-' }}</div>
                                        @if($log->role_name)
                                            <span class="inline-block px-1.5 py-0.5 mt-0.5 text-[9px] font-bold rounded bg-slate-100 text-slate-600">{{ strtoupper($log->role_name) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @switch($log->activity_type)
                                            @case('SECURITY_SUSPEND')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-600 text-white animate-pulse">⛔ AKUN DIBEKUKAN</span>
                                                @break
                                            @case('SECURITY_LOCKOUT')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white">🔒 KUNCI LOGIN</span>
                                                @break
                                            @case('SECURITY_IP_BLOCK')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white">🛑 BLOKIR IP</span>
                                                @break
                                            @case('SECURITY_BLOCKED')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">🚫 AKSES DITOLAK</span>
                                                @break
                                            @case('SECURITY_UNSUSPEND')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">🔓 BUKA BEKU</span>
                                                @break
                                            @case('SECURITY_RESET_PASSWORD')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">🔄 RESET SANDI</span>
                                                @break
                                            @case('LOGIN')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">🔑 LOGIN</span>
                                                @break
                                            @case('LOGOUT')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">🚪 LOGOUT</span>
                                                @break
                                            @case('CREATE')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">➕ CREATE</span>
                                                @break
                                            @case('UPDATE')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">📝 UPDATE</span>
                                                @break
                                            @case('DELETE')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">🗑️ DELETE</span>
                                                @break
                                            @case('UPLOAD_FILE')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">📎 UPLOAD</span>
                                                @break
                                            @default
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">{{ $log->activity_type }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-700">
                                        {{ $log->subject_type ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-800 font-medium max-w-md">
                                        {{ $log->description }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-[11px] text-slate-500">
                                        {{ $log->ip_address ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                        <span class="text-3xl block mb-2">🛡️</span>
                                        Belum ada catatan log aktivitas yang terekam.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->total() > 0)
                    <div class="p-4 border-t border-slate-100">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
