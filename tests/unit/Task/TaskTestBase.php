<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit\Task;

use Codeception\Test\Unit;
use League\Container\Container as LeagueContainer;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcessHelper;
use Sweetchuck\Robo\Composer\Test\Helper\Dummy\DummyTaskBuilder;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\ErrorHandler\BufferingLogger;

class TaskTestBase extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Composer\Test\UnitTester
     */
    protected $tester;

    /**
     * @var \League\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var \Robo\Config
     */
    protected $config;

    /**
     * @var \Robo\Collection\CollectionBuilder
     */
    protected $builder;

    /**
     * @var \Sweetchuck\Robo\Composer\Test\Helper\Dummy\DummyTaskBuilder
     */
    protected $taskBuilder;

    /**
     * @SuppressWarnings("CamelCaseMethodName")
     */
    public function _before()
    {
        parent::_before();

        Robo::unsetContainer();
        DummyProcess::reset();

        $this->container = new LeagueContainer();
        $application = new SymfonyApplication('Sweetchuck - Robo Composer', '1.0.0');
        $application->getHelperSet()->set(new DummyProcessHelper(), 'process');
        $this->config = (new Config());
        $input = null;
        $output = new DummyOutput([
            'verbosity' => DummyOutput::VERBOSITY_DEBUG,
        ]);

        $this->container->add('container', $this->container);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        $this->container->share('logger', BufferingLogger::class);

        $this->builder = CollectionBuilder::create($this->container, null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);
    }
}
