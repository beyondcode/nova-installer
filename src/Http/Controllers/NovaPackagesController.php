<?php

namespace Beyondcode\NovaInstaller\Http\Controllers;

use Cache;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Beyondcode\NovaInstaller\Utils\NovaPackagesFinder;

class NovaPackagesController
{
    /**
     * The Base novapackage.com api url.
     *
     * @var string
     */
    const API_URL = 'https://novapackages.com/api/';

    /**
     * Cache validity.
     *
     * @var int
     */
    const CACHE_TIME = 3600;

    /**
     * The Client implementation.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The finder.
     *
     * @var \Beyondcode\NovaInstaller\Utils\NovaPackagesFinder
     */
    protected $finder;

    /**
     * Create a new controller instance.
     *
     * @param  \Beyondcode\NovaInstaller\Utils\NovaPackagesFinder  $finder
     * @return void
     */
    public function __construct(NovaPackagesFinder $finder)
    {
        $this->client = new Client([
            'base_uri' => static::API_URL,
            'verify' => false,
        ]);
        
        $this->finder = $finder;
    }

    /**
     * Currently installed nova-related packages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function installed(Request $request)
    {
        return $this->finder->all();
    }

    /**
     * Search and cache.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function search(Request $request)
    {
        return Cache::remember('nova_search_'.$request->get('q'), static::CACHE_TIME, function () use ($request) {
            $response = $this->client->request('GET', 'search', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'query' => ['q' => $request->get('q')],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        });
    }

    /**
     * Recent packages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function recent()
    {
        return Cache::remember('nova_recent', static::CACHE_TIME, function () {
            $response = $this->client->request('GET', 'recent', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        });
    }

    /**
     * Popular packages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function popular()
    {
        return Cache::remember('nova_popular', static::CACHE_TIME, function () {
            $response = $this->client->request('GET', 'popular', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        });
    }
}
