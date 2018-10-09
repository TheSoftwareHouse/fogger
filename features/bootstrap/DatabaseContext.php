<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema as Schema;
use Doctrine\DBAL\Types\Type;

class DatabaseContext implements Context
{
    private $source;

    private $target;

    public function __construct(Connection $source, Connection $target)
    {
        error_reporting(E_ALL);
        $this->source = $source;
        $this->target = $target;
    }

    /**
     * @param Connection $connection
     * @throws \Doctrine\DBAL\DBALException
     */
    private function dropSchema(Connection $connection)
    {
        if ($connection->getDriver()->getName() === 'pdo_mysql') {
            $connection->exec('SET FOREIGN_KEY_CHECKS = 0;');
        }
        foreach ($connection->getSchemaManager()->listTables() as $table) {
            $connection->exec(
                sprintf(
                    'DROP TABLE %s CASCADE',
                    $connection->quoteIdentifier($table->getName())
                )
            );
        }
        if ($connection->getDriver()->getName() === 'pdo_mysql') {
            $connection->exec('SET FOREIGN_KEY_CHECKS = 1;');
        }
    }

    /**
     * @Given there is a source database
     * @throws \Doctrine\DBAL\DBALException
     */
    public function thereIsASourceDatabase()
    {
        $this->dropSchema($this->source);
    }

    /**
     * @Given there is an empty target database
     * @throws \Doctrine\DBAL\DBALException
     */
    public function thereIsAnEmptyTargetDatabase()
    {
        $this->dropSchema($this->target);
    }

    /**
     * @Given there is a table :tableName with following columns:
     * @param $tableName
     * @param TableNode $tableNode
     * @throws \Doctrine\DBAL\DBALException
     */
    public function thereIsATableWithFollowingColumns(string $tableName, TableNode $tableNode)
    {
        $columns = $pkColumns = $indexes = $uniqueIndexes = [];
        foreach ($tableNode->getHash() as $row) {
            $column = $this->createColumn($row);
            $columns[] = $column;
            switch ($row['index'] ?? false) {
                case 'primary':
                    $pkColumns[] = $column->getName();
                    break;
                case 'index' :
                    $indexes[] = $column->getName();
                    break;
                case 'unique' :
                    $uniqueIndexes[] = $column->getName();
                    break;
            }
        }
        $table = new Schema\Table($tableName, $columns);
        if ($pkColumns) {
            $table->setPrimaryKey($pkColumns);
        }
        foreach ($indexes as $index) {
            $table->addIndex([$index]);
        }
        foreach ($uniqueIndexes as $index) {
            $table->addUniqueIndex([$index]);
        }
        $this->source->getSchemaManager()->dropAndCreateTable($table);
    }

    /**
     * @param $row
     * @return Schema\Column
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createColumn(array $row): Schema\Column
    {
        $column = new Schema\Column($row['name'], Type::getType($row['type']));
        $column->setLength($row['length'] ?? null);
        $column->setComment($row['comment'] ?? '');
        $column->setNotnull(($row['nullable'] ?? false) !== 'true');

        return $column;
    }

    /**
     * @Given the table :tableName contains following data:
     * @param string $tableName
     * @param TableNode $table
     */
    public function theTableTableContainsFollowingData(string $tableName, TableNode $table)
    {
        foreach ($table->getHash() as $hash) {
            $queryBuilder = $this->source->createQueryBuilder();
            $queryBuilder
                ->insert($this->source->quoteIdentifier($tableName))
                ->values(
                    array_combine(
                        array_map(
                            function ($key) {
                                return $this->source->quoteIdentifier($key);
                            },
                            array_keys($hash)
                        ),
                        array_map(
                            function ($value) {
                                return $value === '' ? 'null' : $this->source->quote($value);
                            },
                            $hash
                        )
                    )
                )
                ->execute();
        }
    }

    /**
     * @Then the table :tablename in target database should have :expected row(s)
     * @param string $tablename
     * @param int $expected
     * @throws Exception
     */
    public function theTableInTargetDatabaseShouldHaveRows(string $tablename, int $expected)
    {
        $queryBuilder = $this->target->createQueryBuilder();
        $count = (int)$queryBuilder
            ->select('count(*)')
            ->from($this->target->quoteIdentifier($tablename))
            ->execute()
            ->fetchColumn();
        if ($count === $expected) {
            return;
        }

        throw new \Exception(
            sprintf(
                'Table contains %d rows, %d expected',
                $count,
                $expected
            )
        );
    }

    private function rowInTableExists(string $tablename, array $columns)
    {
        $queryBuilder = $this->target->createQueryBuilder();
        $queryBuilder
            ->resetQueryParts()
            ->select('count(*)')
            ->from($this->target->quoteIdentifier($tablename));
        $counter = 0;
        foreach ($columns as $key => $value) {
            if ($value === '') {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull($key));
                continue;
            }
            $queryBuilder
                ->andWhere(sprintf('%s = ?', $this->target->quoteIdentifier($key)))
                ->setParameter($counter++, $value);
        }

        return 0 !== (int)$queryBuilder->execute()->fetchColumn();
    }

    /**
     * @Then the table :tablename in target database should contain rows:
     * @param string $tablename
     * @param TableNode $table
     * @throws Exception
     */
    public function theTableInTargetDatabaseShouldContainRows(string $tablename, TableNode $table)
    {
        foreach ($table->getColumnsHash() as $hash) {
            if ($this->rowInTableExists($tablename, $hash)) {
                continue;
            }
            throw new \Exception(sprintf('Row %s not found', json_encode($hash)));
        }
    }

    /**
     * @Then the table :tablename in target database should not contain rows:
     * @param string $tablename
     * @param TableNode $table
     * @throws Exception
     */
    public function theTableInTargetDatabaseShouldNotContainRows(string $tablename, TableNode $table)
    {
        foreach ($table->getColumnsHash() as $hash) {
            if (!$this->rowInTableExists($tablename, $hash)) {
                continue;
            }
            throw new \Exception('row is not in the table');
        }
    }

    /**
     * @Given the :local references :foreign
     * @param string $local
     * @param string $foreign
     */
    public function theUsersSupervisorReferencesUsersEmail(string $local, string $foreign)
    {
        $local = explode('.', $local);
        $foreign = explode('.', $foreign);

        $schemaManager = $this->source->getSchemaManager();
        $schemaManager->createForeignKey(
            new Schema\ForeignKeyConstraint([$local[1]], $foreign[0], [$foreign[1]]),
            $local[0]
        );
    }
}
