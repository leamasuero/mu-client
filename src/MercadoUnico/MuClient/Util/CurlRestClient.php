<?php

namespace MercadoUnico\MuClient\Util;

use CURLFile;
use MercadoUnico\MuClient\Exceptions\JsonErrorException;
use MercadoUnico\MuClient\Exceptions\MuErrorResponseException;
use MercadoUnico\MuClient\Exceptions\MuException;
use MercadoUnico\MuClient\Http\MuResponse;
use MercadoUnico\MuClient\Http\Response;
use MercadoUnico\MuClient\MuClient;

class CurlRestClient
{

    const GET = 'GET';
    const PUT = 'PUT';
    const POST = 'POST';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';

    /**
     * @var string
     */
    private $host;

    /**
     * @var integer
     */
    private $port;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $host
     * @param integer $port
     * @return CurlRestClient
     * @throws MuException
     */
    static public function connect(string $host, int $port = 80): CurlRestClient
    {
        if (!extension_loaded("curl")) {
            throw new MuException("cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.");
        }

        return new self($host, $port);
    }

    /**
     * CurlRestClient constructor.
     * @param string $host
     * @param int $port
     */
    protected function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->headers = [
            "Accept" => "application/json",
            "Content-Type" => "application/json"
        ];
    }

    /**
     * @param string $token
     * @param string $type
     * @return $this
     */
    public function auth(string $token, string $type = 'Basic'): self
    {
        $this->setHeader('Authorization', "{$type} {$token}");
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    private function getHttpHeaders(): array
    {
        $headers = [];
        foreach ($this->getHeaders() as $k => $v) {
            $headers[] = "{$k}: {$v}";
        }

        return $headers;
    }

    /**
     * @param string $path
     * @param array $data
     * @return MuResponse
     * @throws MuErrorResponseException
     * @throws MuException
     */
    public function get(string $path, array $data = []): MuResponse
    {
        return $this->exec(self::GET, $path, $data);
    }

    /**
     * @param string $path
     * @param array $data
     * @return MuResponse
     * @throws JsonErrorException
     * @throws MuErrorResponseException
     * @throws MuException
     */
    public function post(string $path, array $data = []): MuResponse
    {
        return $this->exec(self::POST, $path, $data);
    }

    /**
     * @param string $path
     * @param array $data
     * @return MuResponse
     * @throws MuErrorResponseException
     * @throws MuException
     */
    public function patch(string $path, array $data = []): MuResponse
    {
        return $this->exec(self::PATCH, $path, $data);
    }

    /**
     * @param string $path
     * @param array $data
     * @return MuResponse
     * @throws MuErrorResponseException
     * @throws MuException
     */
    public function delete(string $path, array $data = []): MuResponse
    {
        return $this->exec(self::DELETE, $path, $data);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $data
     * @return resource
     * @throws JsonErrorException
     */
    private function buildRequest(string $method, string $path, array $data)
    {
        $connect = curl_init();

        curl_setopt($connect, CURLOPT_USERAGENT, "MercadoUnico PHP SDK v" . MuClient::VERSION);
        curl_setopt($connect, CURLOPT_URL, "{$this->host}{$path}");
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $this->getHttpHeaders());

        switch ($method) {
            case self::POST:
            case self::PATCH:

                $jsonData = json_encode($data);

                if (function_exists('json_last_error')) {
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new JsonErrorException(json_last_error(), $data);
                    }
                }

                curl_setopt($connect, CURLOPT_POSTFIELDS, $jsonData); // ver como se debe pasar esto? array o string?
                break;
        }

        return $connect;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $data
     * @return MuResponse
     * @throws MuErrorResponseException
     * @throws MuException
     */
    public function exec(string $method, string $path, array $data): MuResponse
    {
        $connect = $this->buildRequest($method, $path, $data);
        $response = curl_exec($connect);

        if ($response === false) {
            throw new MuException(curl_error($connect));
        }

        $responseStatusCode = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($responseStatusCode >= Response::HTTP_BAD_REQUEST) {
            throw new MuErrorResponseException(json_decode($response, true), $responseStatusCode);
        }

        curl_close($connect);

        return new MuResponse(json_decode($response, true), $responseStatusCode);
    }

    /**
     * @param string $path
     * @param CURLFile $CURLFile
     * @return MuResponse
     * @throws MuErrorResponseException
     * @throws MuException
     */
    public function file(string $path, CURLFile $CURLFile): MuResponse
    {
        $this->setHeader('Content-Type', 'multipart/form-data');

        $connect = curl_init();

        curl_setopt($connect, CURLOPT_USERAGENT, "MercadoUnico PHP SDK v" . MuClient::VERSION);
        curl_setopt($connect, CURLOPT_URL, "{$this->host}{$path}");
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($connect, CURLOPT_HTTPHEADER, $this->getHttpHeaders());
        curl_setopt($connect, CURLOPT_POST, 1);
        curl_setopt($connect, CURLOPT_POSTFIELDS, ['files' => $CURLFile]);

        $response = curl_exec($connect);

        if ($response === false) {
            throw new MuException(curl_error($connect));
        }

        $responseStatusCode = curl_getinfo($connect, CURLINFO_HTTP_CODE);
        if ($responseStatusCode >= Response::HTTP_BAD_REQUEST) {
            throw new MuErrorResponseException(json_decode($response, true), $responseStatusCode);
        }

        curl_close($connect);

        return new MuResponse(json_decode($response, true), $responseStatusCode);
    }
}





