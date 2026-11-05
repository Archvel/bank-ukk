<x-admin-layout title="Manajemen Pengguna" breadcrumb="Pengguna">
    <div x-data="{ 
        createModalOpen: false, 
        editModalOpen: false,
        currentUser: { id: null, name: '', username: '', email: '', phone_number: '', role: 'teller', is_active: 1 },
        openEdit(user) {
            this.currentUser = Object.assign({}, user);
            this.editModalOpen = true;
        }
    }">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna Sistem</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola kredensial, hak akses, dan status operasional staf internal Administrator & Piket Teller.</p>
            </div>
            <div>
                <button type="button" 
                        @click="createModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm shadow-emerald-900/20 transition">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    <span>+ Tambah Pengguna Baru</span>
                </button>
            </div>
        </div>

        <!-- 3 Quick Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Administrator</span>
                        <div class="text-2xl font-bold font-mono text-slate-900 mt-1">{{ $adminCount }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">admin_panel_settings</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Piket Teller</span>
                        <div class="text-2xl font-bold font-mono text-slate-900 mt-1">{{ $tellerCount }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">badge</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Akun Aktif</span>
                        <div class="text-2xl font-bold font-mono text-emerald-600 mt-1">{{ $activeCount }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">check_circle</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6">
            <form method="GET" action="{{ url('/admin/users') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <!-- Role Filter -->
                    <select name="role" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Role</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="teller" {{ request('role') === 'teller' ? 'selected' : '' }}>Piket Teller</option>
                        <option value="nasabah" {{ request('role') === 'nasabah' ? 'selected' : '' }}>Nasabah</option>
                    </select>

                    @if(request()->filled('role') || request()->filled('search'))
                        <a href="{{ url('/admin/users') }}" class="text-xs text-slate-500 hover:text-red-600 font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">close</span> Reset
                        </a>
                    @endif
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-80">
                    <span class="material-symbols-outlined text-slate-400 absolute left-3 top-2.5 text-[18px]">search</span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama, email, username..." 
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border-slate-200 bg-slate-50 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4">Username & Kontak</th>
                            <th class="py-3 px-4">Role Akses</th>
                            <th class="py-3 px-4 text-center">Status Akun</th>
                            <th class="py-3 px-4">Terdaftar</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-mono text-slate-700 font-semibold">{{ $user->username ?? '-' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $user->phone_number ?? '-' }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    @if($user->role->value === 'admin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Administrator
                                        </span>
                                    @elseif($user->role->value === 'teller')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Piket Teller
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            Nasabah Siswa
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Diblokir
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-500 font-mono">
                                    {{ $user->created_at?->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                @click="openEdit({{ json_encode($user) }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition"
                                                title="Edit Pengguna">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>

                                        <!-- Toggle Status Button -->
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-amber-700 hover:bg-amber-50 transition"
                                                        title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                                        onclick="return confirm('Apakah Anda yakin ingin mengubah status aktif akun {{ $user->name }}?')">
                                                    <span class="material-symbols-outlined text-[18px]">{{ $user->is_active ? 'block' : 'lock_open' }}</span>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Delete Button -->
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ url('/admin/users/'.$user->id) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-red-700 hover:bg-red-50 transition"
                                                        title="Hapus Pengguna"
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?')">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data pengguna yang sesuai dengan filter pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Tambah Pengguna Baru -->
        <div x-show="createModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200"
                 @click.outside="createModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">person_add</span>
                        </div>
                        <h3 class="font-bold text-base text-slate-900">Tambah Pengguna Baru</h3>
                    </div>
                    <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ url('/admin/users') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required placeholder="Contoh: Budi Pratama, S.Kom" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Resmi *</label>
                            <input type="email" name="email" required placeholder="budi@bankmini.test" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username (Opsional)</label>
                            <input type="text" name="username" placeholder="budipratama" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Peran / Role *</label>
                            <select name="role" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="teller">Piket Teller (Operasional Kas)</option>
                                <option value="admin">Administrator (Super Admin)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. Handphone / WhatsApp</label>
                            <input type="text" name="phone_number" placeholder="081234567890" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Awal *</label>
                        <input type="password" name="password" required placeholder="Minimal 8 karakter" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm transition">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Pengguna -->
        <div x-show="editModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200"
                 @click.outside="editModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </div>
                        <h3 class="font-bold text-base text-slate-900">Edit Data Pengguna</h3>
                    </div>
                    <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form :action="'{{ url('admin/users') }}/' + currentUser.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" x-model="currentUser.name" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Resmi *</label>
                            <input type="email" name="email" x-model="currentUser.email" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username</label>
                            <input type="text" name="username" x-model="currentUser.username" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Peran / Role *</label>
                            <select name="role" x-model="currentUser.role" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="teller">Piket Teller</option>
                                <option value="admin">Administrator</option>
                                <option value="nasabah">Nasabah Siswa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. Handphone</label>
                            <input type="text" name="phone_number" x-model="currentUser.phone_number" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ganti Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" placeholder="Masukkan password baru..." class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm transition">
                            Perbarui Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
