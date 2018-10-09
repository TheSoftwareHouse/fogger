<?php

namespace App\Config;

use App\Config\Model\TableConfig;
use Doctrine\DBAL\Schema as DBAL;

class TableConfigFactory
{
    private $columnConfigFactory;

    public function __construct(ColumnConfigFactory $columnConfigFactory)
    {
        $this->columnConfigFactory = $columnConfigFactory;
    }

    public function createFromDBALTable(DBAL\Table $dbalTable)
    {
        $table = new TableConfig();
        foreach ($dbalTable->getColumns() as $dbalColumn) {
            $table->addColumn(
                $dbalColumn->getName(),
                $this->columnConfigFactory->createFromDBALColumn($dbalColumn)
            );
        }

        return $table;
    }
}
