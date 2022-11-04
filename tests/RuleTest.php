<?php
namespace WebLogin\LaravelHoneypot\Tests;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Mockery;
use WebLogin\LaravelHoneypot\Rules\Honeypot;
use WebLogin\LaravelHoneypot\View\Components\FormFields;


class RuleTest extends TestCase
{

    public function setUp(): void
    {
        Crypt::shouldReceive('encrypt')
            ->andReturnArg(0);
        Crypt::shouldReceive('decrypt')
            ->andReturnArg(0);

        Lang::spy();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
    }


    /** @test */
    function it_should_pass_if_the_data_are_ok()
    {
        Config::shouldReceive('get')
            ->with('honeypot.enabled')
            ->andReturn(true);
        Config::shouldReceive('get')
            ->with('honeypot.min_seconds')
            ->andReturn(0);

        $honeypot = new Honeypot();

        $name = Str::random();
        $formFields = new FormFields($name);
        $data = $this->validData($formFields);


        $response = $honeypot->passes(null, $data);


        $this->assertTrue($response);
    }


    /** @test */
    function it_should_pass_if_honeypot_is_disabled()
    {
        Config::shouldReceive('get')
            ->with('honeypot.enabled')
            ->andReturn(false);
        Config::shouldReceive('get')
            ->with('honeypot.min_seconds')
            ->andReturn(0);

        $honeypot = new Honeypot();

        $formFields = new FormFields(Str::random());
        $data = $this->validData($formFields);
        unset($data[$formFields->potFieldName]);
        unset($data[$formFields->timeFieldName]);


        $response = $honeypot->passes(null, $data);


        $this->assertTrue($response);
    }


    /** @test */
    function it_should_not_pass_if_honeypot_fields_were_not_submitted()
    {
        Config::shouldReceive('get')
            ->with('honeypot.enabled')
            ->andReturn(true);
        Config::shouldReceive('get')
            ->with('honeypot.min_seconds')
            ->andReturn(0);

        $honeypot = new Honeypot();

        $formFields = new FormFields(Str::random());
        $data = $this->validData($formFields);
        unset($data[$formFields->potFieldName]);
        unset($data[$formFields->timeFieldName]);


        $response = $honeypot->passes(null, $data);
        $honeypot->message();


        $this->assertFalse($response);
        Lang::shouldHaveReceived('has')->with("validation.honeypot.pot");
    }


    /** @test */
    function it_should_not_pass_if_the_pot_field_is_not_submitted()
    {
        Config::shouldReceive('get')
            ->with('honeypot.enabled')
            ->andReturn(true);
        Config::shouldReceive('get')
            ->with('honeypot.min_seconds')
            ->andReturn(0);

        $honeypot = new Honeypot();

        $formFields = new FormFields(Str::random());
        $data = $this->validData($formFields);
        unset($data[$formFields->potFieldName]);


        $response = $honeypot->passes(null, $data);
        $honeypot->message();


        $this->assertFalse($response);
        Lang::shouldHaveReceived('has')->with("validation.honeypot.pot");
    }


    /** @test */
    function it_should_not_pass_if_the_pot_field_is_not_empty()
    {
        Config::shouldReceive('get')
            ->with('honeypot.enabled')
            ->andReturn(true);
        Config::shouldReceive('get')
            ->with('honeypot.min_seconds')
            ->andReturn(0);

        $honeypot = new Honeypot();

        $formFields = new FormFields(Str::random());
        $data = $this->validData($formFields);
        $data[$formFields->potFieldName] = Str::random();


        $response = $honeypot->passes(null, $data);
        $honeypot->message();


        $this->assertFalse($response);
        Lang::shouldHaveReceived('has')->with("validation.honeypot.pot");
    }


    /** @test */
    function it_should_not_pass_if_the_time_field_is_not_submitted()
    {
        Config::shouldReceive('get')
            ->with('honeypot.enabled')
            ->andReturn(true);
        Config::shouldReceive('get')
            ->with('honeypot.min_seconds')
            ->andReturn(0);

        $honeypot = new Honeypot();

        $formFields = new FormFields(Str::random());
        $data = $this->validData($formFields);
        unset($data[$formFields->timeFieldName]);


        $response = $honeypot->passes(null, $data);
        $honeypot->message();


        $this->assertFalse($response);
        Lang::shouldHaveReceived('has')->with("validation.honeypot.pot");
    }


    /** @test */
    function it_should_not_pass_if_the_time_field_is_too_fast()
    {
        Config::shouldReceive('get')
            ->with('honeypot.enabled')
            ->andReturn(true);
        Config::shouldReceive('get')
            ->with('honeypot.min_seconds')
            ->andReturn(5);

        $honeypot = new Honeypot();

        $formFields = new FormFields(Str::random());
        $data = $this->validData($formFields);
        $data[$formFields->timeFieldName] = $data[$formFields->timeFieldName] - 4;


        $response = $honeypot->passes(null, $data);
        $honeypot->message();


        $this->assertFalse($response);
        Lang::shouldHaveReceived('has')->with("validation.honeypot.time");
    }


    private function validData(FormFields $formFields)
    {
        $output = [];
        parse_str("{$formFields->potFieldName}=&{$formFields->timeFieldName}=$formFields->encryptedTime", $output);

        return $output;
    }

}
