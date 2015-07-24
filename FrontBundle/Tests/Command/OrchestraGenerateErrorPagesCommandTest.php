<?php

namespace OpenOrchestra\FrontBundle\Tests\Command;

use Phake;
use OpenOrchestra\FrontBundle\Command\OrchestraGenerateErrorPagesCommand;
use Symfony\Component\Console\Application;

/**
 * Class OrchestraGenerateErrorPagesCommandTest
 *
 */
class OrchestraGenerateErrorPagesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrchestraGenerateErrorPagesCommand
     */
    protected $command;

    protected $container;
    protected $application;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->container = Phake::mock('Symfony\Component\DependencyInjection\Container');

        $this->command = new OrchestraGenerateErrorPagesCommand();
        $this->command->setContainer($this->container);

        $this->application = new Application();
        $this->application->add($this->command);
    }

    /**
     * Test presence and name
     */
    public function testPresenceAndName()
    {
        $command = $this->application->find('orchestra:errorpages:generate');

        $this->assertInstanceOf('Symfony\Component\Console\Command\Command', $command);
    }

    /**
     * Test the definition
     */
    public function testDefinition()
    {
        $definition = $this->command->getDefinition();

        $this->assertTrue($definition->hasOption('siteId'));
    }
}
