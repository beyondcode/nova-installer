<?php

namespace Beyondcode\NovaInstaller\Http\Controllers;

use Laravel\Nova\Nova;
use Illuminate\Http\Request;
use Beyondcode\NovaInstaller\Jobs\InstallPackage;

class InstallerController
{
    public function tools()
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
        dispatch(new InstallPackage(
            $request->package,
            $request->packageName,
            parse_url($request->url()),
            $request->cookies
        ));

        return response(null, 204);
    }
}
