<?php

namespace Improntus\Ando\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Improntus\Ando\Helper\Data as AndoHelper;
use Improntus\Ando\Model\Webservice;
use Magento\Framework\Xml\Security;

/**
 * Class AndoMoto
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Model\Carrier
 */
class AndoMoto extends AbstractCarrierOnline implements CarrierInterface
{
    const CARRIER_CODE = 'andomoto';

    /**
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    /**
     * @var
     */
    protected $_webservice;

    /**
     * @var AndoHelper
     */
    protected $_helper;

    /**
     * @var RateRequest
     */
    protected $_rateRequest;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var TarifaFactory
     */
    protected $_tarifaFactory;

    /**
     * Rate result data
     *
     * @var Result
     */
    protected $_result;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * AndoMoto constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param ResultFactory $rateFactory
     * @param MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param Webservice $webservice
     * @param AndoHelper $andoHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger, Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        Webservice $webservice,
        AndoHelper $andoHelper,
        array $data = []
    )
    {
        $this->_rateResultFactory = $rateFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_helper            = $andoHelper;
        $this->_webservice        = $webservice;
        $this->_addressRepository = $addressRepository;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCityRequired()
    {
        return true;
    }

    /**
     * @param null $countryId
     * @return bool
     */
    public function isZipCodeRequired($countryId = null)
    {
        if ($countryId != null) {
            return !$this->_directoryData->isZipCodeOptional($countryId);
        }
        return true;
    }

    /**
     * Is state province required
     *
     * @return bool
     */
    public function isStateProvinceRequired()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['andomoto' => $this->getConfigData('title')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active'))
        {
            return false;
        }

        $helper = $this->_helper;

        $result = $this->_rateResultFactory->create();
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('description'));

        $webservice = $this->_webservice;

        $dimensiones = [
            'alto'  => 0,
            'ancho' => 0,
            'largo' => 0
        ];

        foreach($request->getAllItems() as $_item)
        {
            if($_item->getProductType() == 'configurable')
                continue;

            $_producto = $_item->getProduct();

            if($_item->getParentItem())
                $_item = $_item->getParentItem();

            $dimensiones['alto'] += (int) $_producto->getResource()
                    ->getAttributeRawValue($_producto->getId(),'alto',$_producto->getStoreId()) * $_item->getQty();

            $dimensiones['largo'] += (int) $_producto->getResource()
                    ->getAttributeRawValue($_producto->getId(),'largo',$_producto->getStoreId()) * $_item->getQty();

            $dimensiones['ancho'] = (int) $_producto->getResource()
                    ->getAttributeRawValue($_producto->getId(),'ancho',$_producto->getStoreId()) * $_item->getQty();
        }

        $pesoTotal  = $request->getPackageWeight(); //Peso en unidad de kg

        if($pesoTotal > (int)$helper->getPesoMaximo(self::CARRIER_CODE))
        {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage(__('Su pedido supera el peso máximo permitido por Ando. Por favor divida su orden en más pedidos o consulte al administrador de la tienda.'));

            return $error;
        }

        if($request->getFreeShipping() === true)
        {
            $method->setPrice(0);
            $method->setCost(0);

            $result->append($method);
        }
        else
        {
            $shippingAddress = $helper->getQuote()->getShippingAddress();

            $altura = $shippingAddress->getAltura();
            $observaciones = $shippingAddress->getObservaciones();
            $nombre = $shippingAddress->getFirstname();
            $apellido = $shippingAddress->getLastname();
            $telefono = $shippingAddress->getTelephone();
            $ciudad = $shippingAddress->getCity();

            $address = json_decode(file_get_contents('php://input'), true);

            if(isset($address['address']) && isset($address['address']['custom_attributes']))
            {
                $altura =  $address['address']['custom_attributes']['altura'];
                $observaciones = $address['address']['custom_attributes']['observaciones'];
                $nombre = $address['address']['firstname'];
                $apellido = $address['address']['lastname'];
                //$email = $address['address']['email']; //$address->getEmail();
                $telefono = $address['address']['telephone'];
                $ciudad = $request->getDestCity();
            }
            else if(isset($address['addressId']))
            {
                $address = $this->_addressRepository->getById($address['addressId']);

                $altura = $address->getCustomAttribute('altura')->getValue();
                $observaciones = $address->getCustomAttribute('observaciones')->getValue();
                $nombre = $address->getFirstname();
                $apellido = $address->getLastname();
                $telefono = $address->getTelephone();
                $ciudad = $address->getCity();
            }

            if(!is_null($request->getDestRegionId()))
            {
                $provincia = $helper->getProvincia($request->getDestRegionId());
            }
            else
            {
                if(is_array($region = $shippingAddress->getRegion()))
                    $provincia = $region['region'];
                else
                    $provincia = $region;
            }

            $costoEnvio = $webservice->getShippingQuote(
            [
                'shipFrom_province'      => $this->_scopeConfig->getValue('shipping/ando_webservice/direccion/provincia'),
                'shipFrom_addressStreet' => $this->_scopeConfig->getValue('shipping/ando_webservice/direccion/calle'),
                'shipFrom_addressNumber' => $this->_scopeConfig->getValue('shipping/ando_webservice/direccion/numero'),
                'shipFrom_city'          => $this->_scopeConfig->getValue('shipping/ando_webservice/direccion/ciudad'),
                'shipFrom_country'       => 'Argentina',
                'startSpecialInstructions'=> $this->_scopeConfig->getValue('shipping/ando_webservice/direccion/observaciones'),
                'shipTo_firstName'       => $nombre,
                'shipTo_lastName'        => $apellido,
                'shipTo_email'           => $helper->getQuote()->getCustomerEmail(),
                'shipTo_phone'           => $telefono,
                'shipTo_addressStreet'   => $request->getDestStreet() .' '. $request->getDestStreet1(),
                'shipTo_addressNumber'   => $altura,
                'shipTo_city'            => $ciudad,
                'shipTo_province'        => $provincia,
                'shipTo_country'         => "Argentina",
                'endSpecialInstructions' => $observaciones,
                'packageWidth'           => $dimensiones['alto'],
                'packageLarge'           => $dimensiones['largo'],
                'packageHeight'          => $dimensiones['ancho'],
                'packageWeight'          => $pesoTotal,
                'shippingMethod'         => 'MOTO',
                'digitalSignature'       => false,
                'currency'               => 'ARS',
                'promocode'              => null
            ]);

            if($costoEnvio)
            {
                $method->setPrice($costoEnvio);
                $method->setCost($costoEnvio);

                $result->append($method);
            }
            else
            {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage(__('No existen cotizaciones para la dirección ingresada'));

                $result->append($error);
            }
        }

        return $result;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $result = new \Magento\Framework\DataObject();
        $xmlRequest = $this->_formShipmentRequest($request);
        $xmlResponse = $this->_getCachedQuotes($xmlRequest);

        if ($xmlResponse === null)
        {
            $url = $this->getShipConfirmUrl();

            $debugData = ['request' => $xmlRequest];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (bool)$this->getConfigFlag('mode_xml'));
            $xmlResponse = curl_exec($ch);
            if ($xmlResponse === false)
            {
                throw new \Exception(curl_error($ch));
            } else {
                $debugData['result'] = $xmlResponse;
                $this->_setCachedQuotes($xmlRequest, $xmlResponse);
            }
        }
    }

    /**
     * Processing additional validation to check if carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return $this;
    }
}