<?php
namespace WebLogin\LaravelHoneypot;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use WebLogin\LaravelHoneypot\View\Components\FormFields;


class ServiceProvider extends BaseServiceProvider
{

    public function boot()
    {
        $this->bootConfig();
        $this->bootTranslations();
        $this->bootViews();
    }


    private function bootConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/honeypot.php', 'honeypot');

        $this->publishes([__DIR__ . '/../config/honeypot.php' => config_path('honeypot.php')]);
    }


    private function bootTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'honeypot');

        $langPath = "vendor/honeypot";
        $langPath = (function_exists('lang_path')) ? lang_path($langPath) : resource_path('lang/' . $langPath);
        $this->publishes([__DIR__ . '/../lang' => $langPath]);
    }


    private function bootViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'honeypot');

        Blade::component('honeypot', FormFields::class);

        Blade::directive('honeypot', function ($name) {
            return "<?php echo Blade::renderComponent(new \WebLogin\LaravelHoneypot\View\Components\FormFields($name)) ?>";
        });
    }

}
