<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Composer\Tests\Unit\Task;

use Codeception\Test\Unit;
use League\Container\Container as LeagueContainer;
use Psr\Container\ContainerInterface;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcessHelper;
use Sweetchuck\Robo\Composer\Test\Helper\Dummy\DummyTaskBuilder;
use Sweetchuck\Robo\Composer\Test\UnitTester;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\ErrorHandler\BufferingLogger;

class TaskTestBase extends Unit
{
    protected UnitTester $tester;

    protected ContainerInterface $container;

    protected RoboConfig $config;

    protected CollectionBuilder $builder;

    protected DummyTaskBuilder $taskBuilder;

    /**
     * @SuppressWarnings("CamelCaseMethodName")
     */
    public function _before()
    {
        parent::_before();

        Robo::unsetContainer();
        DummyProcess::reset();

        $this->container = new LeagueContainer();
        $application = new SymfonyApplication('Sweetchuck - Robo Composer', '2.0.0');
        $application->getHelperSet()->set(new DummyProcessHelper(), 'process');
        $this->config = (new RoboConfig());
        $input = null;
        $output = new DummyOutput([
            'verbosity' => DummyOutput::VERBOSITY_DEBUG,
        ]);

        $this->container->add('container', $this->container);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        $this->container->addShared('logger', BufferingLogger::class);

        $this->builder = CollectionBuilder::create($this->container, null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);
    }
}
