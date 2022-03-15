<?php

namespace WalkerChiu\MorphCategory;

use Illuminate\Support\ServiceProvider;

class MorphCategoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/morph-category.php' => config_path('wk-morph-category.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_morph_category_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_morph_category_table.php',
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-morph-category');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-morph-category'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-morph-category.command.cleaner')
            ]);
        }

        config('wk-core.class.morph-category.category')::observe(config('wk-core.class.morph-category.categoryObserver'));
        config('wk-core.class.morph-category.categoryLang')::observe(config('wk-core.class.morph-category.categoryLangObserver'));
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-morph-category')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/morph-category.php', 'wk-morph-category'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/morph-category.php', 'morph-category'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
