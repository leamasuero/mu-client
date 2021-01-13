<?php

namespace MercadoUnico\MuClient\Exceptions;


class MuErrorResponseException extends MuException
{

    public function __construct($errorCodeResponse, int $httpStatusCode)
    {
        parent::__construct($errorCodeResponse['message'] ?? 'MuErrorResponseException', $httpStatusCode);
        $this->setData($errorCodeResponse);
    }
}