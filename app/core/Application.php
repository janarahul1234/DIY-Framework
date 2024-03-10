<?php

namespace App\core;

use Dotenv\Dotenv;
use App\core\Request;
use App\core\Response;
use App\core\Route;

class Application
{
    public static Application $app;

    public Request $request;
    public Response $response;
    public Route $route;

    public function __construct()
    {
        self::$app = $this;

        $dotenv = Dotenv::createImmutable(ROOT_DIR);
        $dotenv->load();

        $this->request = new Request();
        $this->response = new Response();
        $this->route = new Route();
    }
    
    public function run(): void
    {
        echo $this->route->resolve($this->request, $this->response);
    }
}