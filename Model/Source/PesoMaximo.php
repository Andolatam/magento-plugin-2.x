<?php

namespace Improntus\Ando\Model\Source;

/**
 * Class PesoMaximo
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Model\Source
 */
class PesoMaximo implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            '1'  => '1 kg',
            '3'  => '3 kg',
            '8'  => '8 kg'
        ];
    }
}
