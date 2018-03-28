<?php

namespace Benedya\Walker\Mapping;

class Node
{
    /** @var string */
    protected $selector;

    /** @var string */
    protected $property;

    /** @var string */
    protected $name;

    /** @var bool */
    protected $required = false;

    /** @var array */
    protected $children = [];

    /** @var Node */
    protected $parent;

    /** @var string */
    protected $val;

    protected $branch = null;

    /** @var \Closure */
    protected $transformer;

    /** @var bool */
    protected $toSave = false;

    public function __construct()
    {
        $this->name = '';
        $this->children = [];
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function filter(string $selector): Node
    {
        $this->selector = $selector;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): Node
    {
        $this->required = $required;

        return $this;
    }

    public function getChild(string $selector): Node
    {
        $node = (new self())
            ->filter($selector)
            ->setParent($this);

        $this->children[] = $node;

        return $node;
    }

    public function setName(string $name): Node
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getParent(): ?Node
    {
        return $this->parent;
    }

    public function setParent(Node $parent): Node
    {
        $this->parent = $parent;

        return $this;
    }

    public function getCountParents(): int
    {
        $count = 0;
        $node = $this;

        while ($node = $node->getParent()) {
            ++$count;
        }

        return $count;
    }

    public function setProperty(string $property): Node
    {
        $this->property = $property;

        return $this;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function setVal(string $val): Node
    {
        $this->val = $val;

        return $this;
    }

    public function getVal(): ?string
    {
        $closure = $this->transformer;

        return is_callable($closure) ? $closure($this->val) : $this->val;
    }

    public function createBranch(): Node
    {
        $this->branch = (new self());

        return $this->branch;
    }

    public function getBranch(): ?Node
    {
        return $this->branch;
    }

    public function setTransformer(\Closure $closure): Node
    {
        $this->transformer = $closure;

        return $this;
    }

    public function setChildren(array $children): Node
    {
        $this->children = $children;

        return $this;
    }

    public function save(): Node
    {
        $this->toSave = true;

        return $this;
    }

    public function saveIt(): bool
    {
        return $this->toSave;
    }
}
