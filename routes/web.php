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

    $router->group(['prefix' => 'question'], function () use ($router) {
        $router->get('/{order}', 'QuestionnaireController@getQuestionnaire');
    });
    $router->group(['prefix' => 'answer'], function () use ($router) {
        $router->get('/{order}', 'AnswerController@getAnswer');
        $router->post('/', 'AnswerController@answer');
    });

    $router->group(['middleware' => ['guest']], function () use ($router) {
        $router->post('/register', 'Authentication\RegisterController@createQuestion');
        $router->post('/login', 'Authentication\AuthenticationController@login');
    });
    $router->group(['middleware' => ['auth.jwt']], function () use ($router) {
        $router->get('/user', 'Authentication\AuthenticationController@getAuthUser');
        $router->get('/logout', 'Authentication\AuthenticationController@logout');
    });
    $router->group(['middleware' => ['auth.jwt','admin']], function () use ($router) {
        $router->group(['prefix' => 'management'], function () use ($router) {
            $router->group(['prefix' => 'question'], function () use ($router) {
                $router->get('/sets', 'Management\SetController@getQuestionSets');
                $router->group(['prefix' => 'set'], function () use ($router) {
                    $router->post('/', 'Management\SetController@createSet');
                    $router->get('/{set_id}', 'Management\SetController@getQuestionSet');
                    $router->patch('/{set_id}', 'Management\SetController@patchQuestionSet');
                    $router->delete('/{set_id}', 'Management\SetController@deleteQuestionSet');
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