<?php

namespace App\Livewire\Settings\Menu;

use Throwable;
use App\Models\Menu;
use Livewire\Component;
use App\Enums\MenuGroup;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Permission;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?Menu $menu;
    public $nama;
    public $route;
    public bool $route_avail = false;
    public $icon;
    public $permission = [];
    public $parent;
    public $group;

    // select option
    // public $parents;
    public $groups;

    public function rules(): array
    {
        return [
            'nama' => 'required|string',
            'permission' => $this->route ? 'required' : []
        ];
    }

    function mount($id)
    {

        $this->menu = Menu::findOrFail($id);

        if ($this->menu) {
            $this->nama = $this->menu->nama;
            $this->route = $this->menu->route;
            $this->icon = $this->menu->icon;
            $this->permission = $this->menu->permission;
            $this->parent = $this->menu->parent_id;
            $this->group = $this->menu->group;
        }
        // 
        // $this->permission_options = Permission::select('id', 'name')->get();
        $this->route_avail = $this->cekRouteList(routeName: $this->route);
        $this->groups = MenuGroup::options();
    }

    #[Computed]
    public function parents()
    {
        return Menu::with('parent')->select('id', 'nama', 'parent_id', 'group')
            ->get()
            ->map(
                fn($item) => [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'description' => ($item->parent?->nama ?? 'Main Menu') . ", " .  ($item->group?->nama())
                ]
            );
    }

    #[Computed]
    public function permissionOptions(): array
    {
        return Permission::select('id', 'name')->get()->toArray();
    }

    // check route form blade
    public function updatedRoute($value)
    {
        $this->route_avail = $this->cekRouteList(routeName: $value);
    }

    // get route list
    private function getRouteList()
    {
        return collect(Route::getRoutes())
            ->filter(function ($route) {
                return in_array('GET', $route->methods());
            })
            ->map(function ($route) {
                return $route->getName();
            })
            ->toArray();
    }

    // cek is route available
    private function cekRouteList($routeName)
    {
        $routes = $this->getRouteList();
        return in_array($routeName, $routes);
    }

    function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $this->menu->nama = $this->nama;
            $this->menu->route = $this->route;
            $this->menu->icon = $this->icon;
            $this->menu->permission = $this->permission;
            $this->menu->parent_id = $this->parent;
            $this->menu->group = $this->group;
            $this->menu->save();

            DB::commit();

            $this->dispatch('menu-updated');

            $this->toast()
                ->success('Berhasil', 'Update menu sukses.')
                ->send();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast()
                ->error('Failed', 'Error : ' . $th->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.settings.menu.edit');
    }
}
