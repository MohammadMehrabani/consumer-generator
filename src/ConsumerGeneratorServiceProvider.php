<?php

namespace MohammadMehrabani\ConsumerGenerator;

use Illuminate\Support\ServiceProvider;

class ConsumerGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/consumer-generator.php', 'consumer-generator');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/consumer-generator.php' => config_path('consumer-generator.php')
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\Generate::class,
                Console\Commands\Worker::class,
            ]);
        }
    }
}
