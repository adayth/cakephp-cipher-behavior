<?php
use Cake\Routing\Router;

Router::plugin('CipherBehavior', function ($routes) {
    $routes->fallbacks('InflectedRoute');
});
