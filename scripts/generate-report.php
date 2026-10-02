<?php

/**
 * Generator Laporan Word: Sistem KataGigi Dignify.
 * Jalankan: php scripts/generate-report.php
 * Output:   docs/Laporan-Sistem-KataGigi.docx
 */

require __DIR__.'/report-lib.php';

$doc = new DocxBuilder;
$doc->footerPageNumber('KataGigi Dignify — Laporan Sistem & Kepatuhan SatuSehat');

// ================================================================== COVER
$doc->cover(
    'Laporan Sistem Informasi Manajemen Klinik Gigi',
    'Peran Pengguna, Penjelasan Fitur, Kepatuhan SATUSEHAT, dan Rencana Pembangunan',
    'KataGigi Dignify — Banjarmasin & Banjarbaru',
    '22 September 2026',
    [
        'Dokumen' => 'Laporan Sistem v1.0',
        'Cabang' => 'KataGigi Banjarmasin & KataGigi Banjarbaru',
        'Basis Regulasi' => 'Permenkes No. 24/2022 & Standar SATUSEHAT (HL7 FHIR R4)',
    ]
);

// ============================================================ 1. PENDAHULUAN
$doc->h1('1. Pendahuluan');

$doc->h2('1.1 Tujuan Dokumen');
$doc->p([
    ['Dokumen ini menyajikan gambaran lengkap aplikasi Rekam Medis Elektronik (RME) KataGigi Dignify dalam format laporan yang mudah dibaca. ', []],
    ['Isinya mencakup tiga hal utama: ', []],
    ['(1) ', ['bold' => true]],
    ['penjelasan setiap peran pengguna beserta wewenang dan alur kerjanya, ', []],
    ['(2) ', ['bold' => true]],
    ['penjelasan setiap fitur yang tersedia di sistem, serta ', []],
    ['(3) ', ['bold' => true]],
    ['penilaian pemenuhan syarat integrasi SATUSEHAT beserta rencana pembangunan untuk menutup celah yang masih ada.', []],
], ['justify' => true]);

$doc->h2('1.2 Gambaran Umum Sistem');
$doc->p('KataGigi Dignify adalah sistem informasi manajemen klinik gigi berbasis web yang melayani dua cabang operasional, yaitu KataGigi Banjarmasin dan KataGigi Banjarbaru. Sistem mencakup seluruh siklus layanan pasien: mulai dari pendaftaran dan reservasi, antrian kunjungan, pemeriksaan klinis dengan odontogram digital, resep obat, pemakaian bahan habis pakai (BHP), penagihan di kasir, hingga pelaporan keuangan dan integrasi data ke platform SATUSEHAT Kementerian Kesehatan.');
$doc->p('Seluruh akses pengguna diatur secara ketat melalui sistem Role-Based Access Control (RBAC) dengan empat peran resmi. Setiap aktivitas penting terhadap data medis tercatat pada jejak audit (audit log) sesuai amanat Permenkes No. 24/2022 tentang Rekam Medis Elektronik.');

$doc->h2('1.3 Ringkasan Teknis');
$doc->table(
    ['Aspek', 'Keterangan'],
    [
        ['Teknologi inti', ['Laravel (PHP) + Livewire + Tailwind CSS', []]],
        ['Basis data', ['MySQL/MariaDB dengan UUID primary key', []]],
        ['Kontrol akses', ['Spatie Laravel Permission (roles & permissions)', []]],
        ['Bahasa antarmuka', ['Indonesia & Inggris (dapat diganti kapan saja)', []]],
        ['Tampilan', ['Mode terang & gelap (dark mode) mengikuti preferensi pengguna', []]],
        ['Integrasi', ['SATUSEHAT (HL7 FHIR R4), WhatsApp Official', []]],
        ['Jejak audit', ['Tabel audit_logs untuk aktivitas data medis', []]],
    ],
    [3, 7]
);

$doc->pageBreak();

// ==================================================== 2. PERAN PENGGUNA
$doc->h1('2. Peran Pengguna dan Wewenang');

$doc->h2('2.1 Prinsip Pembagian Peran');
$doc->p('Sistem membedakan empat peran pengguna. Prinsip dasarnya: setiap peran hanya melihat menu dan data yang ia butuhkan untuk bekerja. Menu di bilah samping muncul otomatis sesuai hak akses (permission) yang dimiliki, dan tombol aksi yang tidak diizinkan tidak akan tampil maupun bisa dieksekusi dari sisi server.');
$doc->table(
    ['Peran', 'Posisi dalam Klinik', 'Cakupan Utama'],
    [
        ['Manajemen', ['Pimpinan / pemilik klinik', []], ['Seluruh modul + fungsi persetujuan (approval)', []]],
        ['Admin', ['Front office + kasir (merangkap)', []], ['Reservasi, pendaftaran, penjadwalan, kasir, stok, WhatsApp', []]],
        ['Dokter', ['Nakes pemberi layanan', []], ['Pemeriksaan, rekam medis, odontogram, resep, penandatanganan', []]],
        ['Perawat', ['Nakes pendukung / front office', []], ['Data pasien, antrian, pembagian kasir bila ditugaskan', []]],
    ],
    [2.2, 3, 5]
);
$doc->note('Akun demonstrasi telah disiapkan untuk masing-masing peran: manajemen@gmail.com, admin@gmail.com, doctor@gmail.com, dan nurse@gmail.com. Pembagian kasir merangkap admin/perawat mengikuti keputusan desain D-04 sesuai praktik klinik.');

