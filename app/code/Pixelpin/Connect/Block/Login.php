<?php
/**
* Inchoo
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Please do not edit or add to this file if you wish to upgrade
* Magento or this extension to newer versions in the future.
** Inchoo *give their best to conform to
* "non-obtrusive, best Magento practices" style of coding.
* However,* Inchoo *guarantee functional accuracy of
* specific extension behavior. Additionally we take no responsibility
* for any possible issue(s) resulting from extension usage.
* We reserve the full right not to provide any kind of support for our free extensions.
* Thank you for your understanding.
*
* @category Inchoo
* @package SocialConnect
* @author Marko Martinović <marko.martinovic@inchoo.net>
* @copyright Copyright (c) Inchoo (http://inchoo.net/)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/

namespace PixelPin\Connect\Block;

class Login extends \Magento\Framework\View\Element\Template
{
    public $clientPixelpin = null;

    public $numEnabled = 0;
    public $numDescShown = 0;
    public $numButtShown = 0;
    public $url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    //public $isCheckout = "0";

    /**
     * @var \Inchoo\SocialConnect\Model\Pixelpin\Client
     */
    public $socialConnectPixelpinClient;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    protected $_logger;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PixelPin\Connect\Model\Pixelpin\Client $socialConnectPixelpinClient,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession, 
        array $data = []
    ) {
        $this->socialConnectPixelpinClient = $socialConnectPixelpinClient;
        $this->registry = $registry;
        $this->_logger = $logger;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct() {
        parent::_construct();

        $this->clientPixelpin = $this->socialConnectPixelpinClient;

	   if ( $this->clientPixelpin === null )
        {
	
        }

        if(!$this->_pixelpinEnabled()) 
            return;
		
        if($this->_pixelpinEnabled()) {
            $this->numEnabled++;
        }

        $this->registry->register('pixelpin_connect_button_text2', __('Login Using PixelPin'));

        $this->setTemplate('login.phtml');

        $url2 = $this->_storeManager->getStore()->getCurrentUrl();

        $find = 'index';

        $pos = strpos($url2, $find);

        if ($pos === false){
            $url = $this->_storeManager->getStore()->getCurrentUrl();
            $this->customerSession->setMyValue($url);
            return $url;
        } else {
            
        }      
    }

    public function _getColSet()
    {
        return 'col-'.$this->numEnabled.'-set';
    }

    public function _getDescCol()
    {
        return 'col-'.++$this->numDescShown;
    }

    public function _getButtCol()
    {
        return 'col-'.++$this->numButtShown;
    }

    public function _pixelpinEnabled()
    {
        return $this->clientPixelpin->isEnabled();
    }
}
