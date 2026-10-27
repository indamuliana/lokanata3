<div class="py-6" x-data="{ searchPermission: '', filterType: 'all' }">
    <div class="max-w-7xl 2xl:max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        
        <!-- Notifications / Flash Alerts -->
        @if (session()->has('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm animate-in fade-in duration-150">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">✕</button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm animate-in fade-in duration-150">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-900 cursor-pointer">✕</button>
            </div>
        @endif

        @if (session()->has('info'))
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-center justify-between shadow-sm animate-in fade-in duration-150">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">{{ session('info') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-blue-600 hover:text-blue-900 cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2.5 tracking-tight">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 text-lg">🛡️</span>
                    <span>Role &amp; Hak Akses Pengguna</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Atur peran wewenang staf (Role), buat role baru, dan konfigurasi hak lihat (view) serta hak kelola/eksekusi pada 15 modul operasional hotel.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="openCreateRoleModal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Role Baru</span>
                </button>
            </div>
        </div>

        <!-- Master-Detail Desktop Layout (Flexbox: Fixed Sidebar + Expanding Matrix) -->
        <div class="flex flex-col lg:flex-row gap-6 items-start w-full">
            
            <!-- LEFT PANEL: Daftar Role (Sticky Sidebar di Desktop) -->
            <div class="w-full lg:w-80 xl:w-96 shrink-0 lg:sticky lg:top-6 self-start space-y-4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden p-4 space-y-3">
                    <div class="flex items-center justify-between pb-2.5 border-b border-gray-100">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-black uppercase tracking-wider text-gray-500">Pilih Role</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700">
                            {{ count($roles) }} Role Terdaftar
                        </span>
                    </div>

                    <!-- Role Selection Cards -->
                    <div class="space-y-2 max-h-[calc(100vh-14rem)] overflow-y-auto pr-0.5">
                        @foreach($roles as $r)
                            @php
                                $isSelected = ($selectedRoleId === $r->id);
                                $isCore = in_array($r->name, $protectedRoles);
                                $badgeColor = match($r->name) {
                                    'Manager' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'Resepsionis' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'Housekeeping' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    default => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                };
                            @endphp
                            <div class="group relative rounded-xl border p-3 transition cursor-pointer {{ $isSelected ? 'bg-emerald-50/90 border-emerald-500 shadow-sm ring-2 ring-emerald-500/20' : 'bg-white border-gray-200 hover:border-gray-300 hover:bg-gray-50/70' }}"
                                 wire:click="selectRole({{ $r->id }})">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-3 h-3 rounded-full shrink-0 {{ $isSelected ? 'bg-emerald-600 ring-3 ring-emerald-200' : 'bg-gray-300' }}"></span>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-xs sm:text-sm text-gray-900 truncate flex items-center gap-1.5">
                                                <span class="truncate">{{ $r->name }}</span>
                                                @if($isCore)
                                                    <span class="text-[10px] text-gray-400 shrink-0" title="Role Inti Sistem (Dilindungi)">🔒</span>
                                                @endif
                                            </h4>
                                            <p class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5">
                                                <span>{{ $r->users_count }} staf aktif</span>
                                                @if($r->name === 'Manager')
                                                    <span class="text-[10px] text-purple-600 font-semibold">• Super Admin</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Action buttons for custom roles -->
                                    <div class="flex items-center gap-1 shrink-0" @click.stop>
                                        @if(!$isCore)
                                            <button type="button" wire:click="openEditRoleModal({{ $r->id }})" title="Ubah Nama Role" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button type="button" wire:click="deleteRole({{ $r->id }})" wire:confirm="Yakin ingin menghapus role '{{ $r->name }}'?" title="Hapus Role" class="p-1.5 rounded-lg text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @else
                                            <span class="text-[10px] px-1.5 py-0.5 rounded font-bold border {{ $badgeColor }}">
                                                Inti
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Add Role Shortcut inside sidebar -->
                    <div class="pt-2 border-t border-gray-100">
                        <button type="button" wire:click="openCreateRoleModal" class="w-full py-2 bg-gray-50 hover:bg-emerald-50 text-gray-700 hover:text-emerald-800 text-xs font-semibold rounded-xl border border-dashed border-gray-300 hover:border-emerald-300 transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Buat Role Kustom Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Petunjuk Keamanan / Info Tip Box -->
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl border border-gray-200/80 p-4 text-xs space-y-2 shadow-2xs">
                    <div class="flex items-center gap-1.5 font-bold text-gray-700">
                        <span>💡</span>
                        <span>Prinsip Hak Akses:</span>
                    </div>
                    <ul class="space-y-1 text-gray-500 text-[11px] leading-relaxed list-disc list-inside">
                        <li><strong>Manager:</strong> Super Admin permanen, selalu memiliki akses ke seluruh fitur sistem.</li>
                        <li><strong>Hak Akses Bertingkat:</strong> Hak <span class="text-blue-700 font-semibold">Lihat</span> hanya menampilkan data, hak <span class="text-emerald-700 font-semibold">Kelola</span> mengizinkan aksi transaksi.</li>
                        <li><strong>Keamanan:</strong> Tombol aksi dan menu navigasi otomatis disembunyikan jika staf tidak memiliki hak akses.</li>
                    </ul>
                </div>
            </div>

            <!-- RIGHT PANEL: Matriks Hak Akses (Fills 100% Remaining Width) -->
            <div class="flex-1 min-w-0 w-full bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                @if($selectedRole)
                    <!-- Sticky Matrix Action Header -->
                    <div class="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-5 py-3.5 border-b border-gray-200 shadow-2xs">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider shrink-0 {{ match($selectedRole->name) { 'Manager' => 'bg-purple-100 text-purple-900 border border-purple-200', 'Resepsionis' => 'bg-blue-100 text-blue-900 border border-blue-200', 'Housekeeping' => 'bg-amber-100 text-amber-900 border border-amber-200', default => 'bg-emerald-100 text-emerald-900 border border-emerald-200' } }}">
                                    Role Aktif
                                </span>
                                <div class="min-w-0">
                                    <h2 class="text-lg font-black text-gray-900 truncate leading-tight">{{ $selectedRole->name }}</h2>
                                    <p class="text-[11px] text-gray-500 mt-0.5 truncate">
                                        @if($selectedRole->name === 'Manager')
                                            <span class="text-purple-700 font-bold">👑 Super Admin: Seluruh 32 hak akses aktif</span>
                                        @else
                                            <strong class="text-emerald-700 font-bold">{{ count($selectedPermissions) }}</strong> dari 32 hak akses diaktifkan
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if($selectedRole->name !== 'Manager')
                                <div class="flex items-center gap-2 shrink-0">
                                    <!-- Global Select All / Clear All Buttons -->
                                    <button type="button" wire:click="selectAllPermissions" class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-semibold rounded-lg transition cursor-pointer" title="Centang seluruh 32 hak akses">
                                        Centang Semua
                                    </button>
                                    <button type="button" wire:click="clearAllPermissions" class="px-2.5 py-1.5 bg-gray-100 hover:bg-rose-50 text-gray-700 hover:text-rose-700 text-[11px] font-semibold rounded-lg transition cursor-pointer" title="Kosongkan seluruh hak akses">
                                        Kosongkan
                                    </button>
                                    <button type="button" wire:click="savePermissions" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Simpan Hak Akses</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Search & Filter Bar inside Matrix -->
                    <div class="px-5 py-2.5 bg-gray-50/80 border-b border-gray-200/80 flex flex-col sm:flex-row gap-2.5 items-center justify-between text-xs">
                        <div class="relative w-full sm:w-72">
                            <input type="text"
                                   x-model="searchPermission"
                                   placeholder="Cari izin / modul / kode..."
                                   class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border-gray-300 bg-white focus:ring-emerald-500 focus:border-emerald-500">
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        <div class="flex items-center gap-1.5 w-full sm:w-auto overflow-x-auto">
                            <span class="text-[11px] font-semibold text-gray-500 whitespace-nowrap">Filter:</span>
                            <button type="button" @click="filterType = 'all'" :class="filterType === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'" class="px-2.5 py-1 text-[11px] font-medium rounded-lg transition cursor-pointer whitespace-nowrap">
                                Semua
                            </button>
                            <button type="button" @click="filterType = 'view'" :class="filterType === 'view' ? 'bg-blue-600 text-white' : 'bg-white text-blue-700 hover:bg-blue-50 border border-blue-200'" class="px-2.5 py-1 text-[11px] font-medium rounded-lg transition cursor-pointer whitespace-nowrap flex items-center gap-1">
                                <span>👁️</span>
                                <span>Lihat Saja</span>
                            </button>
                            <button type="button" @click="filterType = 'manage'" :class="filterType === 'manage' ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200'" class="px-2.5 py-1 text-[11px] font-medium rounded-lg transition cursor-pointer whitespace-nowrap flex items-center gap-1">
                                <span>⚙️</span>
                                <span>Kelola &amp; Aksi</span>
                            </button>
                        </div>
                    </div>

                    @if($selectedRole->name === 'Manager')
                        <div class="p-5">
                            <div class="p-4 rounded-xl bg-purple-50/80 border border-purple-200 text-purple-900 text-xs flex items-start gap-3">
                                <span class="text-xl shrink-0">👑</span>
                                <div class="space-y-1">
                                    <h4 class="font-bold text-sm text-purple-950">Super Admin Privilege (Akses Penuh Tanpa Batas)</h4>
                                    <p class="text-purple-800 leading-relaxed text-[11px]">
                                        Role <strong>Manager</strong> otomatis memiliki seluruh hak akses sistem secara permanen melalui otorisasi tingkat inti (<code>Gate::before</code>). Hal ini menjaga properti agar tidak pernah mengalami penguncian sistem (system lockout). Hak akses Manager tidak dapat dikurangi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Modules Grid: 2 Kolom Responsif di Desktop XL, 1 Kolom di Tablet/Laptop -->
                    <div class="p-5">
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 items-start">
                            @foreach($groupedModules as $modKey => $module)
                                @php
                                    $modCodes = array_column($module['permissions'], 'code');
                                    $checkedCount = count(array_intersect($modCodes, $selectedPermissions));
                                    $allChecked = ($checkedCount === count($modCodes));
                                    $searchBlob = strtolower($module['name'] . ' ' . implode(' ', array_column($module['permissions'], 'name')) . ' ' . implode(' ', $modCodes));
                                @endphp
                                <div class="rounded-xl border border-gray-200/90 bg-gray-50/40 p-3.5 space-y-2.5 transition hover:border-gray-300"
                                     x-show="searchPermission === '' || '{{ $searchBlob }}'.includes(searchPermission.toLowerCase())">
                                    
                                    <!-- Module Header -->
                                    <div class="flex items-center justify-between pb-2 border-b border-gray-200/70">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="text-lg shrink-0">{{ $module['icon'] }}</span>
                                            <div class="min-w-0">
                                                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wider truncate">{{ $module['name'] }}</h3>
                                                <span class="text-[10px] font-semibold text-gray-500">
                                                    {{ $checkedCount }}/{{ count($module['permissions']) }} aktif
                                                </span>
                                            </div>
                                        </div>

                                        @if($selectedRole->name !== 'Manager')
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                @if($allChecked)
                                                    <button type="button" wire:click="toggleModulePermissions('{{ $modKey }}', false)" class="text-[10px] font-bold text-gray-500 hover:text-rose-600 px-2 py-0.5 rounded hover:bg-rose-50 transition cursor-pointer">
                                                        Batal Semua
                                                    </button>
                                                @else
                                                    <button type="button" wire:click="toggleModulePermissions('{{ $modKey }}', true)" class="text-[10px] font-bold text-emerald-700 hover:text-emerald-900 px-2 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 transition cursor-pointer">
                                                        + Centang Modul
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Permissions Items (Clean Row Layout) -->
                                    <div class="space-y-1.5">
                                        @foreach($module['permissions'] as $p)
                                            @php
                                                $isChecked = in_array($p['code'], $selectedPermissions);
                                                $isView = ($p['type'] === 'view');
                                                $permSearchBlob = strtolower($p['name'] . ' ' . $p['code']);
                                            @endphp
                                            <label class="flex items-start gap-2.5 p-2 rounded-lg border transition cursor-pointer select-none {{ $isChecked ? ($isView ? 'bg-blue-50/70 border-blue-200 text-blue-950 ring-1 ring-blue-200/60' : 'bg-emerald-50/70 border-emerald-300 text-emerald-950 ring-1 ring-emerald-300/60') : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50' }}"
                                                   x-show="(filterType === 'all' || '{{ $p['type'] }}' === filterType) && (searchPermission === '' || '{{ $permSearchBlob }}'.includes(searchPermission.toLowerCase()))">
                                                <input type="checkbox"
                                                       value="{{ $p['code'] }}"
                                                       wire:model="selectedPermissions"
                                                       {{ $selectedRole->name === 'Manager' ? 'disabled checked' : '' }}
                                                       class="mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 shrink-0">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between gap-1.5">
                                                        <span class="font-bold text-[11px] leading-snug">{{ $p['name'] }}</span>
                                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase tracking-wider shrink-0 {{ $isView ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                            {{ $isView ? '👁️ Lihat' : '⚙️ Kelola' }}
                                                        </span>
                                                    </div>
                                                    <span class="text-[10px] text-gray-400 font-mono block mt-0.5">{{ $p['code'] }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Bottom Action Bar -->
                    @if($selectedRole->name !== 'Manager')
                        <div class="px-5 py-4 bg-gray-50/80 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <span class="text-xs text-gray-500">
                                Perubahan hak akses akan segera berlaku untuk seluruh pengguna dengan role <strong>{{ $selectedRole->name }}</strong>.
                            </span>
                            <button type="button" wire:click="savePermissions" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Hak Akses "{{ $selectedRole->name }}"</span>
                            </button>
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>

    <!-- MODAL 1: Tambah Role Baru -->
    @if($showCreateRoleModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs animate-in fade-in duration-150">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-gray-100">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>➕</span>
                        <span>Tambah Role Baru</span>
                    </h3>
                    <button wire:click="$set('showCreateRoleModal', false)" class="text-gray-400 hover:text-gray-600 text-sm cursor-pointer">✕</button>
                </div>

                <form wire:submit.prevent="createRole" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Nama Role Baru <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="newRoleName" placeholder="cth: Supervisor, Accounting, F&amp;B Staff" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500" required autofocus>
                        @error('newRoleName') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        <p class="text-[11px] text-gray-400 mt-1">Nama role baru tidak boleh sama dengan role yang sudah ada.</p>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t">
                        <button type="button" wire:click="$set('showCreateRoleModal', false)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                            <span>Simpan Role</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: Edit Nama Role Kustom -->
    @if($showEditRoleModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs animate-in fade-in duration-150">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-gray-100">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>✏️</span>
                        <span>Ubah Nama Role</span>
                    </h3>
                    <button wire:click="$set('showEditRoleModal', false)" class="text-gray-400 hover:text-gray-600 text-sm cursor-pointer">✕</button>
                </div>

                <form wire:submit.prevent="updateRole" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Nama Role <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="editRoleName" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500" required autofocus>
                        @error('editRoleName') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t">
                        <button type="button" wire:click="$set('showEditRoleModal', false)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                            <span>Perbarui Nama</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
