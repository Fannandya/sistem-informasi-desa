# BadMethodCallException - Internal Server Error

Call to undefined method App\Models\RefKategoriFasilitas::warna()

PHP 8.2.12
Laravel 12.64.0
localhost:8000

## Stack Trace

0 - vendor\laravel\framework\src\Illuminate\Support\Traits\ForwardsCalls.php:67
1 - vendor\laravel\framework\src\Illuminate\Support\Traits\ForwardsCalls.php:36
2 - vendor\laravel\framework\src\Illuminate\Database\Eloquent\Model.php:2544
3 - resources\views\publik\beranda.blade.php:343
4 - vendor\laravel\framework\src\Illuminate\Filesystem\Filesystem.php:123
5 - vendor\laravel\framework\src\Illuminate\Filesystem\Filesystem.php:124
6 - vendor\laravel\framework\src\Illuminate\View\Engines\PhpEngine.php:57
7 - vendor\laravel\framework\src\Illuminate\View\Engines\CompilerEngine.php:76
8 - vendor\laravel\framework\src\Illuminate\View\View.php:208
9 - vendor\laravel\framework\src\Illuminate\View\View.php:191
10 - vendor\laravel\framework\src\Illuminate\View\View.php:160
11 - vendor\laravel\framework\src\Illuminate\Http\Response.php:78
12 - vendor\laravel\framework\src\Illuminate\Http\Response.php:34
13 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:939
14 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:906
15 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
16 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
17 - vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:50
18 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
19 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php:87
20 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
21 - vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48
22 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
23 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120
24 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
25 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
26 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
27 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
28 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
29 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
30 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
31 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
32 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
33 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
34 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
35 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
36 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
37 - app\Http\Middleware\EnsureAdminSessionNotExpired.php:15
38 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
39 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
40 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
41 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
42 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
43 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
44 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
45 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
46 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
47 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
48 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
49 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
50 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
51 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
52 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
53 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
54 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
55 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
56 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
57 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
58 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
59 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
60 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
61 - public\index.php:20
62 - vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php:23

## Request

GET /

## Headers

