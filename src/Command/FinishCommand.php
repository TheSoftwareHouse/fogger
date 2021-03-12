<?php

namespace App\Command;

use App\Config\ConfigLoader;
use App\Fogger\Data\ChunkCache;
use App\Fogger\Data\ChunkError;
use App\Fogger\Recipe\RecipeFactory;
use App\Fogger\Refine\Refiner;
use App\Fogger\Schema\SchemaManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FinishCommand extends Command
{
    protected $schemaManipulator;

    protected $chunkCache;

    protected $chunkError;

    protected $recipeFactory;

    protected $recipe = null;

    private $refiner;

    public function __construct(
        SchemaManipulator $schemaManipulator,
        Refiner $refiner,
        ChunkCache $chunkCache,
        ChunkError $chunkError,
        RecipeFactory $recipeFactory
    ) {
        $this->schemaManipulator = $schemaManipulator;
        $this->refiner = $refiner;
        $this->chunkCache = $chunkCache;
        $this->chunkError = $chunkError;
        $this->recipeFactory = $recipeFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fogger:finish')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_REQUIRED,
                'Where should the command look for a config file. Defaults to fogger.yaml in root folder.',
                ConfigLoader::DEFAULT_FILENAME
            )
            ->setDescription('Recreates all the indexes and foreign keys in the target');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Fogger finish procedure');

        $io = new SymfonyStyle($input, $output);
        if ($this->chunkCache->getProcessedCount() < $this->chunkCache->getPublishedCount()) {
            $this->outputMessage(
                sprintf(
                    "We are still working on it, please try again later (%d/%d)",
                    $this->chunkCache->getProcessedCount(),
                    $this->chunkCache->getPublishedCount()
                ),
                $io,
                'fg=black;bg=yellow'
            );

            return -1;
        }

        if ($this->chunkError->hasError()) {
            $this->outputMessage(sprintf("There has been an error:\n\n%s", $this->chunkError->getError()), $io);

            return -1;
        }

        try {
            $output->writeln(' - refining database...');
            $this->refiner->refine(
                $this->recipe ?? $this->recipeFactory->createRecipe($input->getOption('file'))
            );
            $output->writeln(' - recreating indexes...');
            $this->schemaManipulator->recreateIndexes();
            $output->writeln(' - recreating foreign keys...');
            $this->schemaManipulator->recreateForeignKeys();
            $output->writeln(' - updating auto_increments...');
            $this->schemaManipulator->updateAutoIncrements();
        } catch (\Exception $exception) {
            $this->outputMessage(sprintf("There has been an error:\n\n%s", $exception->getMessage()), $io);

            return -1;
        }

        $this->outputMessage('Data moved, constraints and indexes recreated.', $io, 'fg=black;bg=green');

        return 0;
    }

    protected function outputMessage(string $message, SymfonyStyle $io, string $style = 'fg=white;bg=red')
    {
        $io->block($message, null, $style, ' ', true);
    }
}
