<?php
namespace WebLogin\LaravelHoneypot\View\Components;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;


class FormFields extends Component
{

    public string $name;
    public string $encryptedTime;
    public string $potFieldName;
    public string $timeFieldName;


    public function __construct(string $name)
    {
        $this->name = $name;
        $this->encryptedTime = Crypt::encrypt(time());
        $this->potFieldName = "p" . Str::random();
        $this->timeFieldName = "t" . Str::random();
    }


    public function render()
    {
        if (!Config::get('honeypot.enabled')) {
            return '';
        }

        return View::make('honeypot::form-fields');
    }

}
