<?php

namespace App\Fogger\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema as Schema;

class ForeignKeysExtractor
{
    private $source;

    public function __construct(Connection $source)
    {
        $this->source = $source->getSchemaManager();
    }

    public function findForeignKeysReferencingTable(string $tableName): array
    {
        $foreignKeys = [];
        /** @var Schema\Table $table */
        foreach ($this->source->listTables() as $table) {
            /** @var Schema\ForeignKeyConstraint $foreignKeyConstraint */
            foreach ($table->getForeignKeys() as $foreignKeyConstraint) {
                if ($foreignKeyConstraint->getForeignTableName() === $tableName) {
                    $foreignKeys[] = $foreignKeyConstraint;
                }
            }
        }

        return $foreignKeys;
    }

    /**
     * @param Schema\ForeignKeyConstraint $foreignKey
     * @return bool
     * @throws Schema\SchemaException
     */
    public function isLocalColumnNullable(Schema\ForeignKeyConstraint $foreignKey): bool
    {
        $localColumn = $foreignKey->getLocalColumns()[0];
        $localTable = $foreignKey->getLocalTableName();
        $table = $this->source->listTableDetails($localTable);
        $column = $table->getColumn($localColumn);

        return !$column->getNotnull();
    }
}
