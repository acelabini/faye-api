<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$router->group(['prefix' => 'v1'], function () use ($router) {
    $router->group(['middleware' => ['guest']], function () use ($router) {
        $router->post('/register', 'RegisterController@createQuestion');
        $router->post('/login', 'Authentication\AuthenticationController@login');
    });
    $router->group(['middleware' => ['auth.jwt']], function () use ($router) {
        $router->get('/user', 'Authentication\AuthenticationController@getAuthUser');
        $router->get('/logout', 'Authentication\AuthenticationController@logout');
    });
    $router->group(['middleware' => ['auth.jwt','admin']], function () use ($router) {
        $router->group(['prefix' => 'question'], function () use ($router) {
            $router->get('/sets', 'SetController@getQuestionSets');
            $router->group(['prefix' => 'set'], function () use ($router) {
                $router->post('/', 'SetController@createSet');
                $router->get('/{set_id}', 'SetController@getQuestionSet');
            });
            $router->get('/', 'QuestionController@getQuestions');
            $router->get('/{question_id}', 'QuestionController@getQuestion');
            $router->post('/', 'QuestionController@createQuestion');

        });
    });
});