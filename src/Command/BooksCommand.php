<?php

namespace App\Command;

use App\Config\ConfigLoader;
use Faker\Factory;
use MongoDB\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BooksCommand extends Command
{
    private $configLoader;

    private $client;

    public function __construct(ConfigLoader $loader, Client $client)
    {
        $this->configLoader = $loader;
        $this->client = $client;
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
                "Populating mongo's books collection with %s example documents",
                $input->getOption('count')
            )
        );
        $this->putSomeDocuments($input);

        return 0;
    }

    private function putSomeDocuments(InputInterface $input)
    {
        $config = $this->configLoader->load(ConfigLoader::DEFAULT_FILENAME);
        $db = $config->getTarget();
        $collection = $this->client->$db->books;
        $faker = Factory::create();
        foreach (range(1, $input->getOption('count')) as $i) {
            $collection->insertOne(
                [
                    '_id' => uniqid(),
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
