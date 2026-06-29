<?php
$mockFoundItems = [
    [
        'id' => 1,
        'name' => 'Dompet Hitam',
        'type' => 'non-valuable',
        'desc' => 'Warna Hitam, Ditemukan di Tangga gkb 1...',
        'time' => '2 menit lalu',
        'status' => 'available',
        'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&q=80&w=200&h=200',
        'fullImage' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&q=80&w=800',
        'fullDesc' => 'Dompet berwarna Coklat/Hitam, dengan isi KTP atas nama Zainal Siregar Kapalaud, uang Rp.200.000, STNK sepeda motor Vario Hitam.',
        'location' => 'TANGGA TENGAH LANTAI 2 KE 3, DI GKB 1. (13.00 WIB 19 Mei 2026)'
    ],
    [
        'id' => 2,
        'name' => 'Kacamata',
        'type' => 'non-valuable',
        'desc' => 'Warna, Ditemukan di area tangga',
        'time' => '30 menit lalu',
        'status' => 'available',
        'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&q=80&w=200&h=200',
        'fullImage' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&q=80&w=800',
        'fullDesc' => 'Kacamata Bulat Frame Besi warna Silver, ditemukan di Toilet Cowok GKB 4.',
        'location' => 'Toilet Cowok, Lantai 3 GKB 4 (13.00 WIB 19 Mei 2026)'
    ],
    [
        'id' => 3,
        'name' => 'Jam Tangan Rollex',
        'type' => 'valuable',
        'desc' => 'Warna Silver Titanium, Ditemukan diTang...',
        'time' => '1 Jam lalu',
        'status' => 'available',
        'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&q=80&w=200&h=200',
        'fullImage' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&q=80&w=800',
        'fullDesc' => 'Jam tangan berwarna silver titanium. Ditemukan di tangga lantai 1.',
        'location' => 'Tangga Lantai 1 (10.00 WIB 19 Mei 2026)'
    ],
    [
        'id' => 4,
        'name' => 'HP iPhone 17 Pink',
        'type' => 'valuable',
        'desc' => 'Warna Pink, Ditemukan di area tangga',
        'time' => '30 menit lalu',
        'status' => 'taken',
        'image' => 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?auto=format&fit=crop&q=80&w=200&h=200',
        'fullImage' => 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?auto=format&fit=crop&q=80&w=800',
        'fullDesc' => 'HP iPhone 17 warna pink. Ada casing bening.',
        'location' => 'Area Tangga GKB 2 (09.00 WIB 19 Mei 2026)'
    ]
];

$mockLostReports = [
    [
        'id' => 1,
        'reporter' => 'Rani',
        'text' => 'Yang merasa melihat cermin doraemon, sekitaran daerah toilet gkb 1 lantai 2 silakan h..',
        'verified' => false,
        'fullData' => [
            'name' => 'Indah Sekali Ya MasyaAllah',
            'item' => 'Dompet Putih',
            'location' => 'Stadion UMM',
            'time' => '30 Mei 2026 Minggu, (07.00 WIB)',
            'desc' => 'Disaat saya olahraga di Stadion UMM, saya kehilangan dompet berwarna Putih, dimana saya tidak terasa dompet tersebut jatuh. Sekitar jam 7...',
            'contact' => '089877798308 - Petugas',
            'attachment' => 'NIM Pada KTM 202410370110000'
        ]
    ],
    [
        'id' => 2,
        'reporter' => 'Yanto',
        'text' => 'Yang merasa melihat/ menemukan STNK No N 4711 S, Sepeda BAET Honda. Silak...',
        'verified' => false,
    ],
    [
        'id' => 3,
        'reporter' => 'Raul',
        'text' => 'Yang merasa melihat/menemukan Kunci bertulis Menuju Surga, Hubungi...',
        'verified' => true,
    ]
];
?>
