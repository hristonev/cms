<?php
/**
 * Created by PhpStorm.
 * User: dimitar
 * Date: 17.08.17
 * Time: 09:34
 */

namespace AppBundle\Service\FootballDataORG;


use GuzzleHttp\Client;

class Request
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * Request constructor.
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->client = new Client([
            'headers' => [
                'X-Auth-Token' => $token
            ]
        ]);
    }

    /**
     * @param string $method
     * @param string $uri
     * @return array|\stdClass
     */
    protected function request(string $method, string $uri)
    {
        $res = $this->client->request(
            $method,
            $uri
        );
        return json_decode((string) $res->getBody());
    }
}