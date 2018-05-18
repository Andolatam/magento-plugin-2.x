<?php

namespace Improntus\Ando\Controller\Adminhtml\Cuentacorriente;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Controller\Adminhtml\Cuentacorriente
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $_resultRedirect;

    /**
     * @var \Improntus\Ando\Model\Ando
     */
    protected $_ando;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Solicitar constructor.
     * @param Action\Context $context
     * @param \Improntus\Ando\Model\Ando $ando
     * @param ResultFactory $resultFactory
     * @param \Magento\Framework\Message\ManagerInterface $manager
     */
    public function __construct
    (
        Action\Context $context,
        \Improntus\Ando\Model\Ando $ando,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $manager
    )
    {
        $this->_ando = $ando;
        $this->_resultRedirect = $resultFactory;
        $this->messageManager = $manager;

        parent::__construct($context);
    }

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}


