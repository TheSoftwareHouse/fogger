<?php

namespace App\Fogger\Mask;

final class JSONWhitelistMask extends AbstractMask
{
    public function apply(?string $value, array $options = []): ?string
    {
        if ((null === $value) or ("" === $value )) {
            return $value;
        }

        $whitelist = $options['whitelist'] ?? [];
        
        $result = [];
        $jsonArray = json_decode($value, true);

        foreach ($whitelist as $field) {
            if (array_key_exists($field, $jsonArray)) {
                $result[$field] = $jsonArray[$field];
            }
        }
        return json_encode($result);
    }

    protected function getMaskName(): string
    {
        return 'jsonwhitelist';
    }
}
