<?php

namespace Beyondcode\NovaInstaller\Http\Middleware;

use Beyondcode\NovaInstaller\NovaInstaller;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return resolve(NovaInstaller::class)->authorize($request) ? $next($request) : abort(403);
    }
}