// ---------------------------------------------------------------- manajemen
$doc->h2('2.2 Manajemen — Pimpinan Klinik');
$doc->h3('Deskripsi');
$doc->p('Manajemen adalah peran dengan wewenang paling luas. Peran ini mewarisi seluruh hak akses modul dan menjadi satu-satunya pihak yang dapat melakukan persetujuan atas tindakan-tindakan sensitif, mengelola kredensial integrasi, serta melihat jejak audit formal. Dalam praktiknya, manajemen memantau kesehatan bisnis dari dashboard dan menangani hal-hal yang memerlukan keputusan pimpinan.');

$doc->h3('Wewenang Khusus (tidak dimiliki peran lain)');
$doc->bullets([
    'Menyetujui atau menolak usulan pembatalan nota transaksi (approve cancellation).',
    'Mengelola pencairan jasa medis dokter (manage doctor fee).',
    'Mengelola data cabang, termasuk kredensial SATUSEHAT per cabang (manage branch, manage satusehat).',
    'Mengelola master kode diagnosis ICD-10 / ICD-9-CM / SNOMED (manage diagnosis code).',
    'Melihat jejak audit formal seluruh sistem (read audit log) — hanya manajemen.',
    'Mengelola akun karyawan: dokter, perawat, dan admin.',
]);

$doc->h3('Wewenang Umum yang Dimiliki');
$doc->bullets([
    'Seluruh modul klinis dan administratif (baca dan tulis).',
    'Seluruh laporan keuangan: pendapatan, jasa dokter, laporan keuangan gabungan.',
    'Dashboard manajemen: total pendapatan klinik, tren analisis, metode pembayaran, pasien berdata belum lengkap, nota terbaru, dan antrian hari ini.',
]);

$doc->h3('Alur Kerja Harian');
$doc->numbered([
    'Membuka dashboard manajemen untuk memantau pendapatan, volume kunjungan, dan kelengkapan data pasien.',
    'Memeriksa daftar usulan pembatalan nota dari admin, lalu menyetujui atau menolaknya.',
    'Memverifikasi daftar pencairan jasa medis dokter sebelum diproses ke finance.',
    'Meninjau jejak audit bila ada indikasi perubahan data yang perlu dilacak.',
    'Mengelola kredensial SATUSEHAT dan konfigurasi cabang bila ada perubahan.',
]);

// ---------------------------------------------------------------- admin
$doc->h2('2.3 Admin — Operasional & Kasir');
$doc->h3('Deskripsi');
$doc->p('Admin adalah motor penggerak operasional harian di resepsionis dan kasir. Peran ini menerima reservasi, mendata pasien baru, mencatat transaksi, mengelola stok inventaris dan beban operasional, serta mengoperasikan pengingat WhatsApp. Satu-satunya keterbatasan penting: admin tidak dapat langsung membatalkan nota — ia hanya bisa mengusulkannya dan menunggu persetujuan manajemen.');

$doc->h3('Yang Bisa Dilakukan');
$doc->bullets([
    'Reservasi & jadwal: membuat, membaca, dan memperbarui jadwal dokter serta reservasi pasien (tanpa hak hapus).',
    'Pasien: mendata pasien baru dan memperbarui data pasien (tanpa hak hapus).',
    'Kunjungan: membuka kunjungan (check-in) dan memperbaruinya, namun tidak dapat menandatangani/mengunci rekam medis (tanpa sign visit).',
    'Kasir: membuat, membaca, dan memperbarui nota transaksi serta tagihan.',
    'Pembatalan: mengusulkan pembatalan nota untuk disetujui manajemen (request cancellation).',
    'Inventaris: mengelola stok obat dan BHP (tambah, opname, penyesuaian).',
    'Beban operasional: mencatat dan mengelola pengeluaran klinik.',
    'WhatsApp: mengelola template dan konfigurasi WhatsApp Official.',
    'Rekam medis: hanya dapat membaca, tidak dapat membuat atau mengubah isi klinis.',
    'Laporan: melihat transaksi dan omzet (read turnover).',
]);

$doc->h3('Alur Kerja Harian');
$doc->numbered([
    'Menerima pasien datang atau telepon reservasi; membuat/mengubah reservasi di sistem.',
    'Mendata pasien baru bila belum terdaftar (NIK, kontak, alamat, persetujuan SATUSEHAT).',
    'Melakukan check-in pasien: kunjungan dibuat dan masuk ke daftar antrian klinis.',
    'Menyiapkan nota berdasarkan tindakan dan resep yang diinput dokter setelah pemeriksaan.',
    'Menerima pembayaran, menerbitkan nota lunas dan kwitansi.',
    'Bila pasien meminta pembatalan nota: mengajukan usulan pembatalan, lalu memberi tahu manajemen.',
    'Melakukan penyesuaian stok inventaris setelah penerimaan barang atau opname.',
]);

