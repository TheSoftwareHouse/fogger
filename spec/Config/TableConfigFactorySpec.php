<?php

namespace spec\App\Config;

use App\Config\ColumnConfigFactory;
use App\Config\Model\ColumnConfig;
use App\Config\Model\TableConfig;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use PhpSpec\ObjectBehavior;

class TableConfigFactorySpec extends ObjectBehavior
{
    function let(ColumnConfigFactory $columnConfigFactory)
    {
        $this->beConstructedWith($columnConfigFactory);
    }

    function it_creates_table_config_object_from_dbal_schema_table_instance(
        Table $table,
        Column $schemaColumn,
        ColumnConfigFactory $columnConfigFactory
    ) {
        $schemaColumn->getName()->willReturn('column');
        $table->getColumns()->willReturn([$schemaColumn]);

        $columnConfig = new ColumnConfig('none');

        $columnConfigFactory->createFromDBALColumn($schemaColumn)->willReturn($columnConfig);

        $instance = new TableConfig();
        $instance->addColumn('column', $columnConfig);

        $this->createFromDBALTable($table)->shouldBeLike($instance);
    }

    function it_creates_table_config_object_from_dbal_schema_table_instance_no_columns(Table $table)
    {

        $table->getColumns()->willReturn([]);
        $instance = new TableConfig();

        $this->createFromDBALTable($table)->shouldBeLike($instance);
    }
}
