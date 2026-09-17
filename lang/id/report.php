<?php

return [
    'transaction' => [
        'index' => [
            '_title' => 'Transaksi',
            '_subtitle' => 'Berikut adalah transaksi yang telah dilakukan oleh pasien setelah layanan gigi.',
            'actions' => [
                'add' => 'Tambah Transaksi',
                'delete' => 'Hapus',
                'print' => 'Cetak',
                'find' => 'Cari Transaksi',
            ],
            'labels' => [
                'patient_keyword' => 'Kata Kunci',
                'date_start' => 'Mulai',
                'date_end' => 'Hingga Tanggal',
            ],
            'placeholders' => [
                'type_here' => 'Ketik kata kunci di sini...',
            ],
            'helpers' => [
                'patient_keyword' => 'Ketik ID Pasien, Nama Pasien, atau Nomor Telepon Pasien',
            ],
            'table' => [
                'patient' => 'Pasien',
                'doctor' => 'Dokter',
                'service' => 'Layanan',
                'pricing' => 'Harga',
                'payment-method' => 'Metode Pembayaran',
                'date' => 'Tanggal Transaksi',
                'patient_id' => 'ID Pasien: :id',
                'patient_phone' => 'Telepon: :phone',
                'doctor_nipp' => 'NIPP. :nipp',
            ],
        ],
        'detail' => [
            'data' => [
                'patient' => 'Pasien',
                'doctor' => 'Dokter',
                'service' => [
                    'title' => 'Layanan',
                    'discount' => 'Diskon',
                ],
                'schedule' => [
                    'recomendation' => 'Rekomendasi Jadwal Berikutnya',
                    'reschedule' => 'Jadwal Ulang',
                ],
                'pricing' => [
                    'current_payment' => 'Pembayaran Saat Ini',
                    'installments' => 'Angsuran',
                    'title' => 'Harga',
                    'total' => 'Total',
                    'discount' => 'Diskon',
                    'grand_total' => 'Total Keseluruhan',
                ],
                'voucher' => [
                    'title' => 'Kode Voucher',
                    'code' => 'Kode Voucher',
                    'discount' => 'Diskon',
                ],
            ],
            'button' => [
                'cancel' => 'Batalkan Transaksi Ini',
                'submit_cancel' => 'Kirim Pembatalan',
            ],
            'helper' => [
                'canceled' => 'Transaksi ini telah dibatalkan pada.',
                'reason' => 'karena alasan: ',
                'cancel' => 'Hanya kesalahan penulisan? Anda dapat membatalkannya!',
            ],
        ],
    ],
    'income' => [
        'index' => [
            '_title' => 'Laporan Pendapatan',
            '_subtitle' => 'Rekapitulasi pendapatan bulanan. Berdasarkan kinerja dokter, layanan, dll.',
            'lookup' => [
                '_helper' => 'Jika rentang waktu tidak diatur, halaman ini akan menampilkan transaksi bulan ini.',
                'since' => 'Mulai Tanggal',
                'until' => 'Hingga Tanggal',
                'doctor' => [
                    'title' => 'Dokter',
                    'helper' => 'Biarkan kosong untuk menampilkan semua dokter.',
                ],
            ],
            'helper' => [
                'empty' => 'Tidak ada data laporan!',
            ],
        ],
        'table' => [
            'service' => 'Layanan',
            'price' => 'Harga',
            'discount' => 'Diskon',
            'income' => 'Pendapatan',
        ],
    ],
];