* **host**: localhost:8000
* **connection**: keep-alive
* **cache-control**: max-age=0
* **sec-ch-ua**: "Not;A=Brand";v="8", "Chromium";v="150", "Google Chrome";v="150"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://localhost:8000/
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7
* **cookie**: sb-cblvdnzotjugymrnfgvn-auth-token=base64-eyJhY2Nlc3NfdG9rZW4iOiJleUpoYkdjaU9pSkZVekkxTmlJc0ltdHBaQ0k2SWpkaE1UazFNak16TFRnek5XUXROREl3T0MwNE1UWmtMV000TURNNU56VTJNekF4WmlJc0luUjVjQ0k2SWtwWFZDSjkuZXlKcGMzTWlPaUpvZEhSd2N6b3ZMMk5pYkhaa2JucHZkR3AxWjNsdGNtNW1aM1p1TG5OMWNHRmlZWE5sTG1OdkwyRjFkR2d2ZGpFaUxDSnpkV0lpT2lJelpHWTFNR0ZpTmkwMk5EazJMVFJqT0RJdFlUSXlaUzAyTm1NeE0yRTVNbVkxWkRZaUxDSmhkV1FpT2lKaGRYUm9aVzUwYVdOaGRHVmtJaXdpWlhod0lqb3hOemcwT0RnM016Y3dMQ0pwWVhRaU9qRTNPRFE0T0RNM056QXNJbVZ0WVdsc0lqb2lkR0Z0WVVCbmJXRnBiQzVqYjIwaUxDSndhRzl1WlNJNklpSXNJbUZ3Y0Y5dFpYUmhaR0YwWVNJNmV5SndjbTkyYVdSbGNpSTZJbVZ0WVdsc0lpd2ljSEp2ZG1sa1pYSnpJanBiSW1WdFlXbHNJbDE5TENKMWMyVnlYMjFsZEdGa1lYUmhJanA3SW1WdFlXbHNYM1psY21sbWFXVmtJanAwY25WbGZTd2ljbTlzWlNJNkltRjFkR2hsYm5ScFkyRjBaV1FpTENKaFlXd2lPaUpoWVd3eElpd2lZVzF5SWpwYmV5SnRaWFJvYjJRaU9pSndZWE56ZDI5eVpDSXNJblJwYldWemRHRnRjQ0k2TVRjNE5EZzRNemMzTUgxZExDSnpaWE56YVc5dVgybGtJam9pWmpOaU1HUXdPVE10WWpnek5DMDBORGcyTFdFek9HTXROemRtWlRsbFlUTXlZVFJqSWl3aWFYTmZZVzV2Ym5sdGIzVnpJanBtWVd4elpYMC5TYi1PT3dkRk1wREVOYVVpeFdWRE9XWTdoaG9yTnNMaWtmYzZxTFdHLVdtRXJqVk03N29SeGZ4STg4SmVKdVBCeF9GTDV6Wmw4TWJNRGhNZ293N2lkUSIsInRva2VuX3R5cGUiOiJiZWFyZXIiLCJleHBpcmVzX2luIjozNjAwLCJleHBpcmVzX2F0IjoxNzg0ODg3MzcwLCJyZWZyZXNoX3Rva2VuIjoicmJpeGc1eGk1azZ6IiwidXNlciI6eyJpZCI6IjNkZjUwYWI2LTY0OTYtNGM4Mi1hMjJlLTY2YzEzYTkyZjVkNiIsImF1ZCI6ImF1dGhlbnRpY2F0ZWQiLCJyb2xlIjoiYXV0aGVudGljYXRlZCIsImVtYWlsIjoidGFtYUBnbWFpbC5jb20iLCJlbWFpbF9jb25maXJtZWRfYXQiOiIyMDI2LTA3LTIyVDE0OjMyOjM5LjYyMjU4M1oiLCJwaG9uZSI6IiIsImNvbmZpcm1lZF9hdCI6IjIwMjYtMDctMjJUMTQ6MzI6MzkuNjIyNTgzWiIsImxhc3Rfc2lnbl9pbl9hdCI6IjIwMjYtMDctMjRUMDk6MDI6NTAuNjQ0ODEyMjMxWiIsImFwcF9tZXRhZGF0YSI6eyJwcm92aWRlciI6ImVtYWlsIiwicHJvdmlkZXJzIjpbImVtYWlsIl19LCJ1c2VyX21ldGFkYXRhIjp7ImVtYWlsX3ZlcmlmaWVkIjp0cnVlfSwiaWRlbnRpdGllcyI6W3siaWRlbnRpdHlfaWQiOiI2OWRlOWNlYi0wZjYxLTRlZTYtOTE0Ny0xZjE4NmE1OWM5YTIiLCJpZCI6IjNkZjUwYWI2LTY0OTYtNGM4Mi1hMjJlLTY2YzEzYTkyZjVkNiIsInVzZXJfaWQiOiIzZGY1MGFiNi02NDk2LTRjODItYTIyZS02NmMxM2E5MmY1ZDYiLCJpZGVudGl0eV9kYXRhIjp7ImVtYWlsIjoidGFtYUBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsInBob25lX3ZlcmlmaWVkIjpmYWxzZSwic3ViIjoiM2RmNTBhYjYtNjQ5Ni00YzgyLWEyMmUtNjZjMTNhOTJmNWQ2In0sInByb3ZpZGVyIjoiZW1haWwiLCJsYXN0X3NpZ25faW5fYXQiOiIyMDI2LTA3LTIyVDE0OjMyOjM5LjYyMDg1WiIsImNyZWF0ZWRfYXQiOiIyMDI2LTA3LTIyVDE0OjMyOjM5LjYyMDg5WiIsInVwZGF0ZWRfYXQiOiIyMDI2LTA3LTIyVDE0OjMyOjM5LjYyMDg5WiIsImVtYWlsIjoidGFtYUBnbWFpbC5jb20ifV0sImNyZWF0ZWRfYXQiOiIyMDI2LTA3LTIyVDE0OjMyOjM5LjYwNzA1NFoiLCJ1cGRhdGVkX2F0IjoiMjAyNi0wNy0yNFQwOTowMjo1MC42NTU5MjRaIiwiaXNfYW5vbnltb3VzIjpmYWxzZX0sIndlYWtfcGFzc3dvcmQiOnsibWVzc2FnZSI6IlBhc3N3b3JkIHNob3VsZCBiZSBhdCBsZWFzdCA2IGNoYXJhY3RlcnMuIiwicmVhc29ucyI6WyJsZW5ndGgiXX19; sb-ualvxfgtladpdvowrbxf-auth-token=base64-eyJhY2Nlc3NfdG9rZW4iOiJleUpoYkdjaU9pSkZVekkxTmlJc0ltdHBaQ0k2SW1RMlptSXpZbVF6TFdOa09XUXROR1F3WWkwNFptUTBMVGRsWW1ZMU9XTTBaVFkzTkNJc0luUjVjQ0k2SWtwWFZDSjkuZXlKcGMzTWlPaUpvZEhSd2N6b3ZMM1ZoYkhaNFptZDBiR0ZrY0dSMmIzZHlZbmhtTG5OMWNHRmlZWE5sTG1OdkwyRjFkR2d2ZGpFaUxDSnpkV0lpT2lKak4yUTJObUkxTUMwMll6UTJMVFJsWWpZdE9EaGxNeTA1Tm1GbE5UazBZakkzT0RraUxDSmhkV1FpT2lKaGRYUm9aVzUwYVdOaGRHVmtJaXdpWlhod0lqb3hOemcxTVRZd016UXpMQ0pwWVhRaU9qRTNPRFV4TlRZM05ETXNJbVZ0WVdsc0lqb2ljbVYwWVd4cE9UbEFiV0ZwYkM1amIyMGlMQ0p3YUc5dVpTSTZJaUlzSW1Gd2NGOXRaWFJoWkdGMFlTSTZleUp3Y205MmFXUmxjaUk2SW1WdFlXbHNJaXdpY0hKdmRtbGtaWEp6SWpwYkltVnRZV2xzSWwxOUxDSjFjMlZ5WDIxbGRHRmtZWFJoSWpwN0ltVnRZV2xzWDNabGNtbG1hV1ZrSWpwMGNuVmxmU3dpY205c1pTSTZJbUYxZEdobGJuUnBZMkYwWldRaUxDSmhZV3dpT2lKaFlXd3hJaXdpWVcxeUlqcGJleUp0WlhSb2IyUWlPaUp3WVhOemQyOXlaQ0lzSW5ScGJXVnpkR0Z0Y0NJNk1UYzROVEUxTmpjME0zMWRMQ0p6WlhOemFXOXVYMmxrSWpvaU9HWXpPR05oWVRJdE5HTTNNaTAwTmpjeUxXSmtabVF0TkdJNE9UazBOVGMxTWpCbUlpd2lhWE5mWVc1dmJubHRiM1Z6SWpwbVlXeHpaWDAuZUluRlNnUGZhOC1ySVQ1ZDVaNTFOLWZfT1BzajBKeUdob19yWlgzUU5ubTdTZDQxREQxV01Odjl0UDFlcWZxRDdZMGcwNUt6MWFEWEtlUmR4d1Z6ZlEiLCJ0b2tlbl90eXBlIjoiYmVhcmVyIiwiZXhwaXJlc19pbiI6MzYwMCwiZXhwaXJlc19hdCI6MTc4NTE2MDM0MywicmVmcmVzaF90b2tlbiI6IjJrenNmdG92cnlyaSIsInVzZXIiOnsiaWQiOiJjN2Q2NmI1MC02YzQ2LTRlYjYtODhlMy05NmFlNTk0YjI3ODkiLCJhdWQiOiJhdXRoZW50aWNhdGVkIiwicm9sZSI6ImF1dGhlbnRpY2F0ZWQiLCJlbWFpbCI6InJldGFsaTk5QG1haWwuY29tIiwiZW1haWxfY29uZmlybWVkX2F0IjoiMjAyNi0wNy0yNlQwMjo1ODowMC4xMDYzMloiLCJwaG9uZSI6IiIsImNvbmZpcm1lZF9hdCI6IjIwMjYtMDctMjZUMDI6NTg6MDAuMTA2MzJaIiwibGFzdF9zaWduX2luX2F0IjoiMjAyNi0wNy0yN1QxMjo1MjoyMy43NDU5OTY1NjdaIiwiYXBwX21ldGFkYXRhIjp7InByb3ZpZGVyIjoiZW1haWwiLCJwcm92aWRlcnMiOlsiZW1haWwiXX0sInVzZXJfbWV0YWRhdGEiOnsiZW1haWxfdmVyaWZpZWQiOnRydWV9LCJpZGVudGl0aWVzIjpbeyJpZGVudGl0eV9pZCI6IjliZjFmYzdhLThhY2UtNDkwYy1iZDYwLTdlYTVhM2NlZjljMyIsImlkIjoiYzdkNjZiNTAtNmM0Ni00ZWI2LTg4ZTMtOTZhZTU5NGIyNzg5IiwidXNlcl9pZCI6ImM3ZDY2YjUwLTZjNDYtNGViNi04OGUzLTk2YWU1OTRiMjc4OSIsImlkZW50aXR5X2RhdGEiOnsiZW1haWwiOiJyZXRhbGk5OUBtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjpmYWxzZSwicGhvbmVfdmVyaWZpZWQiOmZhbHNlLCJzdWIiOiJjN2Q2NmI1MC02YzQ2LTRlYjYtODhlMy05NmFlNTk0YjI3ODkifSwicHJvdmlkZXIiOiJlbWFpbCIsImxhc3Rfc2lnbl9pbl9hdCI6IjIwMjYtMDctMjZUMDI6NTg6MDAuMDk4Mzc5WiIsImNyZWF0ZWRfYXQiOiIyMDI2LTA3LTI2VDAyOjU4OjAwLjA5ODQzN1oiLCJ1cGRhdGVkX2F0IjoiMjAyNi0wNy0yNlQwMjo1ODowMC4wOTg0MzdaIiwiZW1haWwiOiJyZXRhbGk5OUBtYWlsLmNvbSJ9XSwiY3JlYXRlZF9hdCI6IjIwMjYtMDctMjZUMDI6NTg6MDAuMDc3NTQ3WiIsInVwZGF0ZWRfYXQiOiIyMDI2LTA3LTI3VDEyOjUyOjIzLjc3MjI4M1oiLCJpc19hbm9ueW1vdXMiOmZhbHNlfSwid2Vha19wYXNzd29yZCI6bnVsbH0; XSRF-TOKEN=eyJpdiI6IjdXcUhwT05MZ2M1QzlOOEZzb1FPT3c9PSIsInZhbHVlIjoidVU1NXlFS0p4ZWgxYXoxWm9rZ1ZiNWxwS0kvK2J3Tm44TDEzdURrOGN3THdjQnNEWm82Vyt0amV6Q1RLc2kxY21wZDBvT0hxMlRiY0poaFFxcUkrTDJMTG12UVJBRkZ3Nkk1QlR2QjQyMnNJd3NtN3h6RlFrYmVFSmhkL00xRFIiLCJtYWMiOiJkYTk5MWZiZWQ5YjY2Y2U5NjI3MzQ2NTI4Mzk0ZWRjYTczYTZiMzc3Nzg2MzczOWJmYzA4NDRhMzZhNWQ2NTg4IiwidGFnIjoiIn0%3D; sistem-informasi-desa-session=eyJpdiI6InlhVlJ0SUhvc1ozRDVPcU5SWFJONWc9PSIsInZhbHVlIjoiTzRsTXlQOGNsaUJvSVQyUkxMVGx5WFNmdHhzbTBYR1ByaHQ3akRiN2RudUREQ0I4a2Y3aDIyYksybzMzTFRBbmk1VWpPc2VwbzZZRlREZDJ1TFN5R05RQWkvMVMvZktNdmpucVRiV3QyUzA2a0h4TmJTMHB3bzM3c0tnR0FIWHYiLCJtYWMiOiI4OTUxYjJkODc1MmY0OTlmYTJkZDRiMGExODZjZmJiZThjODAyZTRjMTRkMzA3OWJhMzRiYjg4MTg5NTY2OGYzIiwidGFnIjoiIn0%3D

