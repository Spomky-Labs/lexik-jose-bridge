<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use Assert\Assertion;
use Behat\Gherkin\Node\PyStringNode;
use SpomkyLabs\LexikJoseBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\CacheWarmupCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

trait ApplicationContext
{
    /**
     * @var null
     */
    private $application = null;

    /**
     * @var null|string
     */
    private $command_output = null;

    /**
     * @var null|\Exception
     */
    private $command_exception = null;

    /**
     * @var null|int
     */
    private $command_exit_code = null;

    /**
     * @param array $command_parameters
     */
    public function setCommandParameters($command_parameters)
    {
        $this->command_parameters = $command_parameters;
    }

    /**
     * @param int|null $command_exit_code
     */
    public function setCommandExitCode($command_exit_code)
    {
        $this->command_exit_code = $command_exit_code;
    }

    /**
     * @param \Exception|null $command_exception
     */
    public function setCommandException($command_exception)
    {
        $this->command_exception = $command_exception;
    }

    /**
     * @var array
     */
    private $command_parameters = [];

    /**
     * Returns HttpKernel service container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    abstract public function getContainer();

    /**
     * Returns the kernel.
     *
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    abstract public function getKernel();

    /**
     * @param string $command_output
     */
    protected function setCommandOutput($command_output)
    {
        $this->command_output = $command_output;
    }

    /**
     * @return null|string
     */
    protected function getCommandOutput()
    {
        return $this->command_output;
    }

    /**
     * @return null|array
     */
    protected function getCommandParameters()
    {
        return $this->command_parameters;
    }

    /**
     * @return null|\Exception
     */
    protected function getCommandException()
    {
        return $this->command_exception;
    }

    /**
     * @return null|int
     */
    protected function getCommandExitCode()
    {
        return $this->command_exit_code;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Console\Application
     */
    protected function getApplication()
    {
        if (null === $this->application) {
            $this->application = new Application($this->getKernel());
            $this->application->add(new Command\RegenCommand());
            $this->application->add(new Command\RotateCommand());
            $this->application->add(new Command\DeleteCommand());
            $this->application->add(new CacheWarmupCommand());
        }

        return $this->application;
    }

    /**
     * @Given I wait :time seconds
     */
    public function iWaitSeconds($time)
    {
        sleep((int) $time);
    }

    /**
     * @When I run command :line
     */
    public function iRunCommand($line)
    {
        try {
            $command = $this->getApplication()->find($line);
        } catch (\Exception $e) {
            $this->setCommandException($e);

            return;
        }
        $tester = new CommandTester($command);

        try {
            $this->setCommandExitCode($tester->execute($this->getCommandParams($command)));
            $this->setCommandException(null);
        } catch (\Exception $e) {
            $this->setCommandException($e);
            $this->setCommandExitCode($e->getCode());
        }
        $this->setCommandOutput($tester->getDisplay());
    }

    /**
     * @Given I run command :line with parameters
     */
    public function iRunACommandWithParameters($line, PyStringNode $parameterJson)
    {
        $this->setCommandParameters(json_decode($parameterJson->getRaw(), true));
        if (null === $this->getCommandParameters()) {
            throw new \InvalidArgumentException(
                'PyStringNode could not be converted to json.'
            );
        }

        $this->iRunCommand($line);
    }

    /**
     * @Then I should see
     */
    public function iShouldSee(PyStringNode $result)
    {
        $output = $this->getCommandOutput();
        var_dump($output);
        Assertion::eq($output, $result->getRaw(), sprintf('The output of the command is not the same as expected. I got "%".', $output));
    }

    /**
     * @Then I should see something like :pattern
     */
    public function iShouldSeeSomethingLike($pattern)
    {
        $result = preg_match($pattern, $this->getCommandOutput(), $matches);
        if (0 === $result) {
            throw new \Exception(sprintf('The command output "%s" does not match with the pattern', $this->getCommandOutput()));
        }
    }

    /**
     * @Then The command exception should not be thrown
     */
    public function theCommandExceptionShouldNotBeThrown()
    {
        if ($this->getCommandException() instanceof \Exception) {
            throw new \Exception(sprintf('An exception was not thrown: "%s".', $this->getCommandException()->getMessage()));
        }
    }

    /**
     * @Then The command exception :exception should be thrown
     */
    public function theCommandExceptionShouldBeThrown($exception)
    {
        if (!$this->getCommandException() instanceof $exception) {
            throw new \Exception('The expected exception was not thrown.');
        }
    }

    /**
     * @Then The command exit code should be :code
     */
    public function theCommandExitCodeShouldBe($code)
    {
        if ($this->getCommandExitCode() !== (int) $code) {
            throw new \Exception(sprintf('The exit code is %u.', $this->getCommandExitCode()));
        }
    }

    /**
     * @Then The command exception :exception with message should be thrown
     */
    public function theCommandExceptionWithMessageShouldBeThrown($exception, PyStringNode $message)
    {
        $this->theCommandExceptionShouldBeThrown($exception);
        if ($this->getCommandException()->getMessage() !== $message->getRaw()) {
            throw new \Exception(sprintf('The message of the exception is "%s".', $this->getCommandException()->getMessage()));
        }
    }

    /**
     * @Then The command exception :exception with message like :pattern should be thrown
     */
    public function theCommandExceptionWithMessageLikeShouldBeThrown($exception, $pattern)
    {
        $this->theCommandExceptionShouldBeThrown($exception);
        if (1 !== preg_match($pattern, $this->getCommandException()->getMessage())) {
            throw new \Exception(sprintf('The message of the exception is "%s".', $this->getCommandException()->getMessage()));
        }
    }

    /**
     * @param string $command
     *
     * @return array
     */
    private function getCommandParams($command)
    {
        return array_merge(
            $this->getCommandParameters(),
            ['command' => $command]
        );
    }

    /**
     * @When the file :filename should not exist
     */
    public function theFileShouldNotExist($filename)
    {
        $filename = $this->getRealFilename($filename);
        Assertion::false(file_exists($filename), sprintf('The file "%s" exists.', $filename));
    }

    /**
     * @Then the file :filename should exist
     */
    public function theFileShouldExist($filename)
    {
        $filename = $this->getRealFilename($filename);
        Assertion::true(file_exists($filename), sprintf('The file "%s" does not exist.', $filename));
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private function getRealFilename($filename)
    {
        return str_replace('%kernel.cache_dir%', $this->getContainer()->getParameter('kernel.cache_dir'), $filename);
    }
}
