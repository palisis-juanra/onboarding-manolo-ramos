<?php

namespace Routes;

class Routes 
{
	protected static $routes = [
		'/' => [
			'GET' => [
				'controller' => 'handleSessionController',
				'method' => 'GET',
				'action' => 'login',
			],
		],
		'/login' => [
			'GET' => [
				'controller' => 'handleSessionController',
				'method' => 'GET',
				'action' => 'login',
			]
		],
		'/login/loginAction' => [
			'POST' => [
				'controller' => 'handleSessionController',
				'method' => 'POST',
				'action' => 'login',
			],
		],
		'/logout' => [
			'GET' => [
				'controller' => 'handleSessionController',
				'method' => 'GET',
				'action' => 'logout',
			],
		],
		'/dashboard' => [
			'GET' => [
				'controller' => 'handleChannelListController',
				'method' => 'GET',
				'action' => 'showChannelList',
			]
		],
		'/dashboard/pickChannel' => [
			'POST' => [
				'controller' => 'handleChannelListController',
				'method' => 'POST',
				'action' => 'pickChannel',
			]
		],
	];

	public static function getRoutes() {
		return self::$routes;
	}
}
