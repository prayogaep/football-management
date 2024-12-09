<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->post('/api/firstUser', 'UserController@store');
$router->post('/api/auth/login', 'AuthController@login');


$router->group(['middleware' => 'jwt'], function () use ($router) {

    $router->group(['prefix' => 'api/auth'], function () use ($router) {
        $router->post('/logout', 'AuthController@logout');
    });
    $router->group(['prefix' => 'api/users'], function () use ($router) {
        $router->get('/', 'UserController@index');
        $router->get('/{id}', 'UserController@show');
        $router->post('/', 'UserController@store');
        $router->put('/{id}', 'UserController@update');
        $router->delete('/{id}', 'UserController@destroy');
    });
    $router->group(['prefix' => 'api/teams'], function () use ($router) {
        $router->get('/', 'TeamController@index');
        $router->get('/{id}', 'TeamController@show');
        $router->post('/', 'TeamController@store');
        $router->post('/{id}', 'TeamController@update');
        $router->delete('/{id}', 'TeamController@destroy');
    });
    $router->group(['prefix' => 'api/players'], function () use ($router) {
        $router->get('/', 'PlayerController@index');
        $router->get('/{id}', 'PlayerController@show');
        $router->post('/', 'PlayerController@store');
        $router->put('/{id}', 'PlayerController@update');
        $router->delete('/{id}', 'PlayerController@destroy');
    });
    $router->group(['prefix' => 'api/schedules'], function () use ($router) {
        $router->get('/', 'ScheduleController@index');
        $router->get('/{id}', 'ScheduleController@show');
        $router->get('/reports/{id}', 'ScheduleController@reports');
        $router->post('/', 'ScheduleController@store');
        $router->put('/{id}', 'ScheduleController@update');
        $router->delete('/{id}', 'ScheduleController@destroy');
    });
    $router->group(['prefix' => 'api/matchResults'], function () use ($router) {
        $router->get('/', 'MatchResultController@index');
        $router->get('/{id}', 'MatchResultController@show');
        $router->post('/', 'MatchResultController@store');
        $router->put('/{id}', 'MatchResultController@update');
        $router->delete('/{id}', 'MatchResultController@destroy');
    });
    $router->group(['prefix' => 'api/goals'], function () use ($router) {
        $router->get('/', 'GoalController@index');
        $router->get('/{id}', 'GoalController@show');
        $router->post('/', 'GoalController@store');
        $router->put('/{id}', 'GoalController@update');
        $router->delete('/{id}', 'GoalController@destroy');
    });
});
