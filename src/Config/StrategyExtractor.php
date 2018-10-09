<?php

namespace App\Config;

use App\Config\Model\ColumnConfig;

class StrategyExtractor
{
    private const PATTERN = '/fogger::(\w+)({.*})*/';

    public function extract(string $comment = null): ColumnConfig
    {
        preg_match(self::PATTERN, $comment, $matches);
        $name = $matches[1] ?? 'none';

        $options = json_decode($matches[2] ?? '[]', true);

        return new ColumnConfig($name, $options);
    }
}
