<?php

namespace Benedya\Walker\Parsing;

use Benedya\Walker\Mapping\Page;

interface Parser
{
    public function parse();

    public function getParent(): ?Page;
}
