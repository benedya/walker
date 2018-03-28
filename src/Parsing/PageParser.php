<?php

namespace Benedya\Walker\Parsing;

use Benedya\Walker\Container;
use Benedya\Walker\Mapping\Node;
use Benedya\Walker\Mapping\Page;
use Symfony\Component\DomCrawler\Crawler;

class PageParser implements Parser
{
    protected $page;
    protected $container;
    protected $parent;
    protected $result;
    protected static $countSaves;

    public function __construct(Page $page, Container $container)
    {
        $this->page = $page;
        $this->container = $container;
    }

    public static function getCountSaves(): int
    {
        return self::$countSaves;
    }

    public function parse()
    {
        $this->parsePage($this->page);
        $this->container->getLogger()->alert(sprintf('Count saves %s', self::$countSaves++));
    }

    protected function parsePage(Page $page)
    {
        $logger = $this->container->getLogger();
        $logger->info(sprintf('Parse page %s', $page->getUrl()));

        $html = $page->getContains();
        $result = [];

        /** @var Node $node */
        foreach ($page->getNodes() as $node) {
            $crawler = $this->container->getCrawler();
            $crawler->addHtmlContent($html);
            $result = array_merge($result, $this->parseNode($node, $crawler));
        }

        return $result;
    }

    protected function parseNode(Node $node, Crawler $crawler)
    {
        $logger = $this->container->getLogger();
        $result = [];
        $selector = $node->getSelector();
        $selectorVerbose = $node->getName();
        if (!is_callable($node->getSelector())) {
            $selectorVerbose = $node->getSelector();
        }
        if ($parent = $node->getParent()) {
            $logger->info(sprintf('%s parse child node %s', str_repeat("\t", $node->getCountParents()), $selectorVerbose));
        } else {
            $logger->info(sprintf('Parse node %s', $selectorVerbose));
        }
        if (is_callable($selector)) {
            if ($data = $selector($crawler)) {
                $result = [$node->getName() => $data];
            }

            return $result;
        }
        $items = $crawler->filter($node->getSelector());
        if (!$node->getChildren()) {
            $items->each(function (Crawler $crawler) use ($node, &$result) {
                if ($node->getProperty()) {
                    $data = $crawler->attr($node->getProperty());
                } else {
                    $data = $crawler->text();
                }
                $data = trim($data);
                $node->setVal($data);
                if ($node->getParent()) {
                    $result[$node->getName()] = $node->getVal();
                } else {
                    $result[$node->getName()][] = $node->getVal();
                }
                if ($branch = $node->getBranch()) {
                    if (false !== filter_var($node->getVal(), FILTER_VALIDATE_URL)) {
                        $newPage = clone $this->page;
                        $newPage->open($node->getVal());
                        foreach ($branch->getChildren() as $child) {
                            $newPage->find($child);
                        }
                        if ($res = $this->parsePage($newPage)) {
                            $result[$node->getVal()] = $res;
                        }
                    } else {
                        $this->container->getLogger()->error(sprintf('Url "%s" is not valid', $node->getVal()));
                    }
                }
            });
        }
        if ($node->getChildren()) {
            $items->each(function (Crawler $crawler) use ($node, &$result) {
                $data = [];
                /** @var Node $subNode */
                foreach ($node->getChildren() as $subNode) {
                    $parseData = $this->parseNode($subNode, $crawler);
                    $data = array_merge($data, $parseData);
                    if ($subNode->saveIt()) {
                        $this->container->getLogger()->info('Saving on demand...');
                        ++self::$countSaves;
                        $this->container->getStorage()->save($data);
                    }
                }
                if ($data) {
                    if ('' === key($data)) {
                        $data = array_pop($data);
                    }
                    if ($node->getName()) {
                        if (is_array($data)) {
                            $result[$node->getName()][] = $data;
                        } else {
                            $result[$node->getName()] = $data;
                        }
                    } else {
                        $result = $data;
                    }
                }
            });
        }

        return $result;
    }

    public function getParent(): ?Page
    {
        return $this->parent;
    }
}
