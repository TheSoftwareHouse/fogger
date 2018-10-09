<?php

namespace App\Config;

use App\Config\Model\ColumnConfig;
use Doctrine\DBAL\Schema as DBAL;

class ColumnConfigFactory
{
    private $extractor;

    public function __construct(StrategyExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    public function createFromDBALColumn(DBAL\Column $dbalColumn): ColumnConfig
    {
        return $this->extractor->extract($dbalColumn->getComment());
    }
}
