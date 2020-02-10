<?php

namespace App\Command;

use App\Config\ConfigLoader;
use App\Fogger\Data\ChunkCache;
use App\Fogger\Data\ChunkError;
use App\Fogger\Data\ChunkMessage;
use App\Fogger\Data\ChunkProducer;
use App\Fogger\Recipe\RecipeFactory;
use App\Fogger\Refine\Refiner;
use App\Fogger\Schema\SchemaManipulator;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunCommand extends FinishCommand
{
    private $chunkProducer;

    public function __construct(
        SchemaManipulator $schemaManipulator,
        ChunkProducer $chunkProducer,
        RecipeFactory $recipeFactory,
        Refiner $refiner,
        ChunkCache $chunkCache,
        ChunkError $chunkError
    ) {

        $this->chunkProducer = $chunkProducer;

        parent::__construct($schemaManipulator, $refiner, $chunkCache, $chunkError, $recipeFactory);
    }

    protected function configure()
    {
        $this
            ->setName('fogger:run')
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
        $output->writeln('If you are masking big database, you can stop this process with Cmd/Ctrl + C');
        $output->writeln('Moving data will continue in the background - but in that case, you must manually');
        $output->writeln('invoke the fogger:finish command to recreate indexes and foreign keys');
        $output->writeln('');

        $output->writeln('Moving data in chunks:');

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

        $chunkSize = (int)$input->getOption('chunk-size');
        if ($chunkSize < 1) {
            $this->outputMessage("There has been an error:\n\nChunk size should be greater than 0", $io);

            return -1;
        }

        if (!$this->schemaManipulator->isTargetSchemaEmpty()) {
            $io->warning('Target database schema is not empty');
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Drop target database schema? (y/n) ', false);
            if ($helper->ask($input, $output, $question)) {
                $io->text('Dropping database schema...');
                $this->schemaManipulator->dropTargetSchema();
                $io->success('Target database schema dropped successfully!');
            }
        }

        try {
            $this->schemaManipulator->copySchemaDroppingIndexesAndForeignKeys();
            $this->recipe = $this->recipeFactory
                ->createRecipe($input->getOption('file'), $chunkSize);
            $this->chunkProducer->run($this->recipe);
        } catch (\Exception $exception) {
            $this->outputMessage("There has been an error:\n\n".$exception->getMessage(), $io);

            return -1;
        }

        if ($input->getOption('dont-wait')) {

            $output->writeln('');
            $output->writeln(
                <<<EOT
<comment>With dont-wait option the command will only queue data chunks to be processed by the rabbit 
worker command. Worker runs in background unless you started docker-composer with --scale=worker=0. 
In order to recreate indexes and foreign keys you will need to manually execute the fogger:finish
command after the workers</comment>
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

        parent::execute($input, $output);

        return 0;
    }
}
