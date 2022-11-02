<?php
namespace WebLogin\LaravelHoneypot;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use WebLogin\LaravelHoneypot\View\Components\FormFields;


class ServiceProvider extends BaseServiceProvider
{

    public function boot()
    {
        $this->registerConfig();
        $this->registerTranslations();
        $this->registerViews();
    }


    public function register()
    {
    }


    private function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/honeypot.php', 'honeypot');

        $this->publishes([__DIR__ . '/../config/honeypot.php' => config_path('honeypot.php')]);
    }


    private function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'honeypot');

        $langPath = "vendor/honeypot";
        $langPath = (function_exists('lang_path')) ? lang_path($langPath) : resource_path('lang/' . $langPath);
        $this->publishes([__DIR__ . '/../lang' => $langPath]);
    }


    private function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'honeypot');

        Blade::component('honeypot', FormFields::class);

        // @TODO : Fix the directive
        Blade::directive('honeypot', function ($expression) {
            $name = trim($expression, "'");
            return Blade::renderComponent(new FormFields($name));
        });
    }

}
