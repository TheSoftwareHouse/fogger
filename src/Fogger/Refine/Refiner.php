<?php

namespace App\Fogger\Refine;

use App\Fogger\Recipe\Recipe;
use App\Fogger\Recipe\Table;
use App\Fogger\Schema\ForeignKeysExtractor;
use App\Fogger\Subset\NoSubset;
use Doctrine\DBAL\Schema as Schema;

class Refiner
{
    private $extractor;

    private $refineExecutor;

    public function __construct(
        ForeignKeysExtractor $extractor,
        RefineExecutor $refineExecutor
    ) {
        $this->extractor = $extractor;
        $this->refineExecutor = $refineExecutor;
    }

    /**
     * @param Table $table
     * @throws Schema\SchemaException
     */
    private function refineIfSubsetted(Table $table): void
    {
        if ($table->getSubsetName() === NoSubset::STRATEGY_NAME) {
            return;
        }
        $this->refineTable($table->getName());
    }

    /**
     * @param Schema\ForeignKeyConstraint $foreignKey
     * @throws Schema\SchemaException
     */
    private function runQueryFor(Schema\ForeignKeyConstraint $foreignKey)
    {
        echo(sprintf(
            "    - %s.%s => %s.%s\n",
            $foreignKey->getLocalTableName(),
            implode('_', $foreignKey->getLocalColumns()),
            $foreignKey->getForeignTableName(),
            implode('_', $foreignKey->getForeignColumns())
        ));
        if ($this->extractor->isLocalColumnNullable($foreignKey)) {
            $this->refineExecutor->setNulls($foreignKey);

            return;
        }
        if ($this->refineExecutor->delete($foreignKey)) {
            $this->refineTable($foreignKey->getLocalTableName());
        }
    }

    /**
     * @param string $tabletableName
     * @throws Schema\SchemaException
     */
    private function refineTable(string $tabletableName)
    {
        echo('  - refining '.$tabletableName."\n");
        /** @var Schema\ForeignKeyConstraint $foreignKey */
        foreach ($this->extractor->findForeignKeysReferencingTable($tabletableName) as $foreignKey) {
            $this->runQueryFor($foreignKey);
        }

    }

    /**
     * @param Recipe $recipe
     * @throws Schema\SchemaException
     */
    public function refine(Recipe $recipe)
    {
        /** @var Table $table */
        foreach ($recipe->getTables() as $table) {
            $this->refineIfSubsetted($table);
        }
        foreach ($recipe->getExcludes() as $excluded) {
            $this->refineTable($excluded);
        }
    }
}