// ---------------------------------------------------------------- dokter
$doc->h2('2.4 Dokter — Pemberi Layanan Klinis');
$doc->h3('Deskripsi');
$doc->p('Dokter adalah pemilik konten klinis. Seluruh nilai medis — anamnesis, odontogram, diagnosis, tindakan, resep — hanya boleh ditulis dan diubah oleh dokter. Momen paling penting adalah penandatanganan kunjungan (sign visit): begitu ditandatangani, rekam medis kunjungan tersebut terkunci sebagai dokumen legal. Dokter juga dapat melihat pendapatan jasanya sendiri, namun tidak melihat keuangan klinik secara keseluruhan.');

$doc->h3('Yang Bisa Dilakukan');
$doc->bullets([
    'Rekam medis: membuat, membaca, memperbarui, dan menghapus rekam medis (full access klinis).',
    'Kunjungan: membuka, memperbarui, dan menandatangani kunjungan (sign visit) untuk mengunci rekam medis.',
    'Odontogram: mencatat kondisi 52 gigi (FDI) per permukaan: karies, amputasi, gigi hilang, crown, dll.',
    'Diagnosis & tindakan: memilih kode ICD-10 dan ICD-9-CM dari master yang tersedia.',
    'Resep: menulis e-resep dengan kode KFA untuk obat dan BHP.',
    'Pasien: membaca data pasien (tanpa membuat/mengubah identitas administratif).',
    'Laporan: melihat transaksi terkait jasanya dan omzet pribadi (read turnover, read transaction).',
    'Inventaris & beban operasional: hanya membaca (read inventory, read expense).',
]);

$doc->h3('Alur Kerja Harian');
$doc->numbered([
    'Membuka Workspace untuk melihat daftar pasien yang menunggu pemeriksaan.',
    'Memanggil pasien; status kunjungan berubah mengikuti alur REGISTERED - WAITING - CALLED - IN TREATMENT - DONE - SIGNED.',
    'Melakukan anamnesis dan mengisi pemeriksaan (termasuk tekanan darah).',
    'Menggambar odontogram: menandai kondisi gigi per permukaan sesuai temuan.',
    'Menetapkan diagnosis (ICD-10) dan tindakan yang dilakukan (ICD-9-CM).',
    'Menulis resep digital; stok BHP yang terpakai pada tindakan dipotong otomatis oleh sistem.',
    'Memeriksa dan menandatangani kunjungan; rekam medis terkunci dan siap disinkronkan ke SATUSEHAT.',
    'Memantau ringkasan praktik pribadi di dashboard: pendapatan jasa, kunjungan, dan rekam medis terbaru.',
]);

// ---------------------------------------------------------------- perawat
$doc->h2('2.5 Perawat — Pendukung Klinis & Front Office');
$doc->h3('Deskripsi');
$doc->p('Perawat berada di garis depan melayani pasien: mendata, mengatur jadwal, dan mengelola antrian. Berbeda dengan admin, perawat boleh menghapus reservasi (misalnya saat pasien membatalkan langsung), namun sama seperti admin ia tidak boleh menghapus data pasien. Bila ditugaskan, perawat juga dapat merangkap tugas kasir. Dashboard perawat sengaja tanpa angka finansial — fokusnya operasional harian.');

$doc->h3('Yang Bisa Dilakukan');
$doc->bullets([
    'Pasien: mendata, membaca, dan memperbarui data pasien (tanpa hak hapus).',
    'Reservasi: membuat, membaca, memperbarui, dan menghapus reservasi (hak hapus reservasi hanya milik perawat selain manajemen).',
    'Kunjungan: membuka dan memperbarui kunjungan (tanpa sign visit).',
    'Rekam medis: hanya membaca.',
    'Kasir (bila ditugaskan): membuat, membaca, dan memperbarui nota transaksi.',
    'Jadwal: membaca jadwal dokter.',
    'Inventaris & beban operasional: hanya membaca.',
]);

$doc->h3('Alur Kerja Harian');
$doc->numbered([
    'Menerima dan mendata pasien baru atau memperbarui data pasien lama.',
    'Membuat reservasi dan memastikan pasien mendapat slot jadwal dokter yang sesuai.',
    'Pada hari layanan: melakukan check-in kunjungan dan memantau daftar antrian di dashboard.',
    'Menjawab permintaan pembatalan reservasi; bila perlu menghapus reservasi langsung.',
    'Bila ditugaskan sebagai kasir: menerima pembayaran dan menerbitkan nota.',
    'Membantu dokter memastikan kelengkapan data pasien sebelum pemeriksaan.',
]);

