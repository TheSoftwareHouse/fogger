<?php

namespace App\Fogger\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema as DBAL;

class SchemaManipulator
{
    private $sourceSchema;

    private $sourceConnection;

    private $targetSchema;

    public function __construct(Connection $source, Connection $target)
    {
        $this->sourceConnection = $source;
        $this->targetConnection = $target;
        $this->sourceSchema = $source->getSchemaManager();
        $this->targetSchema = $target->getSchemaManager();
    }

    /**
     * @throws DBAL\SchemaException
     */
    public function copySchemaDroppingIndexesAndForeignKeys()
    {
        $sourceTables = $this->sourceSchema->listTables();
        /** @var DBAL\Table $table */
        foreach ($sourceTables as $table) {
            $primary = NULL;
            $auto_increments = NULL;
            foreach ($table->getColumns() as $column) {
                if ($column->getAutoincrement()) {
                    $auto_increments[] = clone $column;
                    $column->setAutoincrement(false);

                }
            }
            foreach ($table->getForeignKeys() as $fk) {
                $table->removeForeignKey($fk->getName());
            }
            foreach ($table->getIndexes() as $index) {
                if ($index->getName() == "PRIMARY") {
                    $primary = $index;
                }
                $table->dropIndex($index->getName());
            }
            if (!$table->hasOption('collate')) {
                $table->addOption(
                    'collate',
                    $this->sourceConnection->getParams()['driverOptions']['collate']
                );
            }
            $this->targetSchema->createTable($table);
            if ($primary !== NULL) {
                $this->targetSchema->createIndex($primary, $table->getName());
            }
            /** @var DBAL\Column $column */
            foreach ($auto_increments as $column) {
                $this->targetSchema->alterTable(
                    new DBAL\TableDiff($table->getName(), [], [new DBAL\ColumnDiff($column->getName(), $column)])
                );
            }
        }
    }

    private function recreateIndexesOnTable(DBAL\Table $table)
    {
        $indexes = [];
        foreach ($table->getIndexes() as $index) {
            if ($index->getName() != "PRIMARY") {
                echo(sprintf(
                    "  - %s's index %s on %s\n",
                    $table->getName(),
                    $index->getName(),
                    implode(', ', $index->getColumns())
                ));
                $indexes[$index->getName()] = $index;
            }

        }
        $this->targetSchema->alterTable(
            new DBAL\TableDiff($table->getName(), [], [], [], $indexes)
        );
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

    public function updateAutoIncrements()
    {
        $sourceTables = $this->sourceSchema->listTables();
        foreach ($sourceTables as $table) {
            foreach ($table->getColumns() as $column) {
                if ($column->getAutoincrement()) {
                    $auto_inc = $this->sourceConnection->fetchAssoc(
                        "SHOW TABLE STATUS WHERE Name LIKE '".$table->getName()."'"
                    )['Auto_increment'];
                    echo(sprintf(
                        "  - %s auto_increment to %s\n",
                        $table->getName(),
                        $auto_inc
                    ));
                    $this->targetConnection->query(
                        "ALTER TABLE ".$table->getName()." AUTO_INCREMENT = ". $auto_inc
                    );
                    break;
                }
            }
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
