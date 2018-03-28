<?php

/***
 * Example how to parse github using the Walker.
 *
 * In this example, you can see how the Walker walks through a list "https://github.com/search?utf8=✓&q=php&type=Repositories"
 * scrapes each project name and save it. After that, it opens a next page of the list and does the same.
 * It is repeated until end of the list.
 *
 */

include './vendor/autoload.php';

/**
 * Configuring container.
 */
$container = new \Benedya\Walker\Container(
    new class() implements \Benedya\Walker\Storage {
        public function save(array $data)
        {
            // todo save it
            print_r($data);
        }
    }
);

/*
 * Putting a logger for tracking the parsing process.
 */
$container->setLogger(
    (new class() extends Psr\Log\AbstractLogger {
        public function log($level, $message, array $context = array())
        {
            echo sprintf("\n %s - %s \n", $level, $message);
        }
    })
);

/*
 * Creating a configurator for parsing github.
 */
(new class($container) extends Benedya\Walker\Configurator\Configurator {
    public function __construct(\Benedya\Walker\Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Configuring a map of elements that have to be saved.
     */
    public function buildNodes(\Benedya\Walker\Mapping\Node $node)
    {
        /**
         * Selecting a section with projects.
         */
        $listProjects = $node
            ->filter('.repo-list .repo-list-item')
            ->setName('projects')
        ;
        /*
         * Walking through the project's section selects each project name and save it.
         */
        $listProjects
            ->getChild('a.v-align-middle')
            ->setName('name')
            ->save()
        ;
    }

    /**
     * Detecting where the current page has a link to a next page.
     * If yes then it scrapes the link and parses the next page.
     */
    protected function checkNextPage(
        \Benedya\Walker\Mapping\Page $page,
        \Benedya\Walker\Mapping\Node $nodeData
    ): \Benedya\Walker\Mapping\Node {
        $node = $this->createNodeMapping();
        $node
            ->filter('a.next_page')
            ->setProperty('href')
            ->setName('nextPage')
            ->setTransformer(function ($data) use (&$page, $nodeData) {
                if ($data) {
                    $nextPage = $page
                        ->openNextPage('https://github.com'.$data);
                    $nextPage
                        ->find($nodeData)
                        ->find($this->checkNextPage($nextPage, $nodeData))
                    ;
                }
            })
        ;

        return $node;
    }

    /**
     * Configuring pages that have to be parsed. And nodes that have to be found on the pages.
     */
    public function buildPages(\Benedya\Walker\Mapping\Page $page, \Benedya\Walker\Mapping\Node $node)
    {
        $page
            ->open('https://github.com/search?utf8=✓&q=php&type=Repositories')
            ->find($node)
            ->find($this->checkNextPage($page, $node))
        ;
    }
})
    ->buildParser()
    ->parse();
