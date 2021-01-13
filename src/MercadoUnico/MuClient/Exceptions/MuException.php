<?php

namespace MercadoUnico\MuClient\Exceptions;


class MuException extends \Exception
{

    /**
     * @var array
     */
    private $data;

    public function __construct(string $message, int $code = 500)
    {
        parent::__construct($message, $code);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data = [])
    {
        $this->data = $data;
    }
}