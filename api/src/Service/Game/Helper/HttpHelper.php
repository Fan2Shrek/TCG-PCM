<?php

declare(strict_types=1);

namespace App\Service\Game\Helper;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HttpHelper
{
    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    public function get(string $url): ResponseInterface
    {
        return $this->client->request(Request::METHOD_GET, $url);
    }
}
