<?php

namespace Beyondcode\NovaInstaller\Http\Controllers;

use Cache;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class NovaPackagesController
{
	const API_URL = 'https://novapackages.com/api/';

	const CACHE_TIME = 3600;

	/** @var Client */
	protected $client;

	public function __construct()
	{
		$this->client = new Client([
			'base_uri' => static::API_URL
		]);
	}

    /**
     * @param Request $request
     * @return mixed
     */
	public function search(Request $request)
	{
		return Cache::remember('nova_search_'.$request->get('q'), static::CACHE_TIME, function() use ($request) {
			$response = $this->client->request('GET', 'search', [
				'headers' => [
					'Accept' => 'application/json'
				],
				'query' => ['q' => $request->get('q')]
			]);

			return json_decode($response->getBody()->getContents(), true);
		});
	}

    /**
     * @return mixed
     */
	public function recent()
	{
		return Cache::remember('nova_recent', static::CACHE_TIME, function() {
			$response = $this->client->request('GET', 'recent', [
				'headers' => [
					'Accept' => 'application/json'
				]
			]);

			return json_decode($response->getBody()->getContents(), true);
		});
	}
}