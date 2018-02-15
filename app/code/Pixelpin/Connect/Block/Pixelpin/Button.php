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

namespace PixelPin\Connect\Block\Pixelpin;

class Button extends \Magento\Framework\View\Element\Template
{

    const XML_PATH_PPSSO_ENABLED = 'pixelpinlogin/general/ppsso_customise';
    const XML_PATH_PPSSO_SIZE = 'pixelpinlogin/general/ppsso_size';
    const XML_PATH_PPSSO_COLOUR = 'pixelpinlogin/general/ppsso_colour';
    const XML_PATH_PPSSO_SHOW_TEXT = 'pixelpinlogin/general/ppsso_show_text';
    const XML_PATH_PPSSO_SHOW_LOGIN_TEXT = 'pixelpinlogin/general/login_button_text';
    const XML_PATH_PPSSO_SHOW_REGISTER_TEXT = 'pixelpinlogin/general/register_button_text';

	/**
     * @var \Inchoo\SocialConnect\Model\Pixelpin\Client
     */
    protected $client = null;
	
	/**
     * @var \PixelPin\Connect\Model\Pixelpin\Userinfo
     */
    protected $userInfo = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PixelPin\Connect\Model\Pixelpin\Client $client,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->client = $client;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->customerSession = $customerSession;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct() {
        parent::_construct();
        if(!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = $this->registry->registry('pixelpin_connect_pixelpin_userinfo');

        // CSRF protection
        $this->generic->setPixelpinCsrf($csrf = md5(uniqid(rand(), TRUE)));
        $this->client->setState($csrf);

        if(!($redirect = $this->customerSession->getBeforeAuthUrl())) {
            $redirect = $this->_storeManager->getStore()->getCurrentUrl();     
        }

        // Redirect uri
        $this->generic->setPixelpinRedirect($redirect);

        $this->setTemplate('Pixelpin_Connect::button.phtml');
    }

    public function _getButtonUrl()
    {
        if(empty($this->userInfo)) {
            return $this->client->createAuthUrl();
        } else {
            return $this->getUrl('connect/pixelpin/disconnect');
        }
    }

    public function _getButtonText()
    {
        if(empty($this->userInfo)) {
            if(!($text = $this->registry->registry('pixelpin_connect_button_text'))){
                $text = __('Connect to PixelPin');
            }
        } else {
            $text = __('Disconnect from PixelPin');
        }
        
        return $text;
    }

    public function _getCurrentUrl()
    {
        return $this->_storeManager->getStore()->getCurrentUrl();
    }

    /*
    Retrieves the Store Config 
    */
    public function _getStoreConfig($xmlPath)
    {
        return $this->_scopeConfig->getValue($xmlPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
    }

    public function _getButton()
    {
		$ppssoEnabled = $this->_getStoreConfig(self::XML_PATH_PPSSO_ENABLED);
		$ppssoSize = $this->_getStoreConfig(self::XML_PATH_PPSSO_SIZE);
		$ppssoColour = $this->_getStoreConfig(self::XML_PATH_PPSSO_COLOUR);
		$ppssoTextEnabled = $this->_getStoreConfig(self::XML_PATH_PPSSO_SHOW_TEXT);
		$ppssoLoginText = $this->_getStoreConfig(self::XML_PATH_PPSSO_SHOW_LOGIN_TEXT);
		$ppssoRegisterText = $this->_getStoreConfig(self::XML_PATH_PPSSO_SHOW_REGISTER_TEXT);

        //href
        if(empty($this->userInfo)) {
            $url =  $this->client->createAuthUrl();
        } else {
            $url =  $this->getUrl('connect/pixelpin/disconnect');
        }

        //class
        if(empty($ppssoEnabled)) {
            $class = 'ppsso-btn';
        } else {
			switch ($ppssoColour){
				case 0:
					$colour = '';
					break;
				case 1:
					$colour = 'ppsso-cyan';
					break;
				case 2:
					$colour = 'ppsso-pink';
					break;
				case 3:
					$colour = 'ppsso-white';
					break;
			}

			switch ($ppssoSize){
				case 0:
					$size = 'ppsso-logo-lg';
					break;
				case 1:
					$size = 'ppsso-md ppsso-logo-md';
					break;
				case 2:
					$size = 'ppsso-sm ppsso-logo-sm';
					break;
			}

            $class = 'ppsso-btn ' . $size . ' ' . $colour;
        }

        if(empty($this->userInfo)) {
            if(!($text = $this->registry->registry('pixelpin_connect_button_text'))){
                $text = __('Connect to PixelPin');
            }
        } else {
            $text = __('Disconnect from PixelPin');
        }


        $checkOutButton = '<a class="'. $class .'" href="' . $url .'">Check Out Using <span class="ppsso-logotype">PixelPin</span></a>';
        $connectButton = '<a class="'. $class .'" href="' . $url .'">' . $text . ' <span class="ppsso-logotype">PixelPin</span></a>';

        if(empty($ppssoEnabled)) {
            $loginButton = '<a class="'. $class .'" href="' . $url .'">Login With <span class="ppsso-logotype">PixelPin</span></a>';
            $registerButton = '<a class="'. $class .'" href="' . $url .'">Register Using <span class="ppsso-logotype">PixelPin</span></a>';
        } else {
            if(empty($ppssoTextEnabled)) {
                $loginButton = '<a class="'. $class .'" href="' . $url .'"></a>';
                $registerButton = '<a class="'. $class .'" href="' . $url .'"></a>';
            } else {
                $loginButton = '<a class="'. $class .'" href="' . $url .'">' . $ppssoLoginText . ' <span class="ppsso-logotype">PixelPin</span></a>';
                $registerButton = '<a class="'. $class .'" href="' . $url .'">' . $ppssoRegisterText . ' <span class="ppsso-logotype">PixelPin</span></a>';
            }
		}
		
		$button = array(
			'loginButton' => $loginButton,
            'registerButton' => $registerButton,
            'checkOutButton' => $checkOutButton,
            'connectButton' => $connectButton
		);
        
        return $button;
    }

}
