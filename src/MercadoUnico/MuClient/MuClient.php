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
     * @return bool
     */
    public function isSandboxMode(): bool
    {
        return $this->sandboxMode;
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
    public function findPropiedad(string $id): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->get("/propiedades/{$id}");
    }

    private function httpBuildQuery(array $parametros)
    {
        $excepciones = ['scopes'];
        $vacios = array_filter($parametros, function ($v, $k) use ($excepciones) {
            return in_array($k, $excepciones) && empty($v);
        }, ARRAY_FILTER_USE_BOTH);

        $queryString = http_build_query($parametros);

        foreach ($vacios as $k => $v) {
            $queryString .= "&{$k}[]=";
        }

        return $queryString;
    }


    /**
     * @param array $queryParameters
     * @return MuResponse
     * @throws Exceptions\MuErrorResponseException
     * @throws Exceptions\MuException
     */
    public function getPropiedades(array $queryParameters = []): MuResponse
    {
        $queryString = $this->httpBuildQuery($queryParameters);

        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->get("/propiedades?$queryString");
    }


    /**
     * @param array $datosPropiedad
     * @return MuResponse
     * @throws Exceptions\JsonErrorException
     * @throws Exceptions\MuException
     */
    public function storePropiedad(array $datosPropiedad): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->post('/propiedades', $datosPropiedad);
    }

    /**
     * @param array $datosAlerta
     * @return MuResponse
     * @throws Exceptions\JsonErrorException
     * @throws Exceptions\MuException
     */
    public function storeAlerta(array $datosAlerta): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->post('/alertas', $datosAlerta);
    }

    /**
     * @param string $id
     * @param array $datosPropiedad
     * @return MuResponse
     * @throws Exceptions\JsonErrorException
     * @throws Exceptions\MuException
     * @throws MuErrorRequestException
     */
    public function updatePropiedad(string $id, array $datosPropiedad): MuResponse
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
     * @param array $scopes
     * @return MuResponse
     * @throws Exceptions\JsonErrorException
     * @throws Exceptions\MuErrorResponseException
     * @throws Exceptions\MuException
     * @throws MuErrorRequestException
     */
    public function updatePropiedadScopes(string $id, array $scopes): MuResponse
    {
        if (!$id) {
            throw new MuErrorRequestException("Debe indicar el id de la propiedad que desea operar.");
        }

        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->patch("/propiedades/{$id}/scopes", $scopes);
    }

    /**
     * @param string $id
     * @return MuResponse
     * @throws Exceptions\MuException
     * @throws MuErrorRequestException
     */
    public function destroyPropiedad(string $id): MuResponse
    {
        if (!$id) {
            throw new MuErrorRequestException("Debe indicar el id de la propiedad que desea operar.");
        }

        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->delete("/propiedades/{$id}");
    }

    /**
     * @return MuResponse
     * @throws Exceptions\MuErrorResponseException
     * @throws Exceptions\MuException
     */
    public function getCiudades(): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->get("/ciudades");
    }

    /**
     * @param string $id
     * @return MuResponse
     * @throws Exceptions\MuException
     */
    public function findCiudad(string $id): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->get("/ciudades/{$id}");
    }


    /**
     * @return MuResponse
     * @throws Exceptions\MuErrorResponseException
     * @throws Exceptions\MuException
     */
    public function getTiposPropiedad(): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->get("/tipos-propiedad");
    }

    /**
     * @param string $id
     * @return MuResponse
     * @throws Exceptions\MuException
     */
    public function findTipoPropiedad(string $id): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->get("/tipos-propiedad/{$id}");
    }

    /**
     * @return MuResponse
     * @throws Exceptions\MuErrorResponseException
     * @throws Exceptions\MuException
     */
    public function getOperaciones(): MuResponse
    {
        return CurlRestClient::connect($this->getApiBaseUrl())
            ->auth($this->token)
            ->get("/operaciones");
    }

}
