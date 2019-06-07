<?php

namespace App\Command;

use App\Config\ConfigLoader;
use App\Fogger\Data\Mongo\ChunkCache;
use App\Fogger\Data\Mongo\ChunkMessage;
use App\Fogger\Data\Mongo\ChunkProducer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MongoRunCommand extends Command
{
    private $configLoader;

    private $chunkProducer;

    private $chunkCache;

    public function __construct(ConfigLoader $loader, ChunkProducer $producer, ChunkCache $chunkCache)
    {
        $this->configLoader = $loader;
        $this->chunkProducer = $producer;
        $this->chunkCache = $chunkCache;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fogger:mongo:run')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_REQUIRED,
                'Where should the command look for a config file. Defaults to fogger.yaml in root folder.',
                ConfigLoader::DEFAULT_FILENAME
            )
            ->addOption(
                'chunk-size',
                'c',
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Data is moved in chunks. What should be the size of a chunk. Defaults to %d',
                    ChunkMessage::DEFAULT_CHUNK_SIZE
                ),
                ChunkMessage::DEFAULT_CHUNK_SIZE
            )
            ->addOption(
                'dont-wait',
                '',
                InputOption::VALUE_NONE,
                'With this option command will not wait for the workers to finish.'
            )
            ->setDescription('Starts the process of moving data from source to target database. ');

    }

    private function showProgressBar(OutputInterface $output)
    {
        $published = $this->chunkCache->getPublishedCount();

        $output->writeln('');
        $output->writeln("Data has been divided into chunks. Fogger is copying data to target database");
        $output->writeln('');

        $output->writeln('Progess [number of chunks]:');

        $progressBar = new ProgressBar($output, $published);
        $progressBar->start();

        do {
            $processed = $this->chunkCache->getProcessedCount();
            $progressBar->setProgress($processed);
            usleep(100000);
        } while ($processed < $published);

        $progressBar->finish();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $output->writeln('Fogger run.');

        try {
            $this->chunkProducer->run($this->configLoader->load($input->getOption('file')));
        } catch (\Exception $exception) {
            $io->error("There has been an error:\n\n".$exception->getMessage());

            return -1;
        }
        if ($input->getOption('dont-wait')) {

            $output->writeln('');
            $output->writeln(
                <<<EOT
<comment>With dont-wait option the command will only queue data chunks to be processed by the rabbit 
worker command. Worker runs in background unless you started docker-composer with --scale=worker=0. </comment>
EOT
            );
            $output->writeln('');
            $output->writeln(
                sprintf('<info>%d chunks have been added to queue</info>', $this->chunkCache->getPublishedCount())
            );

            return 0;
        }

        $this->showProgressBar($output);
        $output->writeln('');
        $output->writeln('');

        return 0;
    }
}
