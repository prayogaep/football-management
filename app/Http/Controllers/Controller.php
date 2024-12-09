<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $responseFormatter;
    
    public function __construct()
    {
        $this->responseFormatter = new ResponseFormatter();
    }
    public function handleException(callable $callback)
    {
        try {
            $result = $callback();
            return response()->json($result, $this->responseFormatter->statusCodeSuccess);
        } catch (\Throwable $th) {
            $message = env("APP_ENV") == 'local' ? $th->getMessage() : 'Internal Server Error';
            return response()->json( $this->responseFormatter->format(false, $message), $this->responseFormatter->statusCodeInternalServerError);
        }
    }
    public function returnNotFound($messange)
    {
        return $this->responseFormatter->format(false, $messange . ' Data Not Found', null, $this->responseFormatter->statusCodeNotFound);
    }
    public function returnErrorRequest($validation)
    {
        return response()->json(
            $this->responseFormatter->format(false, 'Validation Error', $validation, $this->responseFormatter->statusCodeBadRequest),
            $this->responseFormatter->statusCodeBadRequest
        );
    }
    public function returnErrorBadRequest($message) {
        return $this->responseFormatter->format(false, $message, null, $this->responseFormatter->statusCodeBadRequest);
    }
    public function returnErrorInvalidRequest($message) {
        return $this->responseFormatter->format(false, $message, null, $this->responseFormatter->statusCodeInvalidRequest);
    }
    public function returnInternalServerError($message) {
        return $this->responseFormatter->format(false, $message, null, $this->responseFormatter->statusCodeInternalServerError);
    }
}
