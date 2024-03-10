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
        $this->loadDotEnv();

        $this->request = new Request();
        $this->response = new Response();
        $this->route = new Route();
    }

    public function loadDotEnv(): void
    {
        try {
            $dotenv = Dotenv::createImmutable(ROOT_DIR);
            $dotenv->load();
        } catch (\Exception $e) {
            echo 'Please remove the env.example file!';
        }
    }
    
    public function run(): void
    {
        echo $this->route->resolve($this->request, $this->response);
    }
}