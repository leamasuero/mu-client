<?php

namespace MercadoUnico\MuClient\Exceptions;

use MercadoUnico\MuClient\Http\Response;

class JsonErrorException extends MuException
{
    /**
     * @var int
     */
    private $jsonErrorCode;

    public function __construct(int $jsonErrorCode, array $data)
    {
        parent::__construct("Json error", Response::HTTP_BAD_REQUEST);
        $this->jsonErrorCode = $jsonErrorCode;
        $this->setData($data);
    }

    /**
     * @return int
     */
    public function getJsonErrorCode(): int
    {
        return $this->jsonErrorCode;
    }
}
