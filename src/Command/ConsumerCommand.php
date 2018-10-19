<?php

namespace App\Command;

use App\Config\ConfigLoader;
use App\Fogger\Data\ChunkCache;
use App\Fogger\Data\ChunkConsumer;
use App\Fogger\Data\ChunkMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumerCommand extends Command
{
    private $chunkCache;

    private $chunkConsumer;

    public function __construct(
        ChunkCache $chunkCache,
        ChunkConsumer $chunkConsumer
    ) {
        $this->chunkCache = $chunkCache;
        $this->chunkConsumer = $chunkConsumer;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fogger:consumer')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_REQUIRED,
                'Where should the command look for a config file. Defaults to fogger.yaml in root folder.',
                ConfigLoader::DEFAULT_FILENAME
            )
            ->addOption(
                'messages',
                'm',
                InputOption::VALUE_REQUIRED,
                'How many messages to process.',
                200
            )
            ->setDescription('Consumes a message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        for ($i = 0; $i < $input->getOption('messages'); $i++) {

            /** @var ChunkMessage $message */
            $message = $this->chunkCache->popMessage();

            if ($message instanceof ChunkMessage) {
                $this->chunkConsumer->execute($message);
            } else {
                echo('.');
                usleep(500000);
            }
        }
    }
}
