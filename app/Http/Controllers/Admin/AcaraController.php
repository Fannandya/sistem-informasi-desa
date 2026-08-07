<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UnggahanGagal;
use App\Http\Controllers\Controller;
use App\Models\AcaraDesa;
use App\Models\FotoAcara;
use App\Services\LayananUnggahGambar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcaraController extends Controller
{
    public function __construct(private readonly LayananUnggahGambar $unggahan) {}

    public function index(Request $request): View
    {
        return view('admin.acara.index', [
            'daftar' => AcaraDesa::query()
                ->withCount('foto')
                ->with('sampul')
                ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
                ->terbaru()
                ->paginate(20)
                ->withQueryString(),
            'status' => $request->input('status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.acara.form', ['acara' => new AcaraDesa(['status' => 'draf'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $acara = new AcaraDesa();
        $this->simpanData($request, $acara);

        return redirect()
            ->route('admin.acara.edit', $acara)
            ->with('status', 'Acara tersimpan. Silakan unggah fotonya.');
    }

    public function edit(AcaraDesa $acara): View
    {
        return view('admin.acara.form', ['acara' => $acara->load('foto')]);
    }

    public function update(Request $request, AcaraDesa $acara): RedirectResponse
    {
        $this->simpanData($request, $acara);

        return redirect()->route('admin.acara.index')->with('status', "Acara \"{$acara->judul}\" diperbarui.");
    }

    public function destroy(AcaraDesa $acara): RedirectResponse
    {
        $judul = $acara->judul;
        $jumlah = $acara->foto()->count();

        // Berkas foto dihapus lewat hook `deleting` pada model, sebelum
        // barisnya ikut terhapus oleh cascade.
        $acara->delete();

        return redirect()->route('admin.acara.index')->with('status', sprintf(
            'Acara "%s" dihapus beserta %d fotonya.',
            $judul,
            $jumlah,
        ));
    }

    public function ubahStatus(AcaraDesa $acara): RedirectResponse
    {
        $acara->update(['status' => $acara->sudahTerbit() ? 'draf' : 'terbit']);

        return back()->with('status', sprintf(
            '"%s" %s.',
            $acara->judul,
            $acara->sudahTerbit() ? 'diterbitkan' : 'dikembalikan ke draf',
        ));
    }

    // ------------------------------------------------------------------
    // Foto
    // ------------------------------------------------------------------

    public function unggahFoto(Request $request, AcaraDesa $acara): RedirectResponse
    {
        $maksBerkas = config('unggahan.maks_berkas_per_kiriman');

        $request->validate([
            'foto' => ['required', 'array', "max:{$maksBerkas}"],
            'foto.*' => LayananUnggahGambar::aturanValidasi(wajib: true),
        ], [
            'foto.required' => 'Pilih minimal satu foto.',
            'foto.max' => "Maksimal {$maksBerkas} foto sekali unggah. Unggah sisanya secara bergantian.",
        ]);

        $urutan = (int) $acara->foto()->max('urutan');
        $berhasil = 0;

        foreach ($request->file('foto') as $berkas) {
            try {
                $path = $this->unggahan->simpan($berkas, 'acara');
            } catch (UnggahanGagal $e) {
                // Satu berkas rusak tidak boleh membatalkan berkas lain yang
                // sudah berhasil diunggah.
                return back()->withErrors(['foto' => $e->getMessage().' '.$berhasil.' foto sebelumnya tetap tersimpan.']);
            }

            $acara->foto()->create([
                'path' => $path,
                'urutan' => ++$urutan,
                'sampul' => $acara->foto()->count() === 0 && $berhasil === 0,
            ]);

            $berhasil++;
        }

        return back()->with('status', "{$berhasil} foto berhasil diunggah.");
    }

    public function perbaruiFoto(Request $request, AcaraDesa $acara): RedirectResponse
    {
        $data = $request->validate([
            'keterangan' => ['array'],
            'keterangan.*' => ['nullable', 'string', 'max:200'],
            'sampul' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($acara, $data): void {
            foreach ($data['keterangan'] ?? [] as $id => $keterangan) {
                $acara->foto()->whereKey($id)->update(['keterangan' => $keterangan]);
            }

            if (isset($data['sampul'])) {
                $acara->foto()->update(['sampul' => false]);
                $acara->foto()->whereKey($data['sampul'])->update(['sampul' => true]);
            }
        });

        return back()->with('status', 'Keterangan foto diperbarui.');
    }

    public function hapusFoto(AcaraDesa $acara, FotoAcara $foto): RedirectResponse
    {
        abort_unless($foto->acara_id === $acara->id, 404);

        $adalahSampul = $foto->sampul;
        $foto->delete();

        // Bila sampul yang dihapus, foto berikutnya naik menggantikan agar
        // kartu galeri tidak mendadak kehilangan gambarnya.
        if ($adalahSampul && $lain = $acara->foto()->first()) {
            $lain->update(['sampul' => true]);
        }

        return back()->with('status', 'Foto dihapus.');
    }

    private function simpanData(Request $request, AcaraDesa $acara): void
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:180'],
            'tanggal_acara' => ['required', 'date', 'before_or_equal:'.now()->addYear()->toDateString()],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'narasi' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['draf', 'terbit'])],
            'slug' => [
                'nullable', 'string', 'max:200', 'alpha_dash',
                Rule::unique('acara_desa', 'slug')->ignore($acara->id),
            ],
        ], [
            'tanggal_acara.before_or_equal' => 'Tanggal acara terlalu jauh di masa depan.',
            'slug.unique' => 'Alamat URL itu sudah dipakai acara lain.',
        ], ['tanggal_acara' => 'tanggal acara']);

        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        }

        $data['admin_id'] = $acara->admin_id ?? $request->user('admin')->id;

        $acara->fill($data)->save();
    }
}
