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
    $router->get('published/{id}', 'Management\QuestionController@getProcessedData');
    $router->group(['middleware' => ['secret']], function () use ($router) {
        $router->get('/cloud', 'SummaryController@wordCloud');
        $router->post('/performLDA/{setId?}', 'SummaryController@getLDA');
        $router->group(['prefix' => 'question'], function () use ($router) {
            $router->get('/{order}/{setId?}', 'QuestionnaireController@getQuestionnaire');
        });
        $router->group(['prefix' => 'set'], function () use ($router) {
            $router->get('/', 'QuestionnaireController@getDefaultSet');
            $router->get('/{setId}', 'QuestionnaireController@getSet');
        });
        $router->group(['prefix' => 'sets'], function () use ($router) {
            $router->get('/', 'QuestionnaireController@getSets');
        });
        $router->group(['prefix' => 'answer'], function () use ($router) {
            $router->get('/summary/{device?}/{order?}/{setId?}', 'SummaryController@summarize');
            $router->get('/{order}', 'AnswerController@getAnswer');
            $router->post('/{order?}/{setId?}', 'AnswerController@answer');
        });
        $router->group(['middleware' => ['guest']], function () use ($router) {
            $router->post('/register', 'Authentication\RegisterController@createQuestion');
            $router->post('/login', 'Authentication\AuthenticationController@login');
        });
        $router->group(['prefix' => 'incident-report'], function () use ($router) {
            $router->post('/', 'Management\HazardController@reportIncident');
            $router->get('/barangays', 'Management\LocationController@barangays');
        });
        $router->get('/dashboard', 'Management\UserController@dashboard');
        $router->post('/sign-up', 'Management\UserController@signUp');
    });
   // $router->group(['middleware' => ['auth.jwt']], function () use ($router) {
        $router->get('/user', 'Authentication\AuthenticationController@getAuthUser');
        $router->get('/logout', 'Authentication\AuthenticationController@logout');
        $router->group(['prefix' => 'profile'], function () use ($router) {
            $router->get('/', 'HomeController@profile');
        });
        //$router->group(['middleware' => ['admin']], function () use ($router) {
            $router->group(['prefix' => 'management'], function () use ($router) {
                $router->post('/summary/reports', 'SummaryController@reportSummary');
                $router->group(['prefix' => 'published'], function () use ($router) {
                    $router->get('/', 'Management\QuestionController@getPublishedData');
                    $router->get('/{id}', 'Management\QuestionController@getProcessedData');
                    $router->post('/{id}', 'Management\QuestionController@updateProcessedData');
                    $router->patch('/{id}', 'Management\QuestionController@patchPublishedData');
                    $router->delete('/{id}', 'Management\QuestionController@deletePublishedData');
                });
                $router->group(['prefix' => 'answers'], function () use ($router) {
                    $router->get('/', 'Management\AnswerController@getAnswerList');
                    $router->post('/summary/{device?}/{order?}', 'SummaryController@summary');
                    $router->post('/publish-data', 'SummaryController@publish');
                    $router->post('/clean-data/{raw?}', 'SummaryController@cleanData');
                    $router->post('/enumerations', 'SummaryController@showEnumerations');
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
                $router->group(['prefix' => 'users'], function () use ($router) {
                    $router->get('/roles', 'Management\UserController@getRoles');
                    $router->get('/', 'Management\UserController@getUsers');
                    $router->post('/', 'Management\UserController@addUser');
                    $router->get('/{userId}', 'Management\UserController@viewUser');
                    $router->patch('/{userId}', 'Management\UserController@editUser');
                });
                $router->group(['prefix' => 'incident-report'], function () use ($router) {
                    $router->patch('/{id}', 'Management\HazardController@updateIncidentStatus');
                    $router->get('/', 'Management\HazardController@getReportIncidents');
                    $router->get('/raw', 'SummaryController@rawIncident');
                });
            });
        //});
    //});
});