$doc->h2('2.6 Ringkasan Matriks Hak Akses');
$doc->p('Tabel berikut merangkum hak akses utama per peran. Tanda centang berarti peran tersebut memiliki hak atas aksi tersebut.');
$doc->table(
    ['Modul / Aksi', 'Manajemen', 'Admin', 'Dokter', 'Perawat'],
    [
        ['Kelola akun karyawan', [['√', ['align' => 'center']], ['', ''], ['', ''], ['', '']]],
        ['Jadwal: buat/ubah', [['√', ['align' => 'center']], ['√', ['align' => 'center']], ['baca', ['align' => 'center']], ['baca', ['align' => 'center']]]],
        ['Reservasi: buat/ubah', [['√', ['align' => 'center']], ['√', ['align' => 'center']], ['baca', ['align' => 'center']], ['√ + hapus', ['align' => 'center']]]],
        ['Data pasien: buat/ubah', [['√', ['align' => 'center']], ['√', ['align' => 'center']], ['baca', ['align' => 'center']], ['√', ['align' => 'center']]]],
        ['Rekam medis: tulis', [['√', ['align' => 'center']], ['baca', ['align' => 'center']], ['√ + hapus', ['align' => 'center']], ['baca', ['align' => 'center']]]],
        ['Kunjungan: tandatangani', [['√', ['align' => 'center']], ['', ''], ['√', ['align' => 'center']], ['', '']]],
        ['Kasir: buat nota', [['√', ['align' => 'center']], ['√', ['align' => 'center']], ['baca', ['align' => 'center']], ['√ (rangkap)', ['align' => 'center']]]],
        ['Usul pembatalan nota', [['√', ['align' => 'center']], ['√', ['align' => 'center']], ['', ''], ['', '']]],
        ['Setujui pembatalan nota', [['√', ['align' => 'center']], ['', ''], ['', ''], ['', '']]],
        ['Inventaris: kelola', [['√', ['align' => 'center']], ['√', ['align' => 'center']], ['baca', ['align' => 'center']], ['baca', ['align' => 'center']]]],
        ['Pencairan jasa dokter', [['√', ['align' => 'center']], ['', ''], ['', ''], ['', '']]],
        ['SATUSEHAT & cabang', [['√', ['align' => 'center']], ['', ''], ['', ''], ['', '']]],
        ['Jejak audit', [['√', ['align' => 'center']], ['', ''], ['', ''], ['', '']]],
    ],
    [4, 1.4, 1.4, 1.6, 1.8]
);

$doc->pageBreak();

// ==================================================== 3. PENJELASAN FITUR
$doc->h1('3. Penjelasan Fitur per Modul');

$doc->h2('3.1 Alur Layanan Pasien End-to-End');
$doc->p('Sebelum masuk ke daftar fitur, berikut alur utama pasien dari awal hingga akhir, agar posisi setiap fitur mudah dipahami:');
$doc->numbered([
    'Reservasi — admin/perawat membuat reservasi pasien pada jadwal dokter yang tersedia.',
    'Check-in — saat pasien datang, kunjungan (visit) dibuka dan pasien masuk antrian klinis.',
    'Antrian & Workspace — dokter memanggil pasien dari Workspace; status kunjungan berubah bertahap hingga selesai ditangani.',
    'Pemeriksaan — dokter mengisi anamnesis, pemeriksaan umum (termasuk tekanan darah), odontogram, diagnosis ICD-10, dan tindakan ICD-9-CM.',
    'Resep & BHP — dokter menulis resep digital; bahan habis pakai yang dipakai pada tindakan dipotong otomatis dari stok.',
    'Penandatanganan — dokter menandatangani kunjungan sehingga rekam medis terkunci sebagai dokumen legal.',
    'Kasir — admin/perawat menerbitkan nota (jasa dokter + tindakan + obat + BHP), menerima pembayaran, dan mencetak kwitansi.',
    'Pasca layanan — sistem mengirim pengingat WhatsApp untuk kontrol berikutnya (H-1), data dikirim ke SATUSEHAT, dan seluruh aktivitas tercatat di laporan serta jejak audit.',
]);

