<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Response;
use InvalidArgumentException;
use App\Exceptions\ApiException;
use App\Http\Responses\ApiResponse;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiController extends Controller
{
    protected $response;

    public function __construct()
    {
        $this->response = new ApiResponse();
    }

    public function runWithExceptionHandling($callback)
    {
        try {
            $callback();
            $response = response()->json($this->response->getData());
            if($cookie = $this->response->getCookie()) {
                $response->cookie($cookie);
            }

            return $response;
        } catch (ApiException $e) {
            throw $e;
        } catch(ValidationException $e) {
            throw new ApiException(
                $e->getMessage(),
                $e->getResponse() ? $e->getResponse()->getStatusCode() : Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->errors()
            );
        } catch(ModelNotFoundException $e) {
            throw new ApiException('Record not found.', Response::HTTP_NOT_FOUND);
        } catch(GuzzleException $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        } catch(InvalidArgumentException $e) {
            throw new ApiException($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (JWTException $e) {
            throw new ApiException($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        }
    }
}