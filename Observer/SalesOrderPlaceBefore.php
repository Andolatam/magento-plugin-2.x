<?php
namespace Improntus\Ando\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesEventQuoteSubmitBeforeObserver
 *
 * @author Improntus <http://www.improntus.com>
 * @package Ids\Andreani\Observer
 */
class SalesOrderPlaceBefore implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * SalesOrderPlaceBefore constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $andoQuoteId = $this->_checkoutSession->getAndoQuoteId();
        $order = $observer->getEvent()->getOrder();

        $metodoEnvio = explode('_',$order->getShippingMethod());

        if($metodoEnvio[0] ==  \Improntus\Ando\Model\Carrier\AndoMoto::CARRIER_CODE ||
            $metodoEnvio[0] ==  \Improntus\Ando\Model\Carrier\AndoBicicleta::CARRIER_CODE)
        {
            $order->setAndoQuoteId($andoQuoteId);
        }

        return $this;
    }
}
