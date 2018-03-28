<?php

namespace Benedya\Walker\Configurator;

use Benedya\Walker\Container;
use Benedya\Walker\Mapping\Node;
use Benedya\Walker\Mapping\Page;
use Benedya\Walker\Parsing\Parser;
use Benedya\Walker\Parsing\ParserCollection;
use Benedya\Walker\Storage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class Configurator
{
    /** @var \GuzzleHttp\Client */
    protected $httpClient;
    /** @var Crawler */
    protected $crawler;
    /** @var LoggerInterface */
    protected $logger;
    /** @var Storage */
    protected $storage;
    /** @var Parser */
    protected $parser;
    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getParser(): Parser
    {
        return $this->parser;
    }

    public function buildParser(): Parser
    {
        $container = $this->container;
        $node = $this->createNodeMapping();
        $this->buildNodes($node);

        $page = $this->createPageMapping();
        $this->buildPages($page, $node);

        $this->parser = new ParserCollection($page, $container);

        return $this->parser;
    }

    protected function createNodeMapping(): Node
    {
        return new Node();
    }

    protected function createPageMapping(): Page
    {
        return new Page($this->container->getHttpClient());
    }

    abstract public function buildNodes(Node $node);

    abstract public function buildPages(Page $page, Node $node);
}
