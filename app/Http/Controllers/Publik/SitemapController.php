<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\AcaraDesa;
use App\Models\BeritaDesa;
use App\Models\PotensiDesa;
use Illuminate\Http\Response;

/**
 * Fitur 14 — sitemap.xml.
 *
 * Dibuat dinamis, bukan berkas statis: halaman detail berita, potensi, dan
 * galeri bertambah terus, dan sitemap statis akan basi tanpa ada yang
 * menyadarinya.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $url = collect();

        foreach ([
            ['beranda', 'daily', '1.0'],
            ['publik.profil', 'monthly', '0.8'],
            ['publik.statistik', 'weekly', '0.6'],
            ['publik.potensi.index', 'weekly', '0.8'],
            ['publik.fasilitas', 'monthly', '0.6'],
            ['publik.prestasi', 'monthly', '0.6'],
            ['publik.berita.index', 'daily', '0.9'],
            ['publik.galeri.index', 'weekly', '0.7'],
            ['publik.struktur', 'monthly', '0.7'],
        ] as [$rute, $frekuensi, $prioritas]) {
            $url->push(['loc' => route($rute), 'changefreq' => $frekuensi, 'priority' => $prioritas]);
        }

        BeritaDesa::query()->terbit()->get()->each(fn (BeritaDesa $b) => $url->push([
            'loc' => route('publik.berita.show', $b),
            'lastmod' => $b->updated_at?->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.7',
        ]));

        PotensiDesa::query()->tampil()->get()->each(fn (PotensiDesa $p) => $url->push([
            'loc' => route('publik.potensi.show', $p),
            'lastmod' => $p->updated_at?->toAtomString(),
            'changefreq' => 'monthly',
            'priority' => '0.7',
        ]));

        AcaraDesa::query()->terbit()->get()->each(fn (AcaraDesa $a) => $url->push([
            'loc' => route('publik.galeri.show', $a),
            'lastmod' => $a->updated_at?->toAtomString(),
            'changefreq' => 'yearly',
            'priority' => '0.5',
        ]));

        return response()
            ->view('publik.sitemap', ['url' => $url])
            ->header('Content-Type', 'application/xml');
    }
}
