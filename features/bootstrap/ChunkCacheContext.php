<?php

use App\Fogger\Data\ChunkCache;
use Behat\Behat\Context\Context;

class ChunkCacheContext implements Context
{
    private $chunkCache;

    public function __construct(ChunkCache $chunkCache)
    {
        $this->chunkCache = $chunkCache;
    }

    /**
     * @param int $value
     * @param int $expected
     * @throws Exception
     */
    private function assertCountEquals(int $value, int $expected)
    {
        if ($value === $expected) {
            return;
        }

        throw new \Exception(
            sprintf(
                'Counter equals %d, %d expected',
                $value,
                $expected
            )
        );
    }

    /**
     * @Then published tasks counter should equal :expected
     * @param $expected
     * @throws Exception
     */
    public function publishedTasksCounterShouldEqual(int $expected)
    {
        $this->assertCountEquals($this->chunkCache->getPublishedCount(), $expected);
    }

    /**
     * @Then processed tasks counter should equal :expected
     * @param $expected
     * @throws Exception
     */
    public function processedTasksCounterShouldEqual(int $expected)
    {
        $this->assertCountEquals($this->chunkCache->getProcessedCount(), $expected);
    }

    /**
     * @Given the task queue is empty
     */
    public function theTaskQueueIsEmpty()
    {
        $this->chunkCache->reset();
    }
}
