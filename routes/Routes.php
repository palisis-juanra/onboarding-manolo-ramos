<?php

namespace Routes;

class Routes 
{
	protected static $routes = [
		'/' => [
			'details' => [
				'controller' => 'handleSessionController',
				'method' => 'GET',
				'action' => 'login',
			],
		],
		'/login' => [
			'details' => [
				'controller' => 'handleSessionController',
				'method' => 'GET',
				'action' => 'login',
			],
		],
		'/login' => [
			'details' => [
				'controller' => 'handleSessionController',
				'method' => 'POST',
				'action' => 'login',
			],
		],
		'/login/action' => [
			'details' => [
				'controller' => 'handleSessionController',
				'method' => 'POST',
				'action' => 'login',
			],
		],
		'/logout' => [
			'details' => [
				'controller' => 'handleSessionController',
				'method' => 'GET',
				'action' => 'logout',
			],
		]
	];

	public static function getRoutes() {
		return self::$routes;
	}
}
