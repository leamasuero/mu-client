<?php

namespace MercadoUnico\MuClient\Http;


class MuResponse extends Response
{
    /**
     * @var array
     */
    private $body;

    /**
     * @var int
     */
    private $httpStatusCode;

    public function __construct(array $body, int $httpStatusCode)
    {
        $this->body = $body;
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function __get($name)
    {
        return isset($this->body[$name]) ? $this->body[$name] : null;
    }
}