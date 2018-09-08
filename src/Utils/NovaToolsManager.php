<?php

namespace Beyondcode\NovaInstaller\Utils;

use GuzzleHttp\Client;
use Laravel\Nova\Nova;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Contracts\Encryption\Encrypter;
use Beyondcode\NovaInstaller\Utils\Manipulation\ManifestManipulator;
use Beyondcode\NovaInstaller\Utils\Manipulation\ServiceProviderManipulator;

class NovaToolsManager
{
    /**
     * Package composer name.
     *
     * @var string
     */
    protected $package;

    /**
     * Nova scripts.
     *
     * @var array
     */
    protected $scripts;

    /**
     * Nova styles.
     *
     * @var string
     */
    protected $styles;

    /**
     * The Service Provider Manipulator implementation.
     *
     * @var Beyondcode\NovaInstaller\Utils\Manipulation\ServiceProviderManipulator
     */
    protected $serviceProviderManipulator;

    /**
     * The Manifest file manipulator.
     *
     * @var Beyondcode\NovaInstaller\Utils\Manipulation\ManifestManipulator
     */
    protected $manifestManipuator;

    /**
     * The Service Provider class.
     *
     * @var string
     */
    protected $serviceProvider;

    /**
     * Create a new Tools Manager object.
     *
     * @param  Beyondcode\NovaInstaller\Utils\Manipulation\ServiceProviderManipulator $serviceProviderManipulator
     * @param  Beyondcode\NovaInstaller\Utils\Manipulation\ManifestManipulator $manifestManipuator
     * @return void
     */
    public function __construct(ServiceProviderManipulator $serviceProviderManipulator, ManifestManipulator $manifestManipuator)
    {
        $this->serviceProviderManipulator = $serviceProviderManipulator;
        $this->manifestManipuator = $manifestManipuator;

        $this->serviceProvider = config('nova-installer.provider');
    }

    /**
     * Set the package that this instance will Manage.
     *
     * @param  string $package
     *
     * @return Beyondcode\NovaInstaller\Utils\NovaToolsManager
     */
    public function setPackage($package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * Get the tools avaible to Nova.
     *
     * @return Illuminate\Support\Collection
     */
    public function getCurrentTools()
    {
        $tools = [];

        collect(Nova::$tools)->map(function ($tool) use (&$tools) {
            $tools[get_class($tool)] = (string) $tool->renderNavigation();
        });

        $this->populateScriptsAndStyles();

        return $tools;
    }

    /**
     * Register current package installables in NovaServiceProvider.
     *
     * @return void
     */
    public function registerTools()
    {
        $this->serviceProviderManipulator->setPackage($this->package);
        $this->serviceProviderManipulator->addTo($this->serviceProvider);
    }

    /**
     * Remove current package installables from NovaServiceProvider and Manifest.
     *
     * @return void
     */
    public function unregisterTools()
    {
        $this->manifestManipuator->removeFromManifest($this->package);
        $this->serviceProviderManipulator->setPackage($this->package);
        $this->serviceProviderManipulator->removeFrom($this->serviceProvider);
    }

    /**
     * Get the newly refreshed tools, scripts and styles after the package action.
     *
     * @return array
     */
    public function getNewToolsScriptsAndStyles($url, $cookies, $tools)
    {
        $updatedTools = $this->getUpdatedTools($url, $cookies);

        return [
            'tools' => collect($updatedTools->tools)->diff($tools)->toArray(),
            'scripts' => collect($updatedTools->scripts)->diff($this->scripts)->toArray(),
            'styles' => collect($updatedTools->styles)->diff($this->styles)->toArray(),
        ];
    }

    /**
     * Get the nova scripts and styles into the current instance.
     *
     * @return void
     */
    protected function populateScriptsAndStyles()
    {
        $this->scripts = Nova::$scripts;
        $this->styles = Nova::$styles;
    }

    /**
     * Perform the http call to get the new updated version of nova scripts, tools, and styles.
     *
     * @param string $url
     * @param array $cookies
     * @return mixed
     */
    protected function getUpdatedTools($url, $cookies)
    {
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