$doc->h2('3.2 Daftar Modul dan Fungsinya');
$doc->table(
    ['Modul', 'Fungsi Utama', 'Pengguna Utama'],
    [
        ['Dashboard', ['Ringkasan satu halaman dengan widget yang menyesuaikan peran (lihat 3.3).', []], ['Semua peran', []]],
        ['Jadwal & Reservasi', ['Kelola jadwal dokter per cabang; reservasi pasien; tampilan kalender.', []], ['Admin, Perawat', []]],
        ['Layanan & Tarif', ['Master kategori dan layanan/tindakan beserta tarif dan komisi dokter.', []], ['Manajemen', []]],
        ['Antrian Kunjungan', ['Daftar kunjungan harian dengan status klinis yang berurutan.', []], ['Semua nakes', []]],
        ['Workspace', ['Meja kerja dokter: pasien yang sedang/akan ditangani hari ini.', []], ['Dokter', []]],
        ['Rekam Medis', ['Dokumen klinis pasien: anamnesis, pemeriksaan, odontogram, diagnosis, tindakan.', []], ['Dokter', []]],
        ['Odontogram', ['Peta 52 gigi notasi FDI dengan pencatatan per permukaan gigi.', []], ['Dokter', []]],
        ['Resep Digital', ['E-resep obat dengan kode KFA; terhubung ke inventaris.', []], ['Dokter', []]],
        ['Inventaris & BHP', ['Stok obat/bahan per batch; pemotongan stok otomatis saat tindakan.', []], ['Admin, Manajemen', []]],
        ['Kasir & Tagihan', ['Nota transaksi, pembayaran cicilan/lunas, kwitansi, usulan pembatalan.', []], ['Admin, Perawat, Manajemen', []]],
        ['Laporan Keuangan', ['Omzet, metode pembayaran, pencairan jasa dokter, laporan gabungan.', []], ['Manajemen, Admin', []]],
        ['Beban Operasional', ['Pencatatan pengeluaran klinik non-medis.', []], ['Admin, Manajemen', []]],
        ['Multi-Cabang', ['Pemisahan data dan konfigurasi Banjarmasin & Banjarbaru.', []], ['Manajemen', []]],
        ['WhatsApp Official', ['Pengingat janji temu otomatis H-1 via WhatsApp; template pesan.', []], ['Admin, Manajemen', []]],
        ['SATUSEHAT', ['Integrasi FHIR ke platform Kemenkes (rincian di Bab 4).', []], ['Manajemen', []]],
        ['Jejak Audit', ['Log otomatis siapa-apa-kapan atas perubahan data medis.', []], ['Manajemen', []]],
        ['Bahasa & Tampilan', ['Pengalih bahasa ID/EN dan mode terang/gelap untuk semua halaman.', []], ['Semua peran', []]],
    ],
    [2.4, 5.6, 2.4]
);

$doc->h2('3.3 Dashboard Satu Halaman dengan Widget per Peran');
$doc->p('Dashboard dibangun dengan prinsip satu halaman untuk semua peran: yang membedakan hanyalah widget apa yang boleh tampil dan lingkup datanya. Ini menjaga pengalaman konsisten sekaligus menjamin keamanan data — dokter hanya melihat angka miliknya, dan perawat sama sekali tidak disajikan angka finansial.');
$doc->table(
    ['Widget Dashboard', 'Manajemen', 'Dokter', 'Perawat'],
    [
        ['Kartu KPI pendapatan', [['Klinik', ['align' => 'center']], ['Pribadi', ['align' => 'center']], ['', '']]],
        ['Kartu KPI pasien/kunjungan', [['√', ['align' => 'center']], ['√', ['align' => 'center']], ['√ (tanpa finansial)', ['align' => 'center']]]],
        ['Grafik tren pendapatan', [['Klinik', ['align' => 'center']], ['Pribadi', ['align' => 'center']], ['', '']]],
        ['Antrian hari ini', [['√', ['align' => 'center']], ['√', ['align' => 'center']], ['√', ['align' => 'center']]]],
        ['Metode pembayaran bulan ini', [['√', ['align' => 'center']], ['', ''], ['', '']]],
        ['Pasien berdata belum lengkap', [['√', ['align' => 'center']], ['', ''], ['', '']]],
        ['Aktivitas terbaru', [['Nota', ['align' => 'center']], ['Rekam medis', ['align' => 'center']], ['', '']]],
    ],
    [4, 1.8, 1.8, 2.4]
);

$doc->h2('3.4 Fitur Klinis Inti');
$doc->h3('Odontogram Digital');
$doc->p('Odontogram adalah jantung rekam medis gigi. Sistem menggambar 52 gigi (32 permanen + 20 susu) dengan notasi FDI, dan setiap gigi dicatat per permukaan (oklusal, mesial, distal, labial/bukal, lingual/palatal) atau sebagai gigi utuh. Kondisi yang didukung antara lain sehat, karies, amputasi/akar sisa, gigi hilang, gigi tertanam (impacted), crown, jembatan, dan pencabutan indikasi. Kondisi gigi juga dipetakan ke kode SNOMED CT untuk keperluan SATUSEHAT.');
$doc->h3('Kode Diagnosis dan Tindakan Terstandar');
$doc->p('Diagnosis wajib memakai ICD-10 dan tindakan memakai ICD-9-CM dari master kode yang tersedia di sistem (dapat dikelola manajemen). Validasi di sisi layanan memastikan hanya kode dengan sistem yang benar yang dikirim ke SATUSEHAT; kode lain dilewati dengan alasan yang tercatat di log sinkronisasi.');
$doc->h3('Pemotongan Stok BHP Otomatis');
$doc->p('Ketika tindakan membutuhkan bahan habis pakai, sistem memotong stok dari batch inventaris secara otomatis (FIFO per tanggal kedaluwarsa) melalui StockService. Ini mencegah selisih stok dan memastikan biaya BHP ikut tercatat pada nota pasien.');

$doc->h3('Bahasa dan Mode Tampilan');
$doc->p('Seluruh antarmuka mendukung dua bahasa (Indonesia dan Inggris) yang dapat diganti dari bilah atas, dan preferensi bahasa diingat per pengguna. Mode terang/gelap juga tersedia dengan palet gelap kontras tinggi sehingga nyaman dipakai pada layar receptionist maupun ruang operasi yang redup.');

$doc->pageBreak();

// ==================================================== 4. SATUSEHAT
$doc->h1('4. Kepatuhan SATUSEHAT');

