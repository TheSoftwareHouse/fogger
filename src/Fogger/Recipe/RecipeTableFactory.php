<?php

namespace App\Fogger\Recipe;

use Doctrine\DBAL\Schema as DBAL;
use App\Config\Model as Config;

class RecipeTableFactory
{
    private function addMask(Table $table, Config\ColumnConfig $column, $columnName): void
    {
        if ($column->getMaskStrategy() != Config\ColumnConfig::NONE_STRATEGY) {
            $table->addMask(
                $columnName,
                new StrategyDefinition($column->getMaskStrategy(), $column->getOptions())
            );
        }
    }

    /**
     * @param DBAL\Table $table
     * @return null|string
     * @throws \Doctrine\DBAL\DBALException
     */
    private function findSortBy(DBAL\Table $table): ?string
    {
        if ($table->getPrimaryKey() && 1 === count($table->getPrimaryKeyColumns())) {
            return $table->getPrimaryKeyColumns()[0];
        }
        foreach ($table->getIndexes() as $index) {
            if ($index->isUnique() && 1 === count($index->getColumns())) {
                return $index->getColumns()[0];
            }
        }
        if ($table->getPrimaryKey()) {
            return $table->getPrimaryKeyColumns()[0];
        }
        foreach ($table->getIndexes() as $index) {
            if ($index->isUnique()) {
                foreach ($index->getColumns() as $column) {
                    if ($table->getColumn($column)->getNotnull()) {
                        return $column;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param DBAL\Table $dbalTable
     * @param int $chunkSize
     * @param Config\TableConfig|null $tableConfig
     * @return Table
     * @throws \Doctrine\DBAL\DBALException
     */
    public function createRecipeTable(
        DBAL\Table $dbalTable,
        int $chunkSize,
        Config\TableConfig $tableConfig = null
    ): Table {
        if ($tableConfig && $subsetStrategy = $tableConfig->getSubsetStrategy()) {
            $subset = new StrategyDefinition($subsetStrategy, $tableConfig->getSubsetOptions());
        }

        $table = new Table(
            $dbalTable->getName(),
            $chunkSize,
            $this->findSortBy($dbalTable),
            $subset ?? new StrategyDefinition('noSubset')
        );

        if (!$tableConfig) {
            return $table;
        }

        foreach ($tableConfig->getColumns() as $key => $column) {
            $this->addMask($table, $column, $key);
        }

        return $table;
    }
}
