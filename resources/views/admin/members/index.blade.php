<x-admin-layout title="Data Calon Penabung" breadcrumb="Calon Penabung">
    <div x-data="{ 
        createModalOpen: false, 
        editModalOpen: false,
        currentMember: { id: null, student_name: '', graduation_year: '', email: '', username: '', phone_number: '' },
        openEdit(member) {
            this.currentMember = {
                id: member.id,
                student_name: member.student_name,
                graduation_year: member.graduation_year,
                email: member.user ? member.user.email : '',
                username: member.user ? member.user.username : '',
                phone_number: member.user ? member.user.phone_number : ''
            };
            this.editModalOpen = true;
        }
    }">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Data Calon Penabung (Members)</h1>
                <p class="text-sm text-slate-500 mt-0.5">Pendaftaran dan manajemen siswa pra-nasabah sebelum aktivasi buku rekening di loket kasir teller.</p>
            </div>
            <div>
                <button type="button" 
                        @click="createModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm shadow-emerald-900/20 transition">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    <span>+ Registrasi Calon Penabung</span>
                </button>
            </div>
        </div>

        <!-- Banner Info Aktivasi Otomatis Loket Teller -->
        <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200/80 shadow-xs flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <span class="material-symbols-outlined text-2xl">info</span>
            </div>
            <div class="text-xs text-slate-700 leading-relaxed">
                <p class="font-bold text-emerald-900 text-sm mb-0.5">Alur Aktivasi Buku Rekening (Automatic Saver Provisioning):</p>
                <p>Siswa yang didaftarkan pada halaman ini akan berstatus <span class="font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">Inactive (Pra-Nasabah)</span>. Nomor rekening resmi <span class="font-bold text-emerald-800">Saver</span> akan <strong>diterbitkan secara otomatis oleh sistem loket</strong> begitu siswa melakukan setoran tunai pertama minimal <strong>Rp 10.000</strong> ke petugas Teller. Tidak diperlukan pembuatan rekening manual oleh Administrator.</p>
            </div>
        </div>

        <!-- 3 KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Terdaftar</span>
                        <div class="text-2xl font-bold font-mono text-slate-900 mt-1">{{ $totalMembers }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">groups</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Menunggu Setoran Awal</span>
                        <div class="text-2xl font-bold font-mono text-amber-600 mt-1">{{ $inactiveCount }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">hourglass_top</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sudah Aktif Jadi Saver</span>
                        <div class="text-2xl font-bold font-mono text-emerald-600 mt-1">{{ $activeCount }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">verified</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6">
            <form method="GET" action="{{ route('admin.members.index') }}" class="flex flex-col lg:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Status</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Menunggu Aktivasi (Inactive)</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif (Saver Terbit)</option>
                    </select>

                    <!-- Jurusan Filter -->
                    @if(isset($majors) && count($majors) > 0)
                        <select name="major" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua Jurusan</option>
                            @foreach($majors as $m)
                                <option value="{{ $m }}" {{ request('major') == $m ? 'selected' : '' }}>Jurusan {{ $m }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Kelas Filter -->
                    @if(isset($classes) && count($classes) > 0)
                        <select name="class" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $c)
                                <option value="{{ $c }}" {{ request('class') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Batch / Year Filter -->
                    <select name="year" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Angkatan</option>
                        @foreach($batches as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>Angkatan {{ $year }}</option>
                        @endforeach
                    </select>

                    @if(request()->filled('status') || request()->filled('major') || request()->filled('class') || request()->filled('year') || request()->filled('search'))
                        <a href="{{ route('admin.members.index') }}" class="text-xs text-slate-500 hover:text-red-600 font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">close</span> Reset
                        </a>
                    @endif
                </div>

                <!-- Search Input -->
                <div class="relative w-full lg:w-80">
                    <span class="material-symbols-outlined text-slate-400 absolute left-3 top-2.5 text-[18px]">search</span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari NIS, nama, kelas, jurusan..." 
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border-slate-200 bg-slate-50 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </form>
        </div>

        <!-- Members Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Siswa</th>
                            <th class="py-3 px-4">Kelas & Jurusan</th>
                            <th class="py-3 px-4">Akun & Kontak</th>
                            <th class="py-3 px-4">Tahun Pelajaran</th>
                            <th class="py-3 px-4 text-center">Status Aktivasi</th>
                            <th class="py-3 px-4">No. Rekening Saver</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($members as $member)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center font-bold text-emerald-800 text-xs shrink-0">
                                            {{ strtoupper(substr($member->student_name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $member->student_name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if($member->nis)
                                                    <span class="text-[11px] font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded font-semibold">NIS: {{ $member->nis }}</span>
                                                @endif
                                                @if($member->absen)
                                                    <span class="text-[11px] text-slate-400">Absen #{{ $member->absen }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($member->class_name)
                                        <span class="font-bold text-slate-800 text-xs">{{ $member->class_name }}</span>
                                        <p class="text-[11px] text-slate-400 font-semibold">{{ $member->major ?? '-' }}</p>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <p class="text-slate-700 font-mono text-[11px]">{{ $member->user?->email ?? '-' }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">User: {{ $member->user?->username ?? '-' }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        {{ $member->academic_year ?? ('Tahun '.$member->graduation_year) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($member->account_status->value === 'active')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Rekening Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Menunggu Setoran Awal
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold">
                                    @if($member->saver)
                                        <a href="{{ route('admin.savers.index', ['search' => $member->saver->account_number]) }}" 
                                           class="text-[#059669] hover:underline flex items-center gap-1">
                                            <span>{{ $member->saver->account_number }}</span>
                                            <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 font-normal italic">Belum terbit</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                @click="openEdit({{ json_encode($member) }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition"
                                                title="Edit Calon Penabung">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>

                                        <!-- Delete Button -->
                                        <form method="POST" action="{{ route('admin.members.destroy', $member) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-red-700 hover:bg-red-50 transition"
                                                    title="Hapus Member"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data calon penabung {{ $member->student_name }}?')">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data calon penabung yang sesuai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($members->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $members->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Registrasi Calon Penabung Baru -->
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
                        <h3 class="font-bold text-base text-slate-900">Registrasi Calon Penabung Baru</h3>
                    </div>
                    <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.members.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Siswa Lengkap *</label>
                        <input type="text" name="student_name" required placeholder="Contoh: Muhammad Farhan" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Siswa / Wali *</label>
                            <input type="email" name="email" required placeholder="farhan@smkn1.sch.id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Angkatan / Lulus *</label>
                            <input type="text" name="graduation_year" required placeholder="Contoh: 2026" maxlength="4" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username Siswa (Opsional)</label>
                            <input type="text" name="username" placeholder="farhan2026" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp Siswa / Orang Tua</label>
                            <input type="text" name="phone_number" placeholder="08xxxxxxxxxx" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password Akun (Default: password123)</label>
                        <input type="password" name="password" placeholder="Biarkan kosong untuk password default 'password123'" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm transition">
                            Daftarkan Member
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Calon Penabung -->
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
                        <h3 class="font-bold text-base text-slate-900">Edit Data Calon Penabung</h3>
                    </div>
                    <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form :action="'{{ url('admin/members') }}/' + currentMember.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Siswa Lengkap *</label>
                        <input type="text" name="student_name" x-model="currentMember.student_name" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Resmi *</label>
                            <input type="email" name="email" x-model="currentMember.email" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Angkatan *</label>
                            <input type="text" name="graduation_year" x-model="currentMember.graduation_year" required maxlength="4" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username Siswa</label>
                            <input type="text" name="username" x-model="currentMember.username" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp</label>
                            <input type="text" name="phone_number" x-model="currentMember.phone_number" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
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
