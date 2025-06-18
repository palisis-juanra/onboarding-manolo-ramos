<?php

namespace Routes;

class Routes 
{
	protected static $routes = [
		'/' => [
			'POST' => [
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
			],
			'POST' => [
				'details' => [
					'controller' => 'handleSessionController',
					'method' => 'POST',
					'action' => 'login',
				],
			],
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
		]
	];

	public static function getRoutes() {
		return self::$routes;
	}
}
