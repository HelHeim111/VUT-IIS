<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
        $router->addRoute('signin', 'Signin:default');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
		$router->addRoute('signup', 'Signup:default');
		return $router;
	}
}
