<?php

namespace Clerk\Classifier;

class Task extends Category
{
    private $id;

    public function __construct($name, $id)
    {
        parent::__construct($name);
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
