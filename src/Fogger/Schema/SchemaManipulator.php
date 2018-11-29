<?php

namespace App\Fogger\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema as DBAL;

class SchemaManipulator
{
    private $sourceSchema;

    private $targetSchema;

    public function __construct(Connection $source, Connection $target)
    {
        $this->sourceSchema = $source->getSchemaManager();
        $this->targetSchema = $target->getSchemaManager();
    }

    /**
     * @return bool
     */
    public function isTargetSchemaEmpty()
    {
        return empty($this->targetSchema->listTableNames());
    }

    public function dropTargetSchema()
    {
        /** @var DBAL\Table[] $tables */
        $tables = $this->targetSchema->listTables();
        foreach ($tables as $table) {
            foreach ($table->getForeignKeys() as $fk) {
                $this->targetSchema->dropForeignKey($fk->getName(), $table->getName());
            }
        }
        foreach ($tables as $table) {
            $this->targetSchema->dropTable($table);
        }
    }

    /**
     * @throws DBAL\SchemaException
     */
    public function copySchemaDroppingIndexesAndForeignKeys()
    {
        $sourceTables = $this->sourceSchema->listTables();
        /** @var DBAL\Table $table */
        foreach ($sourceTables as $table) {
            foreach ($table->getColumns() as $column) {
                $column->setAutoincrement(false);
            }
            foreach ($table->getForeignKeys() as $fk) {
                $table->removeForeignKey($fk->getName());
            }
            foreach ($table->getIndexes() as $index) {
                $table->dropIndex($index->getName());
            }
            $this->targetSchema->createTable($table);
        }
    }

    private function recreateIndexesOnTable(DBAL\Table $table)
    {
        foreach ($table->getIndexes() as $index) {
            echo(sprintf(
                "  - %s's index %s on %s\n",
                $table->getName(),
                $index->getName(),
                implode(', ', $index->getColumns())
            ));
            $this->targetSchema->createIndex($index, $table->getName());
        }
        /** @var DBAL\Column $column */
        foreach ($table->getColumns() as $column) {
            if ($column->getAutoincrement()) {
                $this->targetSchema->alterTable(
                    new DBAL\TableDiff($table->getName(), [], [new DBAL\ColumnDiff($column->getName(), $column)])
                );
            }
        }
    }

    private function recreateForeignKeysOnTable(DBAL\Table $table)
    {
        foreach ($table->getForeignKeys() as $fk) {
            echo(sprintf(
                "  - %s.%s => %s.%s\n",
                $fk->getLocalTableName(),
                implode('_', $fk->getLocalColumns()),
                $fk->getForeignTableName(),
                implode('_', $fk->getForeignColumns())
            ));
            $this->targetSchema->createForeignKey($fk, $table->getName());
        }
    }

    public function recreateIndexes()
    {
        $sourceTables = $this->sourceSchema->listTables();
        foreach ($sourceTables as $table) {
            $this->recreateIndexesOnTable($table);
        }
    }

    public function recreateForeignKeys()
    {
        $sourceTables = $this->sourceSchema->listTables();
        foreach ($sourceTables as $table) {
            $this->recreateForeignKeysOnTable($table);
        }
    }
}
