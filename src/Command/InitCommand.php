<?php

namespace App\Command;

use App\Config\ConfigFactory;
use App\Config\ConfigLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    private $configFactory;

    private $configLoader;

    public function __construct(ConfigFactory $configFactory, ConfigLoader $configLoader)
    {
        $this->configFactory = $configFactory;
        $this->configLoader = $configLoader;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fogger:init')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_REQUIRED,
                'Where should the command save the config file. Defaults to fogger.yaml in root folder.',
                ConfigLoader::DEFAULT_FILENAME
            )
            ->setDescription('Creates configuration boilerplate base on the source DB schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Fogger init.');
        $filename = $input->getOption('file');

        try {
            $this->configLoader->save($this->configFactory->createFromDBAL(), $filename);
        } catch (\Exception $exception) {
            $output->writeln('There has been an error: '.$exception->getMessage());

            return -1;
        }

        $output->writeln('Done! Config boilerplate saved to '.$filename);

        return 0;
    }
}
