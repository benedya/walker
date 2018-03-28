<?php

namespace Benedya\Walker;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DomCrawler\Crawler;

class Container
{
    /** @var Storage */
    protected $storage;
    /** @var LoggerInterface */
    protected $logger;
    /** @var Crawler */
    protected $crawler;
    /** @var \GuzzleHttp\Client */
    protected $httpClient;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->httpClient = new \GuzzleHttp\Client();
        $this->logger = new NullLogger();
        $this->crawler = new Crawler();
    }

    public function getStorage(): Storage
    {
        return $this->storage;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getCrawler(): Crawler
    {
        $this->crawler->clear();

        return $this->crawler;
    }

    public function setStorage(Storage $storage): Container
    {
        $this->storage = $storage;

        return $this;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function setCrawler(Crawler $crawler): Container
    {
        $this->crawler = $crawler;

        return $this;
    }

    public function setHttpClient(\GuzzleHttp\Client $httpClient): Container
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function getHttpClient(): \GuzzleHttp\Client
    {
        return $this->httpClient;
    }
}
