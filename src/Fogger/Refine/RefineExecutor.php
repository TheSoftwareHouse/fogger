<?php

namespace App\Fogger\Refine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema as Schema;

class RefineExecutor
{
    private $target;

    public function __construct(Connection $target)
    {
        $this->target = $target;
    }

    private function innerSelectFromCopiedTable(Schema\ForeignKeyConstraint $foreignKey)
    {
        return $this->target->createQueryBuilder()
            ->select($this->target->quoteIdentifier('__tmp.'.$foreignKey->getForeignColumns()[0]))
            ->from(
                '('.$this->target->createQueryBuilder()
                    ->select($this->target->quoteIdentifier($foreignKey->getForeignColumns()[0]))
                    ->from($this->target->quoteIdentifier($foreignKey->getForeignTableName()))
                    ->getSql().')',
                '__tmp'
            )
            ->getSQL();
    }

    public function delete(Schema\ForeignKeyConstraint $foreignKey): int
    {
        return $this->target->createQueryBuilder()
            ->delete($this->target->quoteIdentifier($foreignKey->getLocalTableName()))
            ->where(
                $this->target->createQueryBuilder()->expr()->notIn(
                    $this->target->quoteIdentifier($foreignKey->getLocalColumns()[0]),
                    $this->innerSelectFromCopiedTable($foreignKey)
                )
            )
            ->execute();
    }

    public function setNulls(Schema\ForeignKeyConstraint $foreignKey)
    {
        $this->target->createQueryBuilder()
            ->update($this->target->quoteIdentifier($foreignKey->getLocalTableName()))
            ->set($this->target->quoteIdentifier($foreignKey->getLocalColumns()[0]), ':val')
            ->where(
                $this->target->createQueryBuilder()->expr()->notIn(
                    $this->target->quoteIdentifier($foreignKey->getLocalColumns()[0]),
                    $this->innerSelectFromCopiedTable($foreignKey)
                )
            )
            ->setParameter('val', null)
            ->execute();
    }
}
