<?php
namespace WebLogin\LaravelHoneypot\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;


class Honeypot implements ImplicitRule
{

    private $message = '';


    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!Config::get('honeypot.enabled')) {
            return true;
        }

        if (!is_array($value) || count($value) !== 2) {
            $this->message = 'pot';
            return false;
        }

        $pot = Arr::first($value, fn($value, $key) => Str::startsWith($key, 'p'), 'missing');
        if (!$this->validatePotField($pot)) {
            $this->message = 'pot';
            return false;
        }

        $time = Arr::first($value, fn($value, $key) => Str::startsWith($key, 't'));
        if (!$this->validateTimeField($time)) {
            $this->message = 'time';
            return false;
        }

        return $this->message === '';
    }


    /**
     * @return string
     */
    public function message()
    {
        if (!$this->message) {
            return '';
        }

        if (Lang::has("validation.honeypot.$this->message")) {
            return Lang::get("validation.honeypot.$this->message");
        }

        return Lang::get("honeypot::validation.$this->message");
    }


    /**
     * @return bool
     */
    private function validatePotField($value)
    {
        return $value === '' || is_null($value);
    }


    /**
     * @return bool
     */
    private function validateTimeField($value)
    {
        if (!$value) {
            return false;
        }

        $time = Crypt::decrypt($value);
        $maxTime = $time + Config::get('honeypot.min_seconds');

        return (is_numeric($time) && (time() > $maxTime));
    }

}
