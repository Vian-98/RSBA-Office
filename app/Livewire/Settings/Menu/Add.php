<?php

namespace App\Livewire\Settings\Menu;

use Throwable;
use App\Enums\MenuGroup;
use App\Models\Menu;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $nama, $route, $icon;
    public bool $route_avail = false;
    public $parent_id;
    public $group;

    // select-options
    public $parents;
    public $groups;
    public $routes;

    public function rules(): array
    {
        return [
            'nama' => "required|string",
            'route' => "nullable|string",
            'route_avail' => ['boolean', function ($attribute, $value, $fail) {
                if (!empty($this->route) && !$this->route_avail) {
                    $fail('Route harus tersedia.');
                }
            }],
            'parent_id' => "required|integer",
        ];
    }

    public function mount()
    {
        $this->parents = Menu::with('parent')->select('id', 'nama', 'parent_id', 'group')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'description' => ($item->parent?->nama ?? 'Main Menu') . ', ' . ($item->group?->nama() ?? null)
                ];
            });

        $this->groups = MenuGroup::options();

        // get route list
        $this->routes = $this->getRouteList();
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


    function submit()
    {
        $this->validate();

        // default permission to this menu : string
        // $defaultPermission = 'view-' . Str::slug($this->nama); // concate 'view-' $nama 
        // $collectDefaultPermission[] = $defaultPermission; // format as array
        $permissions = $this->generatePermissionFromRoute(route: $this->route);


        // data prepare to insert
        $data = [
            'nama' => $this->nama,
            'route' => $this->route,
            'icon' => $this->icon,
            'parent_id' => $this->parent_id ?? 0,
            'group' => $this->group,
            'permission' => $permissions //insert array, di model sudah casts = [permission => array]
        ];

        DB::beginTransaction();
        try {
            // create permission
            // Permission::findOrCreate($defaultPermission, 'web');
            foreach ($permissions as $perm) {
                Permission::firstOrCreate([
                    'name'       => $perm,
                    'guard_name' => 'web',
                ]);

                // Relasi menu → permission
                // $menu->permissions()->attach($permission->id);
            }

            // insert data
            Menu::create($data);
            DB::commit();

            $this->dispatch('new-menu-created');

            $this->toast()
                ->success('Berhasil', 'Menu baru disimpan.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error :' . $e->getMessage())
                ->send();

            # code...

        }
    }

    protected function generatePermissionFromRoute(?string $route = null): array
    {
        if (empty($route)) return [];

        $resource = str_replace('.', '-', $route);

        return [
            "view-{$resource}",
            "add-{$resource}",
            "edit-{$resource}",
            "delete-{$resource}",
        ];
    }

    public function render()
    {
        return view('livewire.settings.menu.add');
    }
}
