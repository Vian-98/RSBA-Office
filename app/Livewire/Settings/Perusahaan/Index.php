<?php

namespace App\Livewire\Settings\Perusahaan;

use Throwable;
use Livewire\Component;
use App\Models\Perusahaan;
use App\Traits\AuthorizesFromRoute;
use Illuminate\Container\Attributes\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use TallStackUi\Traits\Interactions;

#[Title('Perusahaan')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;
    use Interactions;
    use WithFileUploads;

    public $logoTmp;
    public $logo;
    public string $nama, $singkatan, $hastags, $alamat, $telp, $email, $website;

    protected $rules = [
        'nama' => 'required',
        'alamat' => 'required'
    ];

    function mount()
    {
        $perusahan = Perusahaan::first();
        $this->nama = $perusahan->nama;
        $this->singkatan = $perusahan->singkatan;
        $this->hastags = $perusahan->hastags;
        $this->alamat = $perusahan->alamat;
        $this->telp = $perusahan->telp;
        $this->email = $perusahan->email;
        $this->website = $perusahan->website;
        $this->logo = $perusahan->logo;
    }

    function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $perusahan = Perusahaan::find(1);
            $perusahan->nama = $this->nama;
            $perusahan->singkatan = $this->singkatan;
            $perusahan->hastags = $this->hastags;
            $perusahan->alamat = $this->alamat;
            $perusahan->telp = $this->telp;
            $perusahan->email = $this->email;
            $perusahan->website = $this->website;
            $perusahan->save();
            DB::commit();


            $this->toast()
                ->success('Updated!', 'Update data sukses!')
                ->send();
        } catch (Throwable $e) {
            DB::rollback();

            $this->toast()
                ->error('Failed!', 'Error: ' . $e->getMessage())
                ->send();
        }
    }

    function updateLogo()
    {

        $this->validate([
            'logoTmp' => 'required|image|max:1024', // 1MB Max
        ]);

        try {
            $path = $this->logoTmp->store('logos', 'public');

            Perusahaan::where('id', 1)->update(['logo' => $path]);

            $this->toast()->success('Success!', 'Logo berhasil diupdate.')->send();
        } catch (Throwable $e) {
            $this->toast()->error('Failed!', 'Error: ' . $e->getMessage())->send();
        }

        $this->reset('logoTmp');
    }

    public function render()
    {
        // $this->authorize('view-perusahaan');
        $this->authorizeFromRoute();
        return view('livewire.settings.perusahaan.index');
    }
}
