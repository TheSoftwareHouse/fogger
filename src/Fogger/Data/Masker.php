<?php

namespace App\Fogger\Data;

use App\Fogger\Mask\MaskStrategyInterface;
use App\Fogger\Mask\MaskStrategyProvider;
use App\Fogger\Recipe\StrategyDefinition;

class Masker
{
    private $maskStrategyProvider;

    public function __construct(MaskStrategyProvider $maskStrategyProvider)
    {
        $this->maskStrategyProvider = $maskStrategyProvider;
    }

    /**
     * @param array $data
     * @param array $masks
     * @return array
     * @throws \App\Fogger\Mask\Exception\UnknownMaskException
     */
    public function applyMasks(array $data, array $masks): array
    {
        /**
         * @var  $column
         * @var StrategyDefinition $definition
         */
        foreach ($masks as $column => $definition) {
            $data = $this->maskData($data, $column, $definition);
        }


        return $data;
    }

    /**
     * @param array $data
     * @param string $column
     * @param StrategyDefinition $definition
     * @return array
     * @throws \App\Fogger\Mask\Exception\UnknownMaskException
     */
    private function maskData(array $data, string $column, StrategyDefinition $definition): array
    {
        foreach ($data as $key => $row) {
            $data[$key] = $this->maskRow($row, $column, $definition);
        }

        return $data;
    }

    /**
     * @param array $row
     * @param string $column
     * @param StrategyDefinition $definition
     * @return array
     * @throws \App\Fogger\Mask\Exception\UnknownMaskException
     */
    private function maskRow(array $row, string $column, StrategyDefinition $definition): array
    {
        $row[$column] = $this->maskStrategyProvider->getMask($definition->getName())
            ->apply($row[$column], $definition->getOptions());

        return $row;
    }
}
