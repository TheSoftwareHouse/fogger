<?php

use App\Fogger\Data\ChunkCounter;
use Behat\Behat\Context\Context;

class ChunkCounterContext implements Context
{
    private $chunkCounter;

    public function __construct(ChunkCounter $chunkCounter)
    {
        $this->chunkCounter = $chunkCounter;
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
        $this->assertCountEquals($this->chunkCounter->getPublishedCount(), $expected);
    }

    /**
     * @Then processed tasks counter should equal :expected
     * @param $expected
     * @throws Exception
     */
    public function processedTasksCounterShouldEqual(int $expected)
    {
        $this->assertCountEquals($this->chunkCounter->getProcessedCount(), $expected);
    }
}
