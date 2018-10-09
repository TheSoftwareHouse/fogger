<?php

namespace App\Fogger\Recipe;

use App\Fogger\Schema\RelationGroups\GrouppedRelationColumns;
use App\Fogger\Schema\RelationGroups\RelationColumn;
use App\Fogger\Schema\RelationGroupsFactory;

class MaskReplicator
{
    private $relationGroups;

    public function __construct(RelationGroupsFactory $relationGroupsFactory)
    {
        $this->relationGroups = $relationGroupsFactory->createFromDBAL();
    }

    private function replicateMask(
        GrouppedRelationColumns $group,
        StrategyDefinition $mask,
        Recipe $recipe
    ) {
        /** @var RelationColumn $relationColumn */
        foreach ($group->getColumns() as $relationColumn) {
            $table = $recipe->getTable($relationColumn->getTable());
            $table->addMask(implode('|', $relationColumn->getColumns()), $mask);
        }
    }

    private function replicateMasksInTable(Table $table, Recipe $recipe)
    {
        foreach ($table->getMasks() as $column => $mask) {
            if (null === $group = $this->relationGroups
                    ->getGroupByTableAndColumn($table->getName(), $column)) {
                continue;
            }
            $this->replicateMask($group, $mask, $recipe);
        }
    }

    public function replicateMasksToRelatedColumns(Recipe $recipe)
    {
        foreach ($recipe->getTables() as $table) {
            $this->replicateMasksInTable($table, $recipe);
        }
    }
}
