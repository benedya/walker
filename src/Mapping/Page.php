<?php

namespace Benedya\Walker\Mapping;

class Page
{
    protected $resource;
    protected $nodes;
    protected $nextPage;
    protected $httpClient;
    protected $method;
    protected $options;

    public function __construct(\GuzzleHttp\ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->options = [];
        $this->nodes = [];
    }

    public function __clone()
    {
        $this->nodes = [];
        $this->nextPage = null;
        $this->resource = null;
    }

    public function getContains(): string
    {
        $method = $this->method;

        if (!$method) {
            $method = 'get';
        }

        return $this->httpClient->request(
                $method,
                $this->resource,
                $this->options ? $this->options : []
            )->getBody().'';
    }

    public function open($resource, $method = '', array $options = []): Page
    {
        $this->resource = $resource;

        if ($method) {
            $this->method = $method;
        }

        if ($options) {
            $this->options = $options;
        }

        return $this;
    }

    public function find(Node $node): Page
    {
        $this->nodes[] = $node;

        return $this;
    }

    public function openNextPage($resource, $method = '', array $options = []): Page
    {
        $this->nextPage = (new self($this->httpClient))->open($resource, $method, $options);

        return $this->nextPage;
    }

    public function getUrl(): string
    {
        return $this->resource;
    }

    public function getNextPage(): ?Page
    {
        return $this->nextPage;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getHttpClient(): \GuzzleHttp\ClientInterface
    {
        return $this->httpClient;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
