<?php

namespace App\Fogger\Schema;

use App\Fogger\Schema\RelationGroups\RelationsGroups;
use Doctrine\DBAL\Connection;

class RelationGroupsFactory
{
    private $sourceSchema;

    public function __construct(Connection $connection)
    {
        $this->sourceSchema = $connection->getSchemaManager();
    }

    public function createFromDBAL()
    {
        $groups = new RelationsGroups();

        foreach ($this->sourceSchema->listTables() as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                $groups->addForeignKey($foreignKey);
            }
        }

        return $groups;
    }
}
