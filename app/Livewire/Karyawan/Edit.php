<?php

namespace App\Livewire\Karyawan;

use Throwable;
use App\Models\Sdm\Karyawan;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;


#[Lazy]
#[Title('Edit Karyawan')]
class Edit extends Component
{
    use Interactions;

    #[Locked]
    public ?Karyawan $karyawan;

    public function mount($id)
    {
        $this->karyawan = Karyawan::findOrFail($id);
    }

    #[On('updated-karywan')]
    #[On('status-updated')]
    #[On('new-jabatan-created')]
    public function refreshKaryawan()
    {
        $this->karyawan->refresh();
    }

    function directback()
    {
        return $this->redirectIntended('/kepegawaian/karyawan', navigate: true);
    }

    function delete($id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $this->dialog()
            ->question('Warning!', "Yakin hapus <b>$karyawan->nama</b> ?")
            ->confirm('Hapus', 'confirmed', $id)
            ->cancel('Batal', 'cancelled')
            ->send();
    }

    function confirmed($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        try {
            $karyawan->delete();
            $this->directback(); //return back

            $this->toast()
                ->success('Berhasil', "<b>$karyawan->nama</b>  berhasil dihapus.")
                ->send();
        } catch (Throwable $th) {
            $this->toast()
                ->error('Failed', "Error : " . $th->getMessage())
                ->send();
        }
    }

    function cancelled()
    {
        $this->toast()
            ->info('Dibatalkan', 'Hapus data karyawan dibatalkan.')
            ->send();
    }

    function placeholder()
    {
        $skeleton  = file_get_contents(resource_path('views/components/skeleton.blade.php'));
        return <<<HTML
    <div class="w-full">
     <div class="flex flex-col lg:flex-row lg:gap-5">

        <div class="w-full lg:w-1/4 ">
            <!-- include  compoenent.skeleton -->
             $skeleton
        </div>

        <div class="w-full lg:w-3/4 ">
            <!-- include  compoenent.skeleton -->
            $skeleton
        </div>
    </div>
    HTML;
    }

    public function render()
    {
        $this->authorize('edit-kepegawaian-karyawan');
        return view('livewire.karyawan.edit');
    }
}