$doc->h2('4.1 Apa Itu SATUSEHAT dan Apa Syaratnya');
$doc->p([
    ['SATUSEHAT adalah platform pertukaran data kesehatan nasional milik Kementerian Kesehatan. Fasyankes — termasuk klinik gigi — wajib mengirim data layanan pasien ke platform ini dalam format standar internasional ', []],
    ['HL7 FHIR R4', ['bold' => true]],
    ['. Data tidak dikirim sebagai dokumen bebas, melainkan sebagai ', []],
    ['resource', ['italic' => true]],
    [' terstandar: identitas pasien sebagai Patient, kunjungan sebagai Encounter, diagnosis sebagai Condition, tindakan sebagai Procedure, dan seterusnya.', []],
]);
$doc->p('Syarat umum bagi fasyankes untuk berintegrasi:');
$doc->bullets([
    'Registrasi fasyankes dan penerbitan kredensial API (Client ID & Client Secret) per sarana di portal SATUSEHAT.',
    'Penggunaan lingkungan pengembangan (sandbox) terlebih dahulu sebelum produksi.',
    'Identitas pasien nasional: NIK 16 digit sebagai kunci; sistem menerima/registrasi pasien hingga mendapat IHS ID (Indeks Health SatuSehat) sebagai ID pasien nasional.',
    'Persetujuan pasien (consent) atas pembagian data — selaras dengan UU Perlindungan Data Pribadi.',
    'Kode terminologi terstandar: ICD-10 (diagnosis), ICD-9-CM (tindakan), LOINC (observasi), SNOMED CT (klinis), dan KFA (obat & alat kesehatan).',
    'Kode wilayah Kemendagri pada alamat pasien agar Address FHIR valid secara nasional.',
]);

$doc->h2('4.2 Status Implementasi per Resource FHIR');
$doc->p('Tabel berikut merangkum resource FHIR yang disyaratkan untuk layanan rawat jalan klinik gigi, beserta status implementasi saat ini.');
$doc->table(
    ['Resource FHIR', 'Fungsi', 'Status', 'Keterangan'],
    [
        ['Patient', ['Demografi & identitas pasien (NIK, IHS).', []], ['SUDAH', ['bold' => true, 'color' => '047857']], ['Tervalidasi NIK 16 digit; IHS tersimpan balik ke pasien (MPI).', []]],
        ['Encounter', ['Kunjungan/rawat jalan.', []], ['SUDAH', ['bold' => true, 'color' => '047857']], ['Terhubung pasien, dokter, dan waktu kunjungan.', []]],
        ['Condition', ['Diagnosis ICD-10.', []], ['SUDAH', ['bold' => true, 'color' => '047857']], ['Hanya kode ICD-10 valid yang dikirim; lainnya dilewati dengan log.', []]],
        ['Procedure', ['Tindakan ICD-9-CM.', []], ['SUDAH', ['bold' => true, 'color' => '047857']], ['Termasuk pemetaan gigi FDI ke SNOMED CT.', []]],
        ['Observation', ['Pengukuran klinis (LOINC).', []], ['PARSIAL', ['bold' => true, 'color' => 'B45309']], ['Baru tekanan darah (8480-6/8462-4). Nadi, suhu, respirasi, kehamilan belum.', []]],
        ['MedicationRequest', ['Resep obat.', []], ['BELUM', ['bold' => true, 'color' => 'B91C1C']], ['Resep & kode KFA sudah ada di sistem lokal, belum dikirim ke SATUSEHAT.', []]],
        ['Practitioner', ['Dokter/nakes (IHS).', []], ['PARSIAL', ['bold' => true, 'color' => 'B45309']], ['IHS dokter dipakai sebagai referensi, belum ada sinkronisasi/lookup otomatis.', []]],
        ['Organization / Location', ['Identitas sarana & lokasi layanan.', []], ['BELUM', ['bold' => true, 'color' => 'B91C1C']], ['Belum dikirim; dibutuhkan saat onboarding produksi.', []]],
        ['DiagnosticReport / Media', ['Radiologi & foto intraoral.', []], ['BELUM', ['bold' => true, 'color' => 'B91C1C']], ['Modul radiologi (unggah foto rontgen) belum dibangun.', []]],
    ],
    [2.2, 2.2, 1.2, 5.2]
);

