<?php

namespace Beyondcode\NovaInstaller\Utils;

use GuzzleHttp\Client;
use Laravel\Nova\Nova;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Contracts\Encryption\Encrypter;
use Beyondcode\NovaInstaller\Utils\Manipulation\ServiceProviderManipulator;

class NovaToolsManager
{
    protected $newPackage;
    protected $scripts;
    protected $styles;
    protected $serviceProviderManipulator;

    protected $serviceProvider = \App\Providers\NovaServiceProvider::class;

    public function __construct(ServiceProviderManipulator $serviceProviderManipulator)
    {
        $this->serviceProviderManipulator = $serviceProviderManipulator;
    }

    public function setPackage($package)
    {
        $this->newPackage = $package;
    }

    public function getCurrentTools()
    {
        $tools = [];

        collect(Nova::$tools)->map(function ($tool) use (&$tools) {
            $tools[get_class($tool)] = (string) $tool->renderNavigation();
        });

        $this->populateScriptsAndStyles();

        return $tools;
    }

    protected function populateScriptsAndStyles()
    {
        $this->scripts = Nova::$scripts;
        $this->styles = Nova::$styles;
    }

    public function getNewToolsScriptsAndStyles($url, $cookies, $tools)
    {
        $updatedTools = $this->getUpdatedTools($url, $cookies);

        return [
            'tools' => collect($updatedTools->tools)->diff($tools)->toArray(),
            'scripts' => collect($updatedTools->scripts)->diff($this->scripts)->toArray(),
            'styles' => collect($updatedTools->styles)->diff($this->styles)->toArray(),
        ];
    }

    protected function registerTools()
    {
        $this->serviceProviderManipulator->setPackage($this->newPackage);
        $this->serviceProviderManipulator->manipulate($this->serviceProvider);
    }

    protected function getUpdatedTools($url, $cookies)
    {
        $this->registerTools();

        $encrypter = app(Encrypter::class);

        $client = new Client([
            'verify' => false,
        ]);

        $cookies = collect($cookies)->map(function ($cookie) use ($encrypter) {
            return $encrypter->encrypt($cookie, false);
        })->toArray();

        $response = $client->request('GET', url('/nova-vendor/beyondcode/nova-installer/tools'), [
            'cookies' => CookieJar::fromArray($cookies, $url['host']),
        ]);

        return json_decode($response->getBody()->getContents());
    }
}
