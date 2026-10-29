<?php

return [
    'name' => env('ADMIN_NAME', 'Apotek Aja Admin'),
    'email' => env('ADMIN_EMAIL'),
    'password' => env('ADMIN_PASSWORD'),

    'themes' => [
        'rose' => ['name' => 'Merah Apotek Aja', 'description' => 'Warna khas toko, hangat dan percaya diri.', 'color' => '#be123c'],
        'blue' => ['name' => 'Biru Profesional', 'description' => 'Tenang, bersih, dan mudah dibaca.', 'color' => '#1d4ed8'],
        'emerald' => ['name' => 'Hijau Segar', 'description' => 'Nuansa segar untuk ruang kerja Anda.', 'color' => '#047857'],
        'violet' => ['name' => 'Ungu Modern', 'description' => 'Sentuhan kreatif yang tetap elegan.', 'color' => '#6d28d9'],
        'amber' => ['name' => 'Amber Hangat', 'description' => 'Cerah, ramah, dan penuh energi.', 'color' => '#b45309'],
        'slate' => ['name' => 'Slate Minimalis', 'description' => 'Sederhana dengan karakter netral.', 'color' => '#334155'],
    ],
];