## Route Context

controller: App\Http\Controllers\Publik\BerandaController
route name: beranda
middleware: web

## Route Parameters

No route parameter data available.

## Database Queries

* mysql - select * from `sessions` where `id` = '1PYeyQ6Gq1GriJESEd9vUiJzLRJnDqNwXAFyffDX' limit 1 (2.11 ms)
* mysql - select * from `carousel_images` where `tampil` = 1 order by `urutan` asc (0.49 ms)
* mysql - select * from `cache` where `key` in ('sistem-informasi-desa-cache-teks_tampilan') (0.67 ms)
* mysql - select * from `potensi_desa` where `tampil` = 1 order by `urutan` asc, `nama` asc limit 6 (0.49 ms)
* mysql - select * from `berita_desa` where `status` = 'terbit' and `utama` = 1 limit 1 (0.38 ms)
* mysql - select * from `kategori_berita` where `kategori_berita`.`id` in (1) (0.56 ms)
* mysql - select * from `berita_desa` where `status` = 'terbit' order by `tanggal_terbit` desc, `id` desc limit 4 (0.45 ms)
* mysql - select * from `kategori_berita` where `kategori_berita`.`id` in (1, 2, 3) (0.24 ms)
* mysql - select `acara_desa`.*, (select count(*) from `foto_acara` where `acara_desa`.`id` = `foto_acara`.`acara_id`) as `foto_count` from `acara_desa` where `status` = 'terbit' order by `tanggal_acara` desc, `id` desc limit 6 (0.8 ms)
* mysql - select * from `fasilitas_umum` where `tampil` = 1 limit 4 (0.36 ms)
* mysql - select * from `prestasi_desa` where `tampil` = 1 order by `tahun` desc, `id` desc limit 3 (0.63 ms)
* mysql - select `pengurus_desa`.* from `pengurus_desa` inner join `ref_organisasi` on `pengurus_desa`.`organisasi_id` = `ref_organisasi`.`id` where `tampil` = 1 order by `ref_organisasi`.`urutan` asc, `pengurus_desa`.`urutan` asc limit 4 (0.74 ms)
* mysql - select * from `ref_organisasi` where `ref_organisasi`.`id` in (1) (0.38 ms)
* mysql - select jenis_kelamin, COUNT(*) as total from `penduduk` where `deleted_at` is null and `penduduk`.`deleted_at` is null group by `jenis_kelamin` (0.75 ms)
* mysql - select count(*) as aggregate from `penduduk` where `deleted_at` is null and `penduduk`.`deleted_at` is null (0.33 ms)
* mysql - select max(`tahun`) as aggregate from `rekap_kk` (0.25 ms)
* mysql - select sum(`jumlah_kk`) as aggregate from `rekap_kk` where `tahun` = 2026 (0.24 ms)
* mysql - select sum(`jumlah_kk`) as aggregate from `rekap_kk` where `tahun` = 2026 (0.32 ms)
* mysql - select count(*) as aggregate from `penduduk` where `deleted_at` is null and `penduduk`.`deleted_at` is null (0.41 ms)
* mysql - select * from `cache` where `key` in ('sistem-informasi-desa-cache-pengaturan_desa') (0.52 ms)
* mysql - select * from `ref_kategori_potensi` where `ref_kategori_potensi`.`id` = 1 limit 1 (0.48 ms)
* mysql - select * from `ref_kategori_potensi` where `ref_kategori_potensi`.`id` = 2 limit 1 (0.41 ms)
* mysql - select * from `ref_kategori_potensi` where `ref_kategori_potensi`.`id` = 3 limit 1 (0.58 ms)
* mysql - select * from `ref_kategori_potensi` where `ref_kategori_potensi`.`id` = 4 limit 1 (0.28 ms)
* mysql - select * from `ref_kategori_potensi` where `ref_kategori_potensi`.`id` = 2 limit 1 (0.27 ms)
* mysql - select * from `ref_kategori_potensi` where `ref_kategori_potensi`.`id` = 1 limit 1 (0.33 ms)
* mysql - select * from `ref_kategori_fasilitas` where `ref_kategori_fasilitas`.`id` = 1 limit 1 (0.36 ms)
* mysql - select * from `cache` where `key` in ('sistem-informasi-desa-cache-pengaturan_desa') (0.75 ms)
