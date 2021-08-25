<?php
    
    namespace Ys\Export;

    use Illuminate\Support\ServiceProvider;
    
    class ExportServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap the application services.
         *
         * @return void
         */
        public function boot()
        {
            //
        }
    
        /**
         * Register the application services.
         *
         * @return void
         */
        public function register()
        {
            $config = __DIR__.'/config/export.php';
            // merge config
            $this->mergeConfigFrom($config, 'ys-export');
            $this->publishes([
                __DIR__.'/config/export.php' => config_path('ys-export.php')
            ],'ys-export:config');
        }
    }