$doc->h2('4.3 Yang Sudah Diimplementasikan');
$doc->p('Bagian ini merinci kemampuan integrasi yang telah berjalan di sistem:');
$doc->bullets([
    ['Autentikasi OAuth2 — ', ['bold' => true]],
    ['sistem mengelola token akses SATUSEHAT secara otomatis (permintaan token, penyimpanan, dan pembaruan saat kedaluwarsa).', []],
]);
$doc->bullets([
    ['Gerbang persetujuan pasien (consent) — ', ['bold' => true]],
    ['pasien yang belum memberi persetujuan SATUSEHAT dilewati seluruhnya dan halnya tercatat di log. Ini kepatuhan UU PDP sekaligus syarat etik pengiriman data.', []],
]);
$doc->bullets([
    ['Master Patient Index (MPI) — ', ['bold' => true]],
    ['ID IHS yang dikembalikan server SATUSEHAT disimpan balik ke data pasien, sehingga sinkronisasi berikutnya selalu merujuk pasien nasional yang sama dan tidak menciptakan pasien ganda.', []],
]);
$doc->bullets([
    ['Pipeline per kunjungan — ', ['bold' => true]],
    ['satu klik sinkronisasi mengirim Patient, Encounter, seluruh Condition, seluruh Procedure, dan Observation secara berurutan dengan ketergantungan yang benar (Encounter butuh IHS pasien, dsb.).', []],
]);
$doc->bullets([
    ['Terminologi terstandar — ', ['bold' => true]],
    ['ICD-10, ICD-9-CM, LOINC (tekanan darah), dan SNOMED CT (kode gigi FDI) sudah terpetakan di payload FHIR.', []],
]);
$doc->bullets([
    ['Log sinkronisasi lengkap — ', ['bold' => true]],
    ['setiap pengiriman tercatat di tabel satusehat_sync_logs: status (PENDING/SUCCESS/FAILED/SKIPPED), isi JSON permintaan, respons server, jumlah percobaan, dan pesan galat.', []],
]);
$doc->bullets([
    ['Konfigurasi lingkungan — ', ['bold' => true]],
    ['mendukung endpoint sandbox pengembangan maupun produksi melalui konfigurasi.', []],
]);

$doc->h2('4.4 Yang Belum Diimplementasikan');
$doc->p('Celah yang masih ada, diurutkan dari yang paling berdampak ke kepatuhan:');
$doc->numbered([
    ['Kredensial per cabang — ', ['bold' => true]],
    ['saat ini kredensial API disimpan global di file konfigurasi (.env). Dengan dua cabang yang masing-masing punya Client ID dan Organization ID sendiri, kredensial wajib dipindahkan ke database per cabang.', []],
]);
$doc->numbered([
    ['Antrian & percobaan ulang (queue & retry) — ', ['bold' => true]],
    ['sinkronisasi masih berjalan serempak saat tombol ditekan. Bila API SATUSEHAT lambat/gagal, pengiriman perlu pindah ke background job dengan percobaan ulang otomatis dan tombol Retry manual per log gagal.', []],
]);
$doc->numbered([
    ['Tanda vital lengkap — ', ['bold' => true]],
    ['nadi, suhu, laju pernapasan, dan status kehamilan belum dicatat (baru tekanan darah). Resource Observation untuk keempatnya disyaratkan pada rawat jalan.', []],
]);
$doc->numbered([
    ['MedicationRequest — ', ['bold' => true]],
    ['resep digital dengan kode KFA sudah ada, tetapi belum dikirim sebagai resource FHIR.', []],
]);
$doc->numbered([
    ['Kode wilayah Kemendagri — ', ['bold' => true]],
    ['alamat pasien masih teks bebas; Address FHIR yang valid membutuhkan master wilayah (provinsi/kabupaten/kecamatan/kelurahan) berkode nasional.', []],
]);
$doc->numbered([
    ['Onboarding Practitioner / Organization / Location — ', ['bold' => true]],
    ['pencarian IHS dokter via NIK dan pendaftaran identitas sarana per cabang belum otomatis.', []],
]);
$doc->numbered([
    ['Radiologi & foto intraoral — ', ['bold' => true]],
    ['modul unggah rontgen/foto dan DiagnosticReport-nya belum dibangun (opsional untuk klinik, namun bernilai tinggi untuk RME gigi).', []],
]);
$doc->numbered([
    ['Pemeriksaan dental standar Kemenkes — ', ['bold' => true]],
    ['indeks OHI-S (debris & kalkulus), DMF-T/def-t, oklusi, torus, palatum, dan diastema belum menjadi form tersendiri; odontogram kondisional sudah ada.', []],
]);
$doc->numbered([
    ['Kepatuhan Permenkes 24/2022 — ', ['bold' => true]],
    ['jejak audit sudah berjalan, namun informed consent digital (tanda tangan layar sentuh) dan addendum koreksi rekam medis tanpa penghapusan permanen belum ada.', []],
]);

$doc->note('Catatan: beberapa item (radiologi, master kamus lokal) bersifat bernilai tambah, namun kredensial per cabang, queue & retry, informed consent, dan addendum adalah prasyarat kepatuhan yang wajib ditutup sebelum go-live produksi SATUSEHAT.', 'FEF2F2', 'B91C1C');

$doc->pageBreak();

// ==================================================== 5. RENCANA PEMBANGUNAN
$doc->h1('5. Rencana Pembangunan');

$doc->p('Rencana disusun dalam empat fase dengan urutan berdasarkan tingkat risiko regulasi dan kesiapan operasional. Setiap fase memiliki definisi selesai (definition of done) yang sama: migrasi database aman, uji otomatis lolos, terjemahan dua bahasa lengkap, tampilan gelap mengikuti tema, tercatat di jejak audit, dan build aset sukses.');

