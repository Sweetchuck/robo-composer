<?php

namespace Sweetchuck\Robo\Composer\Test\Helper\Dummy;

class Process extends \Symfony\Component\Process\Process
{
    /**
     * @var array
     */
    public static $prophecy = [];

    /**
     * @var int
     */
    protected static $counter = 0;

    /**
     * @var static[]
     */
    public static $instances = null;

    public static function reset(): void
    {
        static::$prophecy = [];
        static::$instances = [];
    }

    /**
     * @var int
     */
    protected $index = 0;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $commandline,
        $cwd = null,
        array $env = null,
        $input = null,
        $timeout = 60,
        array $options = []
    ) {
        parent::__construct($commandline, $cwd, $env, $input, $timeout, $options);

        $this->index = static::$counter++;
        static::$instances[$this->index] = $this;
    }

    public function __destruct()
    {
        parent::__destruct();

        unset(static::$instances[$this->index]);
        unset(static::$prophecy[$this->index]);
    }

    /**
     * {@inheritdoc}
     */
    public function run($callback = null)
    {
        return static::$prophecy[$this->index]['exitCode'];
    }

    /**
     * {@inheritdoc}
     */
    public function getExitCode()
    {
        return static::$prophecy[$this->index]['exitCode'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return static::$prophecy[$this->index]['stdOutput'];
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorOutput()
    {
        return static::$prophecy[$this->index]['stdError'];
    }
}
