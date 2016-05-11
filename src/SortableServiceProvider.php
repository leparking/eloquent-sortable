<?php

namespace LeParking\Sortable;

use Illuminate\Support\ServiceProvider;

class SortableServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register Eloquent events listeners for the sortables models.
     *
     * @return void
     */
    public function register()
    {
        $events = ['creating', 'created', 'deleted'];

        foreach ($events as $event) {
            $this->app['events']->listen("eloquent.$event*", function ($model) use ($event) {
                if ($model instanceof Sortable) {
                    $method = 'sortable' . ucfirst($event);
                    call_user_func([$model, $method]);
                }
            });
        }
    }

    /**
     * Load the sortable package configuration.
     *
     * @return void
     */
    public function boot()
    {
        $config = __DIR__ . '/../config/sortable.php';
        $this->publishes([$config => config_path('sortable.php')]);
        $this->mergeConfigFrom($config, 'sortable');
    }
}
