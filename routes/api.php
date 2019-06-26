<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$router->group(['prefix' => 'v1'], function () use ($router) {
    $router->group(['middleware' => ['secret']], function () use ($router) {
        $router->get('/cloud', 'SummaryController@wordCloud');
        $router->group(['prefix' => 'question'], function () use ($router) {
            $router->get('/{order}', 'QuestionnaireController@getQuestionnaire');
        });
        $router->group(['prefix' => 'set'], function () use ($router) {
            $router->get('/', 'QuestionnaireController@getDefaultSet');
        });
        $router->group(['prefix' => 'answer'], function () use ($router) {
            $router->get('/summary/{device?}/{order?}', 'SummaryController@summary');
            $router->get('/{order}', 'AnswerController@getAnswer');
            $router->post('/{order?}', 'AnswerController@answer');
        });
        $router->group(['middleware' => ['guest']], function () use ($router) {
            $router->post('/register', 'Authentication\RegisterController@createQuestion');
            $router->post('/login', 'Authentication\AuthenticationController@login');
        });
    });
    $router->group(['middleware' => ['auth.jwt']], function () use ($router) {
        $router->get('/user', 'Authentication\AuthenticationController@getAuthUser');
        $router->get('/logout', 'Authentication\AuthenticationController@logout');
        $router->group(['middleware' => ['admin']], function () use ($router) {
            $router->group(['prefix' => 'management'], function () use ($router) {
                $router->group(['prefix' => 'answers'], function () use ($router) {
                    $router->get('/', 'Management\AnswerController@getAnswerList');
                    $router->get('/{device_address}/{set_id}', 'Management\AnswerController@getAnswer');
                });
                $router->group(['prefix' => 'locations'], function () use ($router) {
                    $router->get('/', 'Management\LocationController@getLocations');
                    $router->group(['prefix' => 'hazard-prone'], function () use ($router) {
                        $router->get('/', 'Management\LocationController@getHazardsLocations');
                        $router->get('/{hazardLocationId}', 'Management\LocationController@viewHazardLocation');
                        $router->post('/', 'Management\LocationController@addHazardLocation');
                        $router->patch('/{hazardLocationId}', 'Management\LocationController@editHazardLocation');
                        $router->delete('/{hazardLocationId}', 'Management\LocationController@deleteHazardLocation');
                    });
                });
                $router->group(['prefix' => 'hazards'], function () use ($router) {
                    $router->get('/', 'Management\HazardController@getHazards');
                    $router->post('/', 'Management\HazardController@addHazard');
                    $router->get('/{hazardId}', 'Management\HazardController@viewHazard');
                    $router->patch('/{hazardId}', 'Management\HazardController@editHazard');
                    $router->delete('/{hazardId}', 'Management\HazardController@deleteHazard');
                });
                $router->group(['prefix' => 'question'], function () use ($router) {
                    $router->get('/sets', 'Management\SetController@getQuestionSets');
                    $router->group(['prefix' => 'set'], function () use ($router) {
                        $router->post('/', 'Management\SetController@createSet');
                        $router->get('/{set_id}', 'Management\SetController@getQuestionSet');
                        $router->patch('/{set_id}', 'Management\SetController@patchQuestionSet');
                        $router->delete('/{set_id}', 'Management\SetController@deleteQuestionSet');
                        $router->patch('/status/{set_id}', 'Management\SetController@patchQuestionSetStatus');
                    });
                    $router->get('/', 'Management\QuestionController@getQuestions');
                    $router->post('/', 'Management\QuestionController@createQuestion');
                    $router->get('/{question_id}', 'Management\QuestionController@getQuestion');
                    $router->patch('/{question_id}', 'Management\QuestionController@patchQuestion');
                    $router->delete('/{question_id}', 'Management\QuestionController@deleteQuestion');
                });
            });
        });
    });
});