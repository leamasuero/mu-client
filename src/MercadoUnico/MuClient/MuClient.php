<?php

namespace MercadoUnico\MuClient;

use MercadoUnico\MuClient\Exceptions\MuErrorRequestException;
use MercadoUnico\MuClient\Http\MuResponse;
use MercadoUnico\MuClient\Util\CurlRestClient;

class MuClient
{

    const VERSION = "1.0.0";
    const API_BASE_URL = "https://api.mercado-unico.com";
    const SANDBOX_API_BASE_URL = "https://api.prop44.info";

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var bool
     */
    private $sandboxMode;

    /**
     * MuClient constructor.
     * @param string $username
     * @param string $password
     * @param bool|null $sandboxMode
     */
    function __construct(string $username, string $password, ?bool $sandboxMode = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->sandboxMode = $sandboxMode;
    }

    /**
     * @return MuClient
     */
    public function connect(): self
    {
        $this->token = $this->getAccessToken($this->username, $this->password);
        return $this;
    }

    /**
     * @return string
     */
    private function getApiBaseUrl(): string
    {
        return $this->sandboxMode ? self::SANDBOX_API_BASE_URL : self::API_BASE_URL;
    }

    /**
     * @param $username
     * @param $password
     * @return string
     */
    private function getAccessToken(string $username, string $password): string
    {
        return base64_encode($username . ':' . $password);
    }

    /**
     * @param string $id
     * @return MuResponse
     * @throws Exceptions\MuException
     */
    public function getPropiedad(string $id): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->get("/propiedades/{$id}");
    }

    /**
     * @param array $datosPropiedad
     * @return MuResponse
     * @throws Exceptions\JsonErrorException
     * @throws Exceptions\MuException
     */
    public function crearPropiedad(array $datosPropiedad): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->post('/propiedades', $datosPropiedad);
    }

    /**
     * @param string $id
     * @param array $datosPropiedad
     * @return MuResponse
     * @throws Exceptions\JsonErrorException
     * @throws Exceptions\MuException
     * @throws MuErrorRequestException
     */
    public function editarPropiedad(string $id, array $datosPropiedad): MuResponse
    {
        if (!$id) {
            throw new MuErrorRequestException("Debe indicar el id de la propiedad que desea operar.");
        }

        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->patch("/propiedades/{$id}", $datosPropiedad);
    }

    /**
     * @param string $id
     * @return MuResponse
     * @throws Exceptions\MuException
     * @throws MuErrorRequestException
     */
    public function eliminarPropiedad(string $id): MuResponse
    {
        if (!$id) {
            throw new MuErrorRequestException("Debe indicar el id de la propiedad que desea operar.");
        }

        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->delete("/propiedades/{$id}");
    }
}
