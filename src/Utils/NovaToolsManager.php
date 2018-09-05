<?php

namespace Beyondcode\NovaInstaller\Utils;

use GuzzleHttp\Client;
use Laravel\Nova\Nova;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Contracts\Encryption\Encrypter;

class NovaToolsManager
{
    public $newPackage;
    protected $scripts;
    protected $styles;

    public function getCurrentTools()
    {
        $tools = [];

        collect(Nova::$tools)->map(function ($tool) use (&$tools) {
            $tools[get_class($tool)] = (string)$tool->renderNavigation();
        });

        $this->populateScriptsAndStyles();

        return $tools;
    }

    public function getNewToolsScriptsAndStyles($url, $cookies, $tools)
    {
        $updatedTools = $this->getUpdatedTools($url, $cookies);

        $newTools = collect($updatedTools->tools)->diff($tools)->toArray();
        $newScripts = collect($updatedTools->scripts)->diff($this->scripts)->toArray();
        $newStyles = collect($updatedTools->styles)->diff($this->styles)->toArray();

        return [
            'tools' => $newTools,
            'scripts' => $newScripts,
            'styles' => $newStyles
        ];
    }

    protected function populateScriptsAndStyles()
    {
        $this->scripts = Nova::$scripts;
        $this->styles = Nova::$styles;
    }

    protected function registerTools()
    {
        $manipulator = new \Beyondcode\NovaInstaller\ServiceProviderManipulator($this->newPackage);
        $manipulator->manipulate(\App\Providers\NovaServiceProvider::class);
    }

    protected function getUpdatedTools($url, $cookies)
    {
        $this->registerTools();

        logger('updatedTools');
        logger($url);

        $encrypter = app(Encrypter::class);

        $client = new Client([
            'verify' => false
        ]);

        $cookies = collect($cookies)->map(function ($cookie) use ($encrypter) {
            return $encrypter->encrypt($cookie, false);
        })->toArray();

        $response = $client->request('GET', url('/nova-vendor/beyondcode/nova-installer/tools'), [
            'cookies' => CookieJar::fromArray($cookies, $url['host'])
        ]);

        return json_decode($response->getBody()->getContents());
    }
}
