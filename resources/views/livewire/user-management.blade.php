<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Notifications -->
        @if (session()->has('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">✕</button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-900 cursor-pointer">✕</button>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2.5">
                    <span>👥</span>
                    <span>Manajemen Pengguna & Hak Akses Staf</span>
                </h1>
                <p class="text-sm text-gray-500 mt-1">Kelola data staf operasional hotel, penetapan role (Manager, Resepsionis, Housekeeping), dan keamanan kredensial.</p>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="openCreateModal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Pengguna Baru</span>
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Staf</span>
                <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalUsers }}</p>
                <span class="text-[11px] text-gray-400">Terdaftar di sistem</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Staf Aktif</span>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $activeUsers }}</p>
                <span class="text-[11px] text-emerald-600/80">Bisa login</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Manager</span>
                <p class="text-2xl font-extrabold text-purple-600 mt-1">{{ $managerCount }}</p>
                <span class="text-[11px] text-purple-500">Akses Penuh</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Resepsionis</span>
                <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $receptionistCount }}</p>
                <span class="text-[11px] text-blue-500">Front Desk & Kasir</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Housekeeping</span>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $housekeepingCount }}</p>
                <span class="text-[11px] text-amber-500">Kamar & Kebersihan</span>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row gap-3 justify-between items-center">
            <div class="relative w-full sm:w-80">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, username, email, telp..." class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="flex items-center gap-1.5 w-1/2 sm:w-auto">
                    <span class="text-xs text-gray-500 whitespace-nowrap">Role:</span>
                    <select wire:model.live="roleFilter" class="text-xs py-1.5 rounded-lg border-gray-300 w-full sm:w-auto">
                        <option value="all">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-1.5 w-1/2 sm:w-auto">
                    <span class="text-xs text-gray-500 whitespace-nowrap">Status:</span>
                    <select wire:model.live="statusFilter" class="text-xs py-1.5 rounded-lg border-gray-300 w-full sm:w-auto">
                        <option value="all">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase font-semibold tracking-wider">
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4">Kontak</th>
                            <th class="py-3 px-4">Role Akses</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $u)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 flex items-center gap-1.5">
                                                <span>{{ $u->name }}</span>
                                                @if($u->id === auth()->id())
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">(Anda)</span>
                                                @endif
                                            </p>
                                            <p class="text-[11px] text-gray-400">@<span>{{ $u->username ?? '-' }}</span></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="text-gray-700 font-medium">{{ $u->email }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $u->phone ?: 'Tidak ada no. telp' }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $rName = $u->role_name;
                                        $badgeClass = match($rName) {
                                            'Manager', 'Admin', 'admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'Resepsionis' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'Housekeeping' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            default => 'bg-gray-100 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
                                        {{ $rName }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($u->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Aktif</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Toggle Status Button -->
                                        @if($u->id !== auth()->id())
                                            <button wire:click="toggleStatus({{ $u->id }})" title="{{ $u->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}" class="p-1.5 rounded-lg border text-xs font-medium transition cursor-pointer {{ $u->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                                                @if($u->is_active)
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </button>
                                        @endif

                                        <!-- Edit Profile Button -->
                                        <button wire:click="openEditModal({{ $u->id }})" title="Edit Profil & Role" class="p-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <!-- Reset Password Button -->
                                        <button wire:click="openResetPasswordModal({{ $u->id }})" title="Reset Password Staf" class="p-1.5 rounded-lg border border-blue-200 text-blue-700 hover:bg-blue-50 transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        </button>

                                        <!-- Delete Button -->
                                        @if($u->id !== auth()->id())
                                            <button wire:click="deleteUser({{ $u->id }})" wire:confirm="Apakah Anda yakin ingin menghapus akun staf {{ $u->name }}? Aksi ini tidak dapat dibatalkan jika belum memiliki riwayat." title="Hapus Staf" class="p-1.5 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">
                                    Tidak ada pengguna ditemukan dengan filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL 1: Tambah Pengguna Baru -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-gray-100 animate-in fade-in duration-150">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>➕</span>
                        <span>Tambah Staf / Pengguna Baru</span>
                    </h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600 text-sm cursor-pointer">✕</button>
                </div>

                <form wire:submit.prevent="createUser" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Nama Lengkap Staf <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="name" placeholder="cth: Ahmad Fauzi" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        @error('name') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Username Login <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="username" placeholder="cth: fauzi_fo" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            @error('username') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Role & Hak Akses <span class="text-rose-500">*</span></label>
                            <select wire:model="role" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="email" placeholder="cth: fauzi@insituhotel.id" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            @error('email') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Nomor WhatsApp / Telp</label>
                            <input type="text" wire:model="phone" placeholder="cth: 08123456789" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            @error('phone') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 border-t">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Password Awal <span class="text-rose-500">*</span></label>
                            <input type="password" wire:model="password" placeholder="Minimal 8 karakter" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            @error('password') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Konfirmasi Password <span class="text-rose-500">*</span></label>
                            <input type="password" wire:model="password_confirmation" placeholder="Ulangi password" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" wire:model="is_active" id="create_is_active" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="create_is_active" class="font-medium text-gray-700 cursor-pointer">Langsung aktifkan akun staf ini</label>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-4 border-t">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold shadow-sm transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Pengguna</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: Edit Profil Pengguna & Role -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-gray-100 animate-in fade-in duration-150">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>✏️</span>
                        <span>Edit Profil & Role Staf</span>
                    </h3>
                    <button wire:click="$set('showEditModal', false)" class="text-gray-400 hover:text-gray-600 text-sm cursor-pointer">✕</button>
                </div>

                <form wire:submit.prevent="updateUser" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Nama Lengkap Staf <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="name" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        @error('name') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Username Login <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="username" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            @error('username') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Role & Hak Akses <span class="text-rose-500">*</span></label>
                            <select wire:model="role" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="email" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            @error('email') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Nomor WhatsApp / Telp</label>
                            <input type="text" wire:model="phone" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            @error('phone') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" wire:model="is_active" id="edit_is_active" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" {{ $selectedUserId === auth()->id() ? 'disabled' : '' }}>
                        <label for="edit_is_active" class="font-medium text-gray-700 cursor-pointer">Status Akun Aktif (Bisa Login)</label>
                        @error('is_active') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2.5 pt-4 border-t">
                        <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold shadow-sm transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Perbarui Pengguna</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 3: Reset Password Staf -->
    @if($showResetPasswordModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 border border-gray-100 animate-in fade-in duration-150">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>🔑</span>
                        <span>Reset Password Staf</span>
                    </h3>
                    <button wire:click="$set('showResetPasswordModal', false)" class="text-gray-400 hover:text-gray-600 text-sm cursor-pointer">✕</button>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs">
                    Anda akan mengatur ulang password untuk akun <strong>{{ $name }}</strong>. Staf akan dapat langsung login dengan password baru ini.
                </div>

                <form wire:submit.prevent="resetPassword" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Password Baru <span class="text-rose-500">*</span></label>
                        <input type="password" wire:model="new_password" placeholder="Minimal 8 karakter" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-blue-500 focus:border-blue-500">
                        @error('new_password') <span class="text-rose-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                        <input type="password" wire:model="new_password_confirmation" placeholder="Ulangi password baru" class="w-full rounded-xl border-gray-300 py-2 text-xs focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="flex justify-end gap-2.5 pt-4 border-t">
                        <button type="button" wire:click="$set('showResetPasswordModal', false)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-sm transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Password Baru</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
