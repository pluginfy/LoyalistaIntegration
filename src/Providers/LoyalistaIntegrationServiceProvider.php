<?php

namespace LoyalistaIntegration\Providers;

use Plenty\Plugin\ServiceProvider;

use LoyalistaIntegration\Contracts\ToDoRepositoryContract;
use LoyalistaIntegration\Repositories\ToDoRepository;

/**
 * Class LoyalistaIntegrationServiceProvider
 * @package LoyalistaIntegration\Providers
 */
class LoyalistaIntegrationServiceProvider extends ServiceProvider
{
    /**
    * Register the route service provider
    */
    public function register()
    {
        $this->getApplication()->register(LoyalistaIntegrationRouteServiceProvider::class);

        // bind() function to bind the ToDoRepositoryContract class to the ToDoRepository class.
        // This way, when using the ToDoRepositoryContract` class via dependency injection,
        // the functions defined in the repository will be implemented.

        $this->getApplication()->bind(ToDoRepositoryContract::class, ToDoRepository::class);

    }
}