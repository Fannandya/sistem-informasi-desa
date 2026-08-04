<?php

/*
|--------------------------------------------------------------------------
| Batas Impor Data — fitur 04
|--------------------------------------------------------------------------
|
| Angka di sini berasal dari batasan shared hosting cPanel (PRD 8): tidak ada
| queue worker, sehingga seluruh impor berjalan sinkron dalam satu permintaan
| dengan max_execution_time yang biasanya 30–60 detik.
|
*/

return [
    'maks_baris' => 2000,
    'maks_kb' => 5120,
    'ekstensi' => ['xlsx', 'xls', 'csv', 'txt'],
];
