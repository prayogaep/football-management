<?php


namespace App\Helpers;
use Illuminate\Support\Collection;

class ResponseFormatter
{
    public $statusCodeSuccess                = 200;
    public $statusCodeBadRequest             = 400;
    public $statusCodeInvalidRequest         = 401;
    public $statusCodeNotFound               = 404;
    public $statusCodeInternalServerError    = 500;
    public static function format($status, $message, $data = null, $statusCode = 200)
    {
        $format = [
            'statusCode'    => $statusCode,
            'status'        => $status,
            'message'       => $message,
            'data'          => $data
        ];
        if (is_array($data) || $data instanceof Collection ) {
            $format['totalRows'] = is_array($data) || $data instanceof Collection ? count($data) : null;
        }
        return $format;
    }
    public static function error($status, $data)
    {
        return $data;
    }
}
