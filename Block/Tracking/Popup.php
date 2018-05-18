<?php

namespace Improntus\Ando\Block\Tracking;

use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Class Popup
 * @package Improntus\Ando\Block\Tracking
 */
class Popup extends \Magento\Shipping\Block\Tracking\Popup
{
    /**
     * @var \Improntus\Ando\Model\Webservice
     */
    protected $_andoWs;

    /**
     * Popup constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param \Improntus\Ando\Model\Webservice $webservice
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Improntus\Ando\Model\Webservice $webservice,
        array $data = []
    )
    {
        $this->_andoWs = $webservice;

        parent::__construct($context, $registry, $dateTimeFormatter, $data);
    }

    /**
     * @return string|null
     */
    public function getShipmentAndoInfo()
    {
        $ws = null;

        foreach ($this->getTrackingInfo() as $_track)
        {
            foreach ($_track as $counter => $track)
            {
                $shipmentId = $track->getArguments()[0];
            }
        }

        if(isset($shipmentId))
        {
            $ws = $this->_andoWs->trackShipment($shipmentId);
        }

        return $ws;
    }
}
