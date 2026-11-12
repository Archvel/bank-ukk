<x-admin-layout title="Data Calon Penabung" breadcrumb="Calon Penabung">
    <div x-data="{ 
        createModalOpen: false, 
        editModalOpen: false,
        importModalOpen: false,
        promoteModalOpen: false,
        promoteMode: 'all',
        sourceLevel: 'X',
        sourceClass: '',
        targetClass: '',
        academicYear: '',
        currentMember: { id: null, student_name: '', class_name: '', major: '', graduation_year: '', email: '', username: '', phone_number: '' },
        openEdit(member) {
            this.currentMember = {
                id: member.id,
                student_name: member.student_name,
                class_name: member.class_name || '',
                major: member.major || '',
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
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Data Calon Penabung</h1>
                <p class="text-sm text-slate-500 mt-0.5">Pendaftaran dan manajemen siswa pra-nasabah sebelum aktivasi
                    buku rekening di loket kasir teller.</p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <button type="button" @click="promoteModalOpen = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white shadow-sm shadow-amber-900/20 transition cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">school</span>
                    <span>Naik Kelas Siswa</span>
                </button>
                <button type="button" @click="importModalOpen = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-emerald-700 shadow-xs transition cursor-pointer">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">upload_file</span>
                    <span>Import File Excel</span>
                </button>
                <button type="button" @click="createModalOpen = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm shadow-emerald-900/20 transition cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    <span>+ Registrasi Calon Penabung</span>
                </button>
            </div>
        </div>

        @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                <div class="flex items-center gap-2 font-bold mb-2">
                    <span class="material-symbols-outlined text-[18px] text-amber-600">warning</span>
                    <span>Catatan Baris yang Dilewati / Perlu Perhatian ({{ count(session('import_errors')) }} data):</span>
                </div>
                <ul class="list-disc list-inside space-y-1 max-h-40 overflow-y-auto pl-1 text-[11px] font-mono">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Banner Info Aktivasi Otomatis Loket Teller -->
        <div
            class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200/80 shadow-xs flex items-start gap-3.5">
            <div
                class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <span class="material-symbols-outlined text-2xl">info</span>
            </div>
            <div class="text-xs text-slate-700 leading-relaxed">
                <p class="font-bold text-emerald-900 text-sm mb-0.5">Alur Aktivasi Buku Rekening:</p>
                <p>Siswa yang didaftarkan pada halaman ini akan berstatus <span
                        class="font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">Inactive
                        (Pra-Nasabah)</span>. Nomor rekening resmi <span class="font-bold text-emerald-800">Saver</span>
                    akan <strong>diterbitkan secara otomatis oleh sistem loket</strong> begitu siswa melakukan setoran
                    tunai pertama minimal <strong>Rp 10.000</strong> ke petugas Teller. Tidak diperlukan pembuatan
                    rekening manual oleh Administrator.</p>
            </div>
        </div>

        <!-- 3 KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total
                            Terdaftar</span>
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
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Menunggu Setoran
                            Awal</span>
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
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sudah Aktif Jadi
                            Saver</span>
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
            <form method="GET" action="{{ url('/admin/members') }}"
                class="flex flex-col lg:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()"
                        class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Status</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Menunggu
                            Aktivasi (Inactive)</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif (Saver Terbit)
                        </option>
                    </select>

                    <!-- Jurusan Filter -->
                    @if(isset($majors) && count($majors) > 0)
                        <select name="major" onchange="this.form.submit()"
                            class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua Jurusan</option>
                            @foreach($majors as $m)
                                <option value="{{ $m }}" {{ request('major') == $m ? 'selected' : '' }}>Jurusan {{ $m }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Kelas Filter -->
                    @if(isset($classes) && count($classes) > 0)
                        <select name="class" onchange="this.form.submit()"
                            class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $c)
                                <option value="{{ $c }}" {{ request('class') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Batch / Year Filter -->
                    <select name="year" onchange="this.form.submit()"
                        class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Angkatan</option>
                        @foreach($batches as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>Angkatan {{ $year }}
                            </option>
                        @endforeach
                    </select>

                    @if(request()->filled('status') || request()->filled('major') || request()->filled('class') || request()->filled('year') || request()->filled('search'))
                        <a href="{{ url('/admin/members') }}"
                            class="text-xs text-slate-500 hover:text-red-600 font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">close</span> Reset
                        </a>
                    @endif
                </div>

                <!-- Search Input -->
                <div class="relative w-full lg:w-80">
                    <span
                        class="material-symbols-outlined text-slate-400 absolute left-3 top-2.5 text-[18px]">search</span>
                    <input type="text" name="search" value="{{ request('search') }}"
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
                        <tr
                            class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
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
                                        <div
                                            class="w-9 h-9 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center font-bold text-emerald-800 text-xs shrink-0">
                                            {{ strtoupper(substr($member->student_name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $member->student_name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if($member->nis)
                                                    <span
                                                        class="text-[11px] font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded font-semibold">NIS:
                                                        {{ $member->nis }}</span>
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
                                    <p class="text-[11px] text-slate-400 font-mono">User:
                                        {{ $member->user?->username ?? '-' }}
                                    </p>
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        {{ $member->academic_year ?? ('Tahun ' . $member->graduation_year) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($member->account_status->value === 'active')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Rekening Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Menunggu
                                            Setoran Awal
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
                                        <!-- 1-Click Naik Kelas Button -->
                                        <form method="POST" action="{{ route('admin.members.promote-single', $member->id) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-amber-700 hover:bg-amber-50 transition cursor-pointer"
                                                title="Naikkan Kelas Siswa (1-Klik)"
                                                onclick="return confirm('Konfirmasi naik kelas untuk siswa {{ $member->student_name }} dari {{ $member->class_name ?: '(Belum ada kelas)' }}?')">
                                                <span class="material-symbols-outlined text-[18px]">school</span>
                                            </button>
                                        </form>

                                        <!-- Edit Button -->
                                        <button type="button" @click="openEdit({{ json_encode($member) }})"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition"
                                            title="Edit Calon Penabung">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>

                                        <!-- Delete Button -->
                                        <form method="POST" action="{{ url('/admin/members/' . $member->id) }}"
                                            class="inline">
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
                                <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data calon penabung yang
                                    sesuai.</td>
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

                <form method="POST" action="{{ url('/admin/members') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Siswa Lengkap *</label>
                        <input type="text" name="student_name" required placeholder="Contoh: Muhammad Farhan"
                            class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Siswa / Wali *</label>
                            <input type="email" name="email" required placeholder="farhan@smkn1.sch.id"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Angkatan / Lulus
                                *</label>
                            <input type="text" name="graduation_year" required placeholder="Contoh: 2026" maxlength="4"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas (Rombel)</label>
                            <input type="text" name="class_name" placeholder="Contoh: X BD, X RPL 1"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jurusan</label>
                            <input type="text" name="major" placeholder="Contoh: BD, RPL, AK"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username Siswa
                                (Opsional)</label>
                            <input type="text" name="username" placeholder="farhan2026"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp Siswa / Orang
                                Tua</label>
                            <input type="text" name="phone_number" placeholder="08xxxxxxxxxx"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password Akun (Default:
                            password123)</label>
                        <input type="password" name="password"
                            placeholder="Biarkan kosong untuk password default 'password123'"
                            class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="createModalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm transition">
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
                        <input type="text" name="student_name" x-model="currentMember.student_name" required
                            class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Resmi *</label>
                            <input type="email" name="email" x-model="currentMember.email" required
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Angkatan *</label>
                            <input type="text" name="graduation_year" x-model="currentMember.graduation_year" required
                                maxlength="4"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas (Rombel)</label>
                            <input type="text" name="class_name" x-model="currentMember.class_name" placeholder="Contoh: XI BD, XII RPL 1"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jurusan</label>
                            <input type="text" name="major" x-model="currentMember.major" placeholder="Contoh: BD, RPL, AK"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username Siswa</label>
                            <input type="text" name="username" x-model="currentMember.username"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp</label>
                            <input type="text" name="phone_number" x-model="currentMember.phone_number"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="editModalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm transition">
                            Perbarui Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Import Calon Penabung via Excel / CSV -->
        <div x-show="importModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200"
                @click.outside="importModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">upload_file</span>
                        </div>
                        <h3 class="font-bold text-base text-slate-900">Import Calon Penabung via Excel</h3>
                    </div>
                    <button type="button" @click="importModalOpen = false"
                        class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Template Download Card -->
                <div
                    class="mb-5 p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-800">Gunakan Format Standar</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Unduh contoh template kolom untuk mempermudah
                            pengisian data.</p>
                    </div>
                    <a href="{{ url('/admin/members/template-import') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300 transition shadow-xs">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        <span>Unduh Template</span>
                    </a>
                </div>

                <form method="POST" action="{{ url('/admin/members/import') }}" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Berkas Excel / CSV
                            *</label>
                        <div
                            class="relative border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-emerald-500 transition bg-slate-50/50">
                            <span class="material-symbols-outlined text-3xl text-slate-400 mb-1">table_view</span>
                            <p class="text-xs text-slate-600 font-medium">Klik untuk memilih berkas atau seret ke sini
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Format: <strong>.xlsx, .csv</strong>
                                (Maks. 10MB)</p>
                            <input type="file" name="file" required accept=".xlsx,.csv"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        </div>
                    </div>

                    <!-- Column Mapping Hints -->
                    <div
                        class="text-[11px] text-slate-500 bg-emerald-50/60 p-3 rounded-xl border border-emerald-100 leading-relaxed">
                        <p class="font-bold text-emerald-900 mb-1">Panduan Kolom File Excel:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-slate-600">
                            <li>Kolom Wajib: <strong>NIS</strong> dan <strong>Nama Siswa</strong>.</li>
                            <li>Kolom Opsional: <strong>Kelas</strong>, <strong>Jurusan</strong>,
                                <strong>Angkatan</strong>, <strong>No Absen</strong>, <strong>No WhatsApp</strong>,
                                <strong>Email</strong>.
                            </li>
                            <li>Setiap siswa otomatis dibuatkan akun dengan password default <span
                                    class="font-mono text-emerald-800 font-semibold">password123</span>.</li>
                            <li>Status awal adalah <strong>Inactive</strong> sampai siswa menyetor uang pertama di loket
                                teller.</li>
                        </ul>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="importModalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm transition cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">cloud_upload</span>
                            <span>Unggah & Mulai Import</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Kenaikan Kelas Siswa (Bulk / Targeted Class Promotion) -->
        <div x-show="promoteModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200"
                @click.outside="promoteModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/80 flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">school</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Kenaikan Kelas Siswa</h3>
                            <p class="text-[11px] text-slate-500">Proses kenaikan kelas siswa untuk tahun ajaran baru</p>
                        </div>
                    </div>
                    <button type="button" @click="promoteModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.members.promote') }}" class="space-y-4">
                    @csrf

                    <!-- Pilihan Skema Kenaikan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Skema Kenaikan Kelas</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <label class="border rounded-xl p-3 flex flex-col items-center text-center cursor-pointer transition"
                                   :class="promoteMode === 'all' ? 'border-amber-500 bg-amber-50/60 text-amber-900 font-bold shadow-2xs' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                <input type="radio" name="mode" value="all" x-model="promoteMode" class="sr-only">
                                <span class="material-symbols-outlined text-xl mb-1 text-amber-600">published_with_changes</span>
                                <span class="text-xs">Semua Siswa</span>
                                <span class="text-[10px] text-slate-400 font-normal mt-0.5">X&rarr;XI, XI&rarr;XII</span>
                            </label>

                            <label class="border rounded-xl p-3 flex flex-col items-center text-center cursor-pointer transition"
                                   :class="promoteMode === 'level' ? 'border-amber-500 bg-amber-50/60 text-amber-900 font-bold shadow-2xs' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                <input type="radio" name="mode" value="level" x-model="promoteMode" class="sr-only">
                                <span class="material-symbols-outlined text-xl mb-1 text-amber-600">stairs</span>
                                <span class="text-xs">Per Tingkat</span>
                                <span class="text-[10px] text-slate-400 font-normal mt-0.5">Pilih X / XI / XII</span>
                            </label>

                            <label class="border rounded-xl p-3 flex flex-col items-center text-center cursor-pointer transition"
                                   :class="promoteMode === 'class' ? 'border-amber-500 bg-amber-50/60 text-amber-900 font-bold shadow-2xs' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                <input type="radio" name="mode" value="class" x-model="promoteMode" class="sr-only">
                                <span class="material-symbols-outlined text-xl mb-1 text-amber-600">groups</span>
                                <span class="text-xs">Per Rombel</span>
                                <span class="text-[10px] text-slate-400 font-normal mt-0.5">Kelas spesifik</span>
                            </label>
                        </div>
                    </div>

                    <!-- Keterangan Mode: All -->
                    <div x-show="promoteMode === 'all'" class="p-3.5 rounded-xl bg-amber-50 border border-amber-200/90 text-xs text-amber-950 space-y-1.5">
                        <p class="font-bold flex items-center gap-1.5 text-amber-900">
                            <span class="material-symbols-outlined text-[16px] text-amber-600">info</span>
                            Aturan Kenaikan Kelas Otomatis:
                        </p>
                        <ul class="list-disc list-inside space-y-1 text-[11px] text-amber-800 pl-1">
                            <li>Kelas <strong>X</strong> naik menjadi Kelas <strong>XI</strong> (contoh: <code>X BD</code> &rarr; <code>XI BD</code>)</li>
                            <li>Kelas <strong>XI</strong> naik menjadi Kelas <strong>XII</strong> (contoh: <code>XI BD</code> &rarr; <code>XII BD</code>)</li>
                            <li>Kelas <strong>XII</strong> dinyatakan lulus menjadi <strong>Alumni</strong> (contoh: <code>XII BD</code> &rarr; <code>Alumni BD</code>)</li>
                        </ul>
                    </div>

                    <!-- Keterangan Mode: Level -->
                    <div x-show="promoteMode === 'level'" class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-700">Pilih Tingkat Kelas Asal</label>
                        <select name="source_level" x-model="sourceLevel"
                            class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-amber-500 focus:ring-amber-500 font-medium">
                            <option value="X">Tingkat X &rarr; Naik ke Tingkat XI (Semua rombel X)</option>
                            <option value="XI">Tingkat XI &rarr; Naik ke Tingkat XII (Semua rombel XI)</option>
                            <option value="XII">Tingkat XII &rarr; Dinyatakan Lulus / Alumni</option>
                        </select>
                    </div>

                    <!-- Keterangan Mode: Class -->
                    <div x-show="promoteMode === 'class'" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas Asal *</label>
                            <select name="source_class" x-model="sourceClass"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-amber-500 focus:ring-amber-500 font-medium">
                                <option value="">-- Pilih Kelas Asal --</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kelas Baru (Tujuan)</label>
                            <input type="text" name="target_class" x-model="targetClass"
                                placeholder="Biarkan kosong untuk otomatis (misal: XI BD)"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-amber-500 focus:ring-amber-500">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Jika dikosongkan, sistem otomatis menaikkan tingkat rombel (X &rarr; XI, XI &rarr; XII).</span>
                        </div>
                    </div>

                    <!-- Input Tambahan: Tahun Pelajaran Baru -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Pelajaran Baru (Opsional)</label>
                        <input type="text" name="academic_year" x-model="academicYear"
                            placeholder="Contoh: 2027 - 2028 (biarkan kosong jika tidak diubah)"
                            class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-amber-500 focus:ring-amber-500 font-mono">
                    </div>

                    <!-- Checkbox Konfirmasi -->
                    <div class="pt-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" required class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-xs text-slate-600 leading-snug">Saya mengonfirmasi bahwa data kenaikan kelas ini sudah benar dan siap diperbarui untuk akun nasabah siswa terpilih.</span>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="promoteModalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold bg-amber-500 text-white hover:bg-amber-600 shadow-sm shadow-amber-900/20 transition cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">school</span>
                            <span>Proses Kenaikan Kelas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>