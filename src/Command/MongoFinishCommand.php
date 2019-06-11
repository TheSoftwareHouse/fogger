<?php

namespace App\Command;

use App\Config\ConfigLoader;
use MongoDB\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MongoFinishCommand extends Command
{
    private $client;

    private $configLoader;

    public function __construct(Client $client, ConfigLoader $loader)
    {
        $this->client = $client;
        $this->configLoader = $loader;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fogger:mongo:finish')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_REQUIRED,
                'Where should the command look for a config file. Defaults to fogger.yaml in root folder.',
                ConfigLoader::DEFAULT_FILENAME
            )
            ->setDescription('Recreate indexes on collections. ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->configLoader->load($input->getOption('file'));
        $source = $config->getSource();
        $target = $config->getTarget();
        foreach ($config->getCollections() as $collectionName => $collectionConfig) {
            $sourceCollection = $this->client->$source->$collectionName;
            $targetCollectionName = $collectionName.$config->getSuffix();
            $targetCollection = $this->client->$target->$targetCollectionName;
            foreach ($sourceCollection->listIndexes() as $index) {
                $key = $index->getKey();
                if (1 === count($key) && isset($key['_id'])) {
                    continue;
                }
                $targetCollection->createIndex($index->getKey());
            }
        }
    }
}
