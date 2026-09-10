<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Kelola Pengguna Sistem</h1>
            <p class="text-sm text-slate-400 mt-1">Daftar akun Administrator dan Operator Perangkat Daerah</p>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-600 hover:to-teal-700 transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah User Baru
        </button>
    </div>

    {{-- Search --}}
    <div class="mb-4 max-w-sm">
        <input wire:model.live.debounce.300ms="search" type="text"
            class="w-full rounded-lg border border-slate-600 bg-slate-800 text-sm text-slate-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            placeholder="Cari nama, email, atau nama dinas...">
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-slate-700/50 bg-slate-800/50 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700/50">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase">Nama Pengguna</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase">Email</th>
                    <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase">Role</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase">Perangkat Daerah</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/30">
                @forelse($users as $u)
                    <tr class="hover:bg-slate-700/20 transition-colors">
                        <td class="px-5 py-4 text-white font-medium flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-300">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <span>{{ $u->name }}</span>
                        </td>
                        <td class="px-5 py-4 text-slate-300">{{ $u->email }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($u->isAdmin())
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    Administrator
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    Operator Dinas
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-400 text-xs">
                            @if($u->nama_dinas)
                                <span class="text-slate-300">{{ $u->nama_dinas }}</span>
                                @if($u->alias)
                                    <span class="text-slate-600 ml-1">({{ $u->alias }})</span>
                                @endif
                            @else
                                <span class="text-slate-600">{{ $u->isAdmin() ? 'Semua Perangkat Daerah' : '-' }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right space-x-2">
                            <button wire:click="openEditModal({{ $u->id }})" class="text-emerald-400 hover:text-emerald-300 text-xs font-medium cursor-pointer">
                                Edit
                            </button>
                            @if($u->id !== auth()->id())
                                <button wire:click="deleteUser({{ $u->id }})" wire:confirm="Hapus pengguna ini?" class="text-red-400 hover:text-red-300 text-xs font-medium cursor-pointer">
                                    Hapus
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-500 text-sm">
                            Tidak ada data pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Create / Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative bg-slate-800 border border-slate-700/50 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-base font-semibold text-white">
                    {{ $editingId ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}
                </h3>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input wire:model="name" type="text" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-3 py-2 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Budi Santoso">
                        @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Email <span class="text-red-400">*</span></label>
                        <input wire:model="email" type="email" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-3 py-2 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="budi@dinas.go.id">
                        @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Password {{ $editingId ? '(Kosongkan jika tidak diubah)' : '*' }}</label>
                        <input wire:model="password" type="password" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-3 py-2 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="••••••••">
                        @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Role <span class="text-red-400">*</span></label>
                        <select wire:model.live="role_id" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-3 py-2 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->label }} ({{ $role->name }})</option>
                            @endforeach
                        </select>
                        @error('role_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    @php
                        $chosenRole = $roles->firstWhere('id', $role_id);
                    @endphp

                    @if($chosenRole && $chosenRole->name === 'operator')
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">
                                Nama Perangkat Daerah <span class="text-red-400">*</span>
                            </label>
                            <input wire:model="nama_dinas" type="text"
                                class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-3 py-2 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="Dinas Komunikasi dan Informatika">
                            @error('nama_dinas') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">
                                Singkatan / Alias
                            </label>
                            <input wire:model="alias" type="text"
                                class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-3 py-2 text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="Diskominfo" maxlength="50">
                            @error('alias') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 rounded-lg border border-slate-600 text-sm text-slate-300 hover:bg-slate-700 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-sm font-semibold text-white cursor-pointer">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
