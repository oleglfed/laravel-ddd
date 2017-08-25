<?php

namespace Test;

use oleglfed\LaravelDDD\Traits\GeneratorTrait;
use PHPUnit\Framework\TestCase;

class TraitTest extends TestCase
{
    public $dummy;

    /**
     * Set up test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dummy = new Dummy();
    }

    public function testGetPath()
    {
        $this->dummy->setTestPath('test');
        $this->assertEquals($this->dummy->getTestPath(), 'test');
    }

    public function testGetTable()
    {
        $this->dummy->table = 'test';
        $this->assertEquals($this->dummy->getTable(), 'test');
    }

    public function testGetProvidedName()
    {
        $this->dummy->name = 'test';
        $this->assertEquals($this->dummy->getProvidedName(), 'test');
    }

    public function testGetDirectory()
    {
        $this->dummy->directory = 'test';
        $this->assertEquals($this->dummy->getDirectory(), 'test');
    }
}

class Dummy
{
    use GeneratorTrait;
}
