<?php
/**
 * @package   Mediarox_BilligerDeTrackingPixel
 * @copyright Copyright 2020 (c) mediarox UG (haftungsbeschraenkt) (http://www.mediarox.de)
 * @author    Marcus Bernt <mbernt@mediarox.de>
 */

namespace Mediarox\BilligerDeTrackingPixel\Model\Source\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class MethodOptions
 */
class MethodOptions implements OptionSourceInterface
{
    const METHOD_INCLUDE_ORDER_ITEMS = 'include_order_items';
    const METHOD_EXCLUDE_ORDER_ITEMS = 'exclude_order_items';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::METHOD_INCLUDE_ORDER_ITEMS,
                'label' => __('Inlcude Order Items')
            ],
            [
                'value' => self::METHOD_EXCLUDE_ORDER_ITEMS,
                'label' => __('Exclude Order Items')
            ]
        ];
    }
}
