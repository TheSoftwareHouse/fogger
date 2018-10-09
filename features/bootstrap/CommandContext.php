<?php

use App\Command\InitCommand;
use App\Config\ConfigFactory;
use App\Config\ConfigLoader;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandContext implements Context
{
    const FOGGER_COMMAND_TEMPLATE = 'fogger:%s';

    private $application;

    /** @var CommandTester */
    private $tester;

    public function __construct(
        KernelInterface $kernel,
        ConfigFactory $configFactory,
        ConfigLoader $configLoader
    ) {
        $this->application = new Application($kernel);
        $this->application->add(new InitCommand($configFactory, $configLoader));
    }

    private function foggerName(string $name): string
    {
        return sprintf(self::FOGGER_COMMAND_TEMPLATE, $name);
    }

    private function runCommand(string $name, array $input = [])
    {
        $command = $this->application->find($name);
        $this->tester = new CommandTester($command);
        $this->tester->execute(array_merge(['command' => $command->getName()], $input));
    }

    /**
     * @When I run :name command
     * @param string $name
     */
    public function iRunCommand(string $name)
    {
        $this->runCommand($this->foggerName($name));
    }

    /**
     * @When I run :name command with input:
     * @param $name
     * @param TableNode $table
     */
    public function iRunCommandWithInput($name, TableNode $table)
    {
        $this->runCommand(
            $this->foggerName($name),
            array_map(function ($item) { return $item === 'true' ? true : $item; }, $table->getRowsHash())
        );
    }

    /**
     * @Then I should see :text in command's output
     * @param string $text
     * @throws Exception
     */
    public function iShouldSeeInCommandsOutput($text)
    {
        if (false === strpos($this->tester->getDisplay(), $text)) {
            throw new \Exception('Text not present in command output');
        }
    }

    /**
     * @When the command should exit with code :code
     * @param int $code
     * @throws Exception
     */
    public function theCommandShouldExitCode(int $code)
    {
        if ($code !== $this->tester->getStatusCode()) {
            throw new \Exception(
                sprintf(
                    'Command exited with %d, %d expected',
                    $this->tester->getStatusCode(),
                    $code
                )
            );
        }
    }

    /**
     * @Then print commands output
     */
    public function printCommandsOutput()
    {
        dump($this->tester->getDisplay());
    }

    /**
     * @Given the task queue is empty
     */
    public function theTaskQueueIsEmpty()
    {
        $this->runCommand(
            'rabbitmq:purge',
            [
                'name' => 'fogger_data_chunks_test',
                '--no-confirmation' => true,
            ]
        );
    }

    /**
     * @When worker processes :count task(s)
     * @param int $count
     */
    public function workerProcessTask(int $count)
    {
        $this->runCommand(
            'rabbitmq:consumer',
            [
                '--messages' => $count,
                'name' => 'fogger_data_chunks_test',
            ]
        );
    }
}
