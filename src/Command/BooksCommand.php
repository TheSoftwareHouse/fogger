<?php

namespace App\Command;

use App\Config\ConfigLoader;
use App\Fogger\Data\Mongo\ChunkCache;
use App\Fogger\Data\Mongo\ChunkProducer;
use Faker\Factory;
use MongoDB\Client;
use MongoDB\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BooksCommand extends Command
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
            ->setName('fogger:mongo:books')
            ->setDescription('Mongo Playground - puts example documents to test:books collection')
            ->addOption(
                'count',
                'c',
                InputOption::VALUE_REQUIRED,
                'How many messages to process.',
                1000
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            sprintf(
                "Populating mongo's test:books with %s example documents",
                $input->getOption('count')
            )
        );
        $this->putSomeDocuments($input);

        return 0;
    }

    private function collection(): Collection
    {
        $client = new Client("mongodb://root:example@mongo:27017");
        $db = 'test';
        $collection = 'books';

        return $client->$db->$collection;
    }

    private function putSomeDocuments(InputInterface $input)
    {
        $collection = $this->collection();
        $faker = Factory::create();
        foreach (range(1, $input->getOption('count')) as $i) {
            $collection->insertOne(
                [
                    'authors' => [
                        ['firstName' => $faker->firstName, 'lastName' => $faker->lastName, 'born' => $faker->date],
                        ['firstName' => $faker->firstName, 'lastName' => $faker->lastName, 'born' => $faker->date()],
                    ],
                    'title' => $faker->catchPhrase,
                    'created' => $faker->date,
                    'published' => new \DateTime(),
                    'review' => $faker->paragraph,
                    'publisher' => [
                        'name' => $faker->company,
                        'contacts' => [
                            ['firstName' => $faker->firstName, 'lastName' => $faker->lastName],
                            ['firstName' => $faker->firstName, 'lastName' => $faker->lastName],
                            ['firstName' => $faker->firstName, 'lastName' => $faker->lastName],
                            ['firstName' => $faker->firstName, 'lastName' => $faker->lastName],
                        ],
                    ],
                ]
            );
        }
    }
}
