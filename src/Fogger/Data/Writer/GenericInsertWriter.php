<?php

namespace App\Fogger\Data\Writer;

use Doctrine\DBAL\Connection;

class GenericInsertWriter implements ChunkWriterInterface
{
    const FLUSH_RATE = 1000;

    private $target;

    private $inserts = [];

    public function __construct(Connection $target)
    {
        $this->target = $target;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function flush()
    {
        if (!count($this->inserts)) {
            return;
        }
        $this->target->beginTransaction();
        foreach ($this->inserts as $insert) {
            $this->target->exec($insert);
        }
        $this->target->commit();
        $this->inserts = [];
    }

    /**
     * @param string $table
     * @param array $data
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insert(string $table, array $data)
    {
        $counter = 0;
        $queryBuilder = $this->target->createQueryBuilder();
        $this->inserts = [];
        foreach ($data as $row) {
            $this->inserts[] = $queryBuilder
                ->insert($this->target->quoteIdentifier($table))
                ->values(
                    array_combine(
                        array_map(
                            function ($key) {
                                return $this->target->quoteIdentifier($key);
                            },
                            array_keys($row)
                        ),
                        array_map(
                            function ($value) {
                                return $value === null ? 'null' : $this->target->quote($value);
                            },
                            $row
                        )
                    )
                )->getSQL();
            if (!(++$counter % self::FLUSH_RATE)) {
                $this->flush();
            }
        }
        $this->flush();
    }

    public function isApplicable(): bool
    {
        return true;
    }
}
