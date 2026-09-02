<?php

namespace App\Providers;

use App\Repositories\Contracts\GroceryItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Eloquent\GroceryItemRepository;
use App\Repositories\Eloquent\OrderRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        GroceryItemRepositoryInterface::class => GroceryItemRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
