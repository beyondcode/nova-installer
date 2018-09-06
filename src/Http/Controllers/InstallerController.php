<?php

namespace Beyondcode\NovaInstaller\Http\Controllers;

use Laravel\Nova\Nova;
use Illuminate\Http\Request;
use Beyondcode\NovaInstaller\Jobs\RemovePackage;
use Beyondcode\NovaInstaller\Jobs\InstallPackage;

class InstallerController
{
    /**
     * List the tools, scripts and styles available.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tools(Request $request)
    {
        $tools = [];

        collect(Nova::$tools)->map(function ($tool) use (&$tools) {
            $tools[get_class($tool)] = (string) $tool->renderNavigation();
        });

        return [
            'tools' => $tools,
            'scripts' => Nova::$scripts,
            'styles' => Nova::$styles,
        ];
    }

    /**
     * Start the installation process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Start the removal process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request)
    {
        dispatch(new RemovePackage(
            $request->package,
            $request->packageName,
            parse_url($request->url()),
            $request->cookies
        ));

        return response(null, 204);
    }

    /**
     * Start the update process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        dispatch(new RemovePackage(
            $request->package,
            $request->packageName,
            parse_url($request->url()),
            $request->cookies
        ));

        return response(null, 204);
    }
}