$doc->h2('5.1 Peta Fase');
$doc->table(
    ['Fase', 'Fokus', 'Item Utama', 'Prioritas'],
    [
        ['Fase 1', ['Kepatuhan legal (Permenkes 24/2022)', []], ['Informed consent + tanda tangan digital di layar; addendum koreksi rekam medis tanpa hard delete.', []], ['Tertinggi', ['bold' => true, 'color' => 'B91C1C']]],
        ['Fase 2', ['Infrastruktur SATUSEHAT multi-cabang', []], ['Kredensial per cabang; antrian + retry; onboarding Practitioner/Organization/Location.', []], ['Tinggi', ['bold' => true, 'color' => 'B45309']]],
        ['Fase 3', ['Kelengkapan klinis', []], ['Tanda vital lengkap + status kehamilan; pemeriksaan dental OHI-S & DMF-T; modul radiologi + DiagnosticReport.', []], ['Sedang', []]],
        ['Fase 4', ['Master kamus & administratif', []], ['Master wilayah Kemendagri; master KFA/LOINC/SNOMED lokal; MedicationRequest; template surat & penjamin.', []], ['Sedang', []]],
    ],
    [1.2, 2.6, 5.4, 1.4]
);

$doc->h2('5.2 Rincian per Fase');

$doc->h3('Fase 1 — Kepatuhan Legal');
$doc->bullets([
    'Membuat master template persetujuan tindakan (cabut gigi, PSA, bedah mulut) dan form tanda tangan digital pasien/wali di layar sentuh.',
    'Dokumen consent tersimpan beserta waktu, IP, dan perangkat; dapat dicetak/diunduh sebagai PDF bertanda tangan.',
    'Membuat tabel addendum rekam medis: setiap perubahan data medis tercatat nilai lama, nilai baru, alasan, dan pelakunya — data asli tidak pernah dihapus permanen.',
    'Menonaktifkan hard delete pada data medis (kunjungan, odontogram, diagnosis, tindakan) dan menggantinya dengan penandaan terhapus.',
]);
$doc->h3('Fase 2 — Infrastruktur SATUSEHAT');
$doc->bullets([
    'Menyimpan Client ID/Secret, Organization ID, dan Location ID per cabang (terenkripsi) beserta halaman kelola untuk manajemen.',
    'Memindahkan sinkronisasi ke background job dengan percobaan ulang otomatis (termasuk menghadapi batas laju API), tombol Retry manual, dan notifikasi hasil.',
    'Sinkronisasi otomatis Practitioner (IHS dokter dari NIK) dan identitas sarana per cabang.',
]);
$doc->h3('Fase 3 — Kelengkapan Klinis');
$doc->bullets([
    'Menambahkan nadi, suhu, respirasi, dan status kehamilan pada pemeriksaan; mengirimnya sebagai Observation LOINC.',
    'Form pemeriksaan dental lengkap: OHI-S (debris/kalkulus), DMF-T/def-t, oklusi, torus, palatum, diastema.',
    'Modul radiologi: unggah foto periapikal/panoramik/intraoral, catatan bacaan dokter (DiagnosticReport), dan pengiriman FHIR.',
]);
$doc->h3('Fase 4 — Master Kamus & Administratif');
$doc->bullets([
    'Master wilayah Kemendagri dengan dropdown berantai pada data pasien; Address FHIR memakai kode resmi.',
    'Master KFA lokal untuk obat & BHP; pengiriman MedicationRequest untuk resep.',
    'Template surat sakit, surat berobat, dan rujukan; master penjamin/asuransi pada pendaftaran.',
]);

$doc->h2('5.3 Urutan Pengerjaan yang Disarankan');
$doc->numbered([
    'Fase 1 (legal) — menutup risiko regulasi terbesar sebelum integrasi produksi.',
    'Fase 2 (infrastruktur SATUSEHAT) — prasyarat teknis go-live produksi dengan dua cabang.',
    'Fase 3 (klinis) — memperkaya data yang dikirim ke SATUSEHAT sekaligus alur kerja dokter.',
    'Fase 4 (master & administratif) — menyempurnakan validasi data dan layanan administratif pasien.',
]);

// ==================================================== 6. PENUTUP
$doc->h1('6. Penutup');
$doc->p('Secara keseluruhan, fondasi sistem KataGigi Dignify telah kuat: alur layanan pasien dari reservasi hingga kasir berjalan utuh, kontrol akses per peran ditegakkan di seluruh modul, dan inti integrasi SATUSEHAT (identitas pasien, kunjungan, diagnosis, tindakan) sudah berfungsi dengan persetujuan pasien dan jejak audit. Pekerjaan selanjutnya terfokus pada penutupan celah kepatuhan — informed consent, addendum, kredensial per cabang, dan antrian pengiriman — kemudian memperkaya data klinis (tanda vital lengkap, pemeriksaan dental, radiologi) hingga seluruh syarat SATUSEHAT terpenuhi.');
$doc->p('Dokumen ini akan diperbarui setiap kali fase pada rencana pembangunan selesai, sehingga selalu mencerminkan kondisi terkini sistem.');

$doc->save(__DIR__.'/../docs/Laporan-Sistem-KataGigi.docx');
echo "OK: docs/Laporan-Sistem-KataGigi.docx\n";
