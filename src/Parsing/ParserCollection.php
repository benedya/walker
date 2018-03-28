<?php

namespace Benedya\Walker\Parsing;

use Benedya\Walker\Container;
use Benedya\Walker\Mapping\Page;

class ParserCollection implements Parser
{
    protected $page;
    protected $container;
    protected $pages = [];
    protected $totalParsed = [];

    public function __construct(Page $page, Container $container)
    {
        $this->page = $page;
        $this->container = $container;
    }

    public function parse()
    {
        $page = $this->page;
        $parser = (new PageParser($page, $this->container));
        $parser->parse();
        $this->totalParsed[] = [
            $page->getUrl() => $parser::getCountSaves(),
        ];
        /** @var Page $page */
        while ($page = $page->getNextPage()) {
            $this->container->getLogger()->info(sprintf('Next page %s', $page->getUrl()));
            sleep(3);
            $parser = (new PageParser($page, $this->container));
            $parser->parse();
            $this->totalParsed[] = [
                $page->getUrl() => $parser::getCountSaves(),
            ];
        }
    }

    public function getParent(): ?Page
    {
    }

    public function getTotalParsed(): array
    {
        return $this->totalParsed;
    }
}
