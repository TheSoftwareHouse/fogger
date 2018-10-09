<?php

namespace App\Config;

use App\Config\Model\Config;
use Doctrine\DBAL\Connection;

class ConfigFactory
{
    private $sourceSchemaManager;

    private $tableConfigFactory;

    public function __construct(Connection $connection, TableConfigFactory $tableConfigFactory)
    {
        $this->sourceSchemaManager = $connection->getSchemaManager();
        $this->tableConfigFactory = $tableConfigFactory;
    }

    public function createFromDBAL()
    {
        $dbalTables = $this->sourceSchemaManager->listTables();
        $config = new Config();

        foreach ($dbalTables as $dbalTable) {
            $config->addTable($dbalTable->getName(), $this->tableConfigFactory->createFromDBALTable($dbalTable));
        }

        return $config;
    }
}
