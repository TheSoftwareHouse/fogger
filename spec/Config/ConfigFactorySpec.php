<?php

namespace spec\App\Config;

use App\Config\Model\Config;
use App\Config\Model\TableConfig;
use App\Config\TableConfigFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use PhpSpec\ObjectBehavior;

class ConfigFactorySpec extends ObjectBehavior
{
    function let(Connection $connection, TableConfigFactory $tableConfigFactory, AbstractSchemaManager $schemaManager)
    {
        $connection->getSchemaManager()->willReturn($schemaManager);
        $this->beConstructedWith($connection, $tableConfigFactory);
    }

    function it_creates_config_with_tables_fetched_from_DBAL_schema(
        Table $table,
        TableConfigFactory $tableConfigFactory,
        AbstractSchemaManager $schemaManager
    ) {
        $config = new Config();
        $tableConfig = new TableConfig();
        $config->addTable('table', $tableConfig);

        $schemaManager->listTables()->willReturn([$table]);
        $table->getName()->willReturn('table');
        $tableConfigFactory->createFromDBALTable($table)->willReturn($tableConfig);

        $this->createFromDBAL()->shouldBeLike($config);
    }

    function it_created_empty_config_when_there_are_no_tables_in_schema(AbstractSchemaManager $schemaManager)
    {
        $config = new Config();

        $schemaManager->listTables()->willReturn([]);

        $this->createFromDBAL()->shouldBeLike($config);
    }
}
