<?php

namespace Beyondcode\NovaInstaller\Http\Controllers;

use Illuminate\Foundation\PackageManifest;
use Beyondcode\NovaInstaller\Composer;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;
use Laravel\Nova\Nova;

class InstallerController
{
	public function tools(Request $request, PackageManifest $manifest)
	{
		$tools = [];

	    collect(Nova::$tools)->map(function ($tool) use (&$tools) {
	        $tools[get_class($tool)] = (string)$tool->renderNavigation();
	    });

	    return [
	        'tools' => $tools,
	        'scripts' => Nova::$scripts,
	        'styles' => Nova::$styles
	    ];
	}

	protected function getCurrentTools()
	{
		$tools = [];

	    collect(Nova::$tools)->map(function ($tool) use (&$tools) {
	        $tools[get_class($tool)] = (string)$tool->renderNavigation();
	    });

	    return $tools;
	}

	protected function getUpdatedTools(Request $request)
	{
	    $encrypter = app(Encrypter::class);

	    $url = parse_url($request->url());

	    $client = new Client();

	    $cookies = collect($request->cookies)->map(function ($cookie) use ($encrypter) {
	        return $encrypter->encrypt($cookie, false);
	    })->toArray();

	    $response = $client->request('GET', url('/nova-vendor/beyondcode/nova-installer/tools'), [
	        'cookies' => CookieJar::fromArray($cookies, $url['host'])
	    ]);

	    return json_decode($response->getBody()->getContents());
	}

	public function install(Request $request, Composer $composer)
	{
		$tools = $this->getCurrentTools();
	    $scripts = Nova::$scripts;
	    $styles = Nova::$styles;

	    $composer->install($request->get('package'), function ($type, $data) {});

	    $this->registerTools();

	    $updatedTools = $this->getUpdatedTools($request);

	    $newTools = collect($updatedTools->tools)->diff($tools)->toArray();
	    $newScripts = collect($updatedTools->scripts)->diff($scripts)->toArray();
	    $newStyles = collect($updatedTools->styles)->diff($styles)->toArray();

	    return [
	        'tools' => $newTools,
	        'scripts' => $newScripts,
	        'styles' => $newStyles
	    ];
	}

    protected function registerTools()
    {
        $manipulator = new \Beyondcode\NovaInstaller\ServiceProviderManupilator();
        $manipulator->manipulate(\App\Providers\NovaServiceProvider::class);
    }
}