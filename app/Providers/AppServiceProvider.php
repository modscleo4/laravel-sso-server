<?php

namespace App\Providers;

use App\Auth;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use JeroenNoten\LaravelAdminLte\Menu\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @param Dispatcher $events
     * @return void
     */
    public function boot(Dispatcher $events)
    {
        Schema::defaultStringLength(191);
        setlocale(LC_TIME, "{$this->app->getLocale()}.UTF8");

        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {
            $this->loadMenu($event->menu);
        });
    }

    private function loadMenu(Builder $menu)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $menu->add(__('menu.system'));
            $menu->add([
                'text' => __('menu.users'),
                'icon' => 'fas fa-fw fa-user',
                'submenu' => [
                    [
                        'text' => __('menu.view'),
                        'route' => 'admin.user.index',
                        'icon' => 'fas fa-fw fa-th-list',
                        'active' => ['admin/user']
                    ],
                    [
                        'text' => __('menu.new'),
                        'route' => 'admin.user.new',
                        'icon' => 'fas fa-fw fa-edit',
                        'active' => ['admin/user/new']
                    ],
                ]
            ]);

            $menu->add([
                'text' => __('menu.brokers'),
                'icon' => 'fas fa-fw fa-server',
                'submenu' => [
                    [
                        'text' => __('menu.view'),
                        'route' => 'admin.broker.index',
                        'icon' => 'fas fa-fw fa-th-list',
                        'active' => ['admin/broker']
                    ],
                    [
                        'text' => __('menu.new'),
                        'route' => 'admin.broker.new',
                        'icon' => 'fas fa-fw fa-edit',
                        'active' => ['admin/broker/new']
                    ],
                ]
            ]);
        }
    }
}
