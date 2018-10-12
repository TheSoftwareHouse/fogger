<?php

namespace App\Fogger\Mask;


use App\Fogger\Mask\Exception\UnknownMaskException;

class MaskStrategyProvider
{
    private $masks = [];

    public function __construct(iterable $masks)
    {
        foreach ($masks as $mask) {
            $this->addMask($mask);
        }
    }

    public function addMask(MaskStrategyInterface $mask)
    {
        $this->masks[] = $mask;
    }

    /**
     * @param string $name
     * @return MaskStrategyInterface
     * @throws UnknownMaskException
     */
    public function getMask(string $name): MaskStrategyInterface
    {
        foreach ($this->masks as $mask) {
            if ($mask->supports($name)) {
                return $mask;
            }
        }

        throw new UnknownMaskException('Unknown mask "'.$name.'".');
    }
}
