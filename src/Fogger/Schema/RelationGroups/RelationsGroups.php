<?php

namespace App\Fogger\Schema\RelationGroups;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;

class RelationsGroups
{
    private $groups = [];

    private function getGroupContainingColumn(RelationColumn $column)
    {
        /** @var GrouppedRelationColumns $group */
        foreach ($this->groups as $group) {
            if ($group->contains($column)) {
                return $group;
            }
        }

        return null;
    }

    private function newGroup()
    {
        $group = new GrouppedRelationColumns();
        $this->groups[] = $group;

        return $group;
    }

    public function addForeignKey(ForeignKeyConstraint $foreignKeyConstraint)
    {
        $local = new RelationColumn(
            $foreignKeyConstraint->getLocalTableName(),
            $foreignKeyConstraint->getLocalColumns()
        );

        $foreign = new RelationColumn(
            $foreignKeyConstraint->getForeignTableName(),
            $foreignKeyConstraint->getForeignColumns()
        );

        $g1 = $this->getGroupContainingColumn($local);
        $g2 = $this->getGroupContainingColumn($foreign);

        $group = $g1 ?? $g2 ?? $this->newGroup();
        $group->addRelationColumn($local);
        $group->addRelationColumn($foreign);
    }

    public function getGroupByTableAndColumn(string $table, string $column): ?GrouppedRelationColumns
    {
        foreach ($this->groups as $group) {
            if ($group->containsByKey(
                sprintf
                (
                    RelationColumn::DESCRIPTOR_PATTERN,
                    $table,
                    $column
                )
            )) {
                return $group;
            }
        }

        return null;
    }
}
