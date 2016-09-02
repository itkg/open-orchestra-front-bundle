<?php

namespace OpenOrchestra\FrontBundle\Tests\SubQuery;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\FrontBundle\SubQuery\SubQueryGeneratorManager;
use Phake;

/**
 * Test SubQueryGeneratorManagerTest
 */
class SubQueryGeneratorManagerTest extends AbstractBaseTestCase
{
    /**
     * @var SubQueryGeneratorManager
     */
    protected $manager;

    protected $strategy;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->strategy = Phake::mock('OpenOrchestra\FrontBundle\SubQuery\SubQueryGeneratorInterface');
        Phake::when($this->strategy)->getName()->thenReturn('foo');

        $this->manager = new SubQueryGeneratorManager();
        $this->manager->addStrategy($this->strategy);
    }

    /**
     * @param bool  $support
     * @param array $blockParameters
     * @param array $strategyResponse
     * @param array $baseSubQuery
     * @param array $expected
     *
     * @dataProvider provideGenerateData
     */
    public function testGenerate($support, array $blockParameters, array $strategyResponse, array $baseSubQuery, array $expected)
    {
        Phake::when($this->strategy)->support(Phake::anyParameters())->thenReturn($support);
        Phake::when($this->strategy)->generate(Phake::anyParameters())->thenReturn($strategyResponse);

        $this->assertSame($expected, $this->manager->generate($blockParameters, $baseSubQuery));
    }

    /**
     * @return array
     */
    public function provideGenerateData()
    {
        $fooBarArray = array('foo' => 'bar');

        return array(
            array(false, array(), array(), array(), array()),
            array(true, array(), array(), array(), array()),
            array(true, array('blockParameter' => array('foo')), $fooBarArray, array(), $fooBarArray),
            array(true, array('blockParameter' => array('foo')), array(), $fooBarArray, $fooBarArray),
            array(false, array('blockParameter' => array('foo')), $fooBarArray, array(), array()),
            array(true, array('blockParameter' => array('foo')), $fooBarArray, array('foo' => 'foo'), $fooBarArray),
            array(true, array('blockParameter' => array('foo', 'bar')), $fooBarArray, array('foo' => 'foo'), $fooBarArray),
        );
    }
}
