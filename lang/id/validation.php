<?php

/*
|--------------------------------------------------------------------------
| Pesan Validasi Bahasa Indonesia
|--------------------------------------------------------------------------
|
| Laravel 12 tidak menyertakan berkas bahasa apa pun. Karena APP_LOCALE dan
| APP_FALLBACK_LOCALE keduanya "id", kunci yang tidak ditemukan akan tampil
| apa adanya kepada pengguna — aparat desa melihat "validation.required"
| alih-alih penjelasan yang bisa dipahami.
|
| Berkas ini menutup celah itu. Pesan khusus per-formulir tetap didefinisikan
| di masing-masing FormRequest dan menimpa pesan di sini.
|
*/

return [
    'accepted' => ':attribute harus disetujui.',
    'active_url' => ':attribute bukan alamat yang sah.',
    'after' => ':attribute harus tanggal setelah :date.',
    'after_or_equal' => ':attribute harus tanggal :date atau sesudahnya.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute harus berupa daftar.',
    'before' => ':attribute harus tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus tanggal :date atau sebelumnya.',
    'between' => [
        'array' => ':attribute harus berisi antara :min sampai :max item.',
        'file' => 'Ukuran :attribute harus antara :min sampai :max kilobyte.',
        'numeric' => ':attribute harus bernilai antara :min sampai :max.',
        'string' => ':attribute harus terdiri antara :min sampai :max karakter.',
    ],
    'boolean' => ':attribute hanya boleh bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => ':attribute tidak sesuai.',
    'date' => ':attribute bukan tanggal yang sah.',
    'date_equals' => ':attribute harus tanggal yang sama dengan :date.',
    'date_format' => 'Format :attribute tidak sesuai dengan :format.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus terdiri dari :digits angka.',
    'digits_between' => ':attribute harus terdiri antara :min sampai :max angka.',
    'dimensions' => 'Ukuran gambar :attribute tidak sesuai.',
    'distinct' => ':attribute memuat nilai yang sama.',
    'email' => ':attribute harus berupa alamat email yang sah.',
    'ends_with' => ':attribute harus diakhiri salah satu dari: :values.',
    'enum' => 'Pilihan :attribute tidak sah.',
    'exists' => ':attribute yang dipilih tidak terdaftar.',
    'file' => ':attribute harus berupa berkas.',
    'filled' => ':attribute wajib diisi.',
    'gt' => [
        'array' => ':attribute harus berisi lebih dari :value item.',
        'file' => 'Ukuran :attribute harus lebih dari :value kilobyte.',
        'numeric' => ':attribute harus lebih besar dari :value.',
        'string' => ':attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => ':attribute harus berisi :value item atau lebih.',
        'file' => 'Ukuran :attribute minimal :value kilobyte.',
        'numeric' => ':attribute minimal bernilai :value.',
        'string' => ':attribute minimal :value karakter.',
    ],
    'image' => ':attribute harus berupa gambar.',
    'in' => 'Pilihan :attribute tidak sah.',
    'in_array' => ':attribute tidak ada di dalam :other.',
    'integer' => ':attribute harus berupa angka bulat.',
    'ip' => ':attribute harus berupa alamat IP yang sah.',
    'ipv4' => ':attribute harus berupa alamat IPv4 yang sah.',
    'ipv6' => ':attribute harus berupa alamat IPv6 yang sah.',
    'json' => ':attribute harus berupa JSON yang sah.',
    'lt' => [
        'array' => ':attribute harus berisi kurang dari :value item.',
        'file' => 'Ukuran :attribute harus kurang dari :value kilobyte.',
        'numeric' => ':attribute harus lebih kecil dari :value.',
        'string' => ':attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => ':attribute tidak boleh lebih dari :value item.',
        'file' => 'Ukuran :attribute maksimal :value kilobyte.',
        'numeric' => ':attribute maksimal bernilai :value.',
        'string' => ':attribute maksimal :value karakter.',
    ],
    'max' => [
        'array' => ':attribute tidak boleh lebih dari :max item.',
        'file' => 'Ukuran :attribute tidak boleh lebih dari :max kilobyte.',
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'mimes' => ':attribute harus berformat: :values.',
    'mimetypes' => ':attribute harus berformat: :values.',
    'min' => [
        'array' => ':attribute harus berisi minimal :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
        'numeric' => ':attribute minimal bernilai :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'not_in' => 'Pilihan :attribute tidak sah.',
    'not_regex' => 'Format :attribute tidak sah.',
    'numeric' => ':attribute harus berupa angka.',
    'present' => ':attribute wajib ada.',
    'regex' => 'Format :attribute tidak sesuai.',
    'required' => ':attribute wajib diisi.',
    'required_if' => ':attribute wajib diisi bila :other bernilai :value.',
    'required_unless' => ':attribute wajib diisi kecuali :other bernilai :values.',
    'required_with' => ':attribute wajib diisi bila ada :values.',
    'required_without' => ':attribute wajib diisi bila tidak ada :values.',
    'same' => ':attribute dan :other harus sama.',
    'size' => [
        'array' => ':attribute harus berisi :size item.',
        'file' => 'Ukuran :attribute harus :size kilobyte.',
        'numeric' => ':attribute harus bernilai :size.',
        'string' => ':attribute harus :size karakter.',
    ],
    'starts_with' => ':attribute harus diawali salah satu dari: :values.',
    'string' => ':attribute harus berupa teks.',
    'timezone' => ':attribute harus berupa zona waktu yang sah.',
    'unique' => ':attribute sudah dipakai.',
    'uploaded' => ':attribute gagal diunggah. Periksa ukuran berkasnya.',
    'url' => 'Format :attribute tidak sah.',
    'uuid' => ':attribute harus berupa UUID yang sah.',

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    'attributes' => [],
];
