<?php

namespace App\Fogger\Data\Writer;

use Doctrine\DBAL\Connection;
use Exception;

class MysqlInfileWriter implements ChunkWriterInterface
{
    private $target;

    private $cacheDir;

    public function __construct(Connection $target, string $cacheDir)
    {
        $this->target = $target;
        $this->cacheDir = $cacheDir;
    }

    private function forgeTempFilename()
    {
        return sprintf('%s/%s.txt', $this->cacheDir, uniqid());
    }

    /**
     * @param string $table
     * @param array $inserts
     * @throws \Doctrine\DBAL\DBALException
     */
    private function loadInfile(string $table, array $inserts)
    {
        $filename = $this->forgeTempFilename();
        file_put_contents($filename, implode("\n", $inserts));
        $this->target->exec(
            sprintf(
                "LOAD DATA LOCAL INFILE '%s' INTO TABLE %s",
                $filename,
                $table
            ).
            " FIELDS TERMINATED BY ',' ENCLOSED BY '\'' LINES TERMINATED BY '\n' STARTING BY ''"
        );
        unlink($filename);
    }

    private function forgeRow(array $row): string
    {
        return implode(
            ',',
            array_map(
                function ($item) {
                    return $item === null ? '\N' : $this->target->quote($item);
                },
                $row
            )
        );
    }

    /**
     * @param string $table
     * @param array $data
     * @throws Exception
     */
    public function insert(string $table, array $data)
    {
        $inserts = [];
        foreach ($data as $row) {
            $inserts[] = $this->forgeRow($row);
        }
        $this->loadInfile($table, $inserts);
    }

    public function isApplicable(): bool
    {
        return $this->target->getDriver()->getName() === 'pdo_mysql';
    }
}
