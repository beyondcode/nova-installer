<?php

namespace Beyondcode\NovaInstaller\Http\Controllers;

use Laravel\Nova\Nova;
use Illuminate\Http\Request;
use Illuminate\Foundation\PackageManifest;
use Beyondcode\NovaInstaller\Jobs\InstallPackage;

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

    public function install(Request $request)
    {
        [$package, $packageName, $url, $cookies] = $this->getJobParams($request);

        dispatch(new InstallPackage($package, $packageName, $url, $cookies));

        return response(null, 204);
    }

    public function getJobParams(Request $request)
    {
        $package = $request->package;
        $packageName = $request->packageName;
        $url = parse_url($request->url());
        $cookies = $request->cookies;

        return [$package, $packageName, $url, $cookies];
    }
}
