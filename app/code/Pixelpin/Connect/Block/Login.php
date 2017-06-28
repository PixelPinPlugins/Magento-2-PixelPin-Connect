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
    public $numEnabled = 0;
    public $numDescShown = 0;
    public $numButtShown = 0;
    public $url;
	
	/**
     * @var \PixelPin\Connect\Model\Pixelpin\Userinfo
     */
	protected $userInfo = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Inchoo\SocialConnect\Model\Pixelpin\Client
     */
    protected $client = null;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PixelPin\Connect\Model\Pixelpin\Client $client,
		\PixelPin\Connect\Model\Pixelpin\Userinfo $userInfo,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession, 
        array $data = []
    ) {
        $this->client = $client;
		$this->userInfo = $userInfo;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct() {
        parent::_construct();

        $this->client = $this->client;

        if(!$this->_pixelpinEnabled()) 
            return;
		
        if($this->_pixelpinEnabled()) {
            $this->numEnabled++;
        }

        $this->userInfo = $this->registry->registry('pixelpin_connect_pixelpin_userinfo');

        $this->registry->register('pixelpin_connect_button_text2', __('Login Using PixelPin'));

        $this->setTemplate('login.phtml');

        $url2 = $this->_storeManager->getStore()->getCurrentUrl();

        $find = 'index';

        $pos = strpos($url2, $find);

        if ($pos === false){
            $url = $this->_storeManager->getStore()->getCurrentUrl();
            $this->customerSession->setMyValue($url);
            return $url;
        }  
    }
	
	/**
	 * Sets the col-set number
	 * 
	 * Used in the setTemplate. 
	 * 
	 * @return string
	 */
    public function _getColSet()
    {
        return 'col-'.$this->numEnabled.'-set';
    }
	
	/**
	 * Sets the col number
	 * 
	 * Used in the setTemplate. 
	 * 
	 * @return string
	 */
    public function _getDescCol()
    {
        return 'col-'.++$this->numDescShown;
    }
	
	/**
	 * Sets the col number
	 * 
	 * Used in the setTemplate. 
	 * 
	 * @return string
	 */
    public function _getButtCol()
    {
        return 'col-'.++$this->numButtShown;
    }
	
	/**
	 * Checks if the client is enabled
	 * 
	 * Used in the setTemplate
	 * 
	 * @return bool
	 */
    public function _pixelpinEnabled()
    {
        return $this->client->isEnabled();
    }
	
	/**
	 * Gets the href for the pixelpin sso button.
	 * 
	 * Used in the setTemplate. 
	 * 
	 * @return string.
	 */
	public function _getButtonUrl()
    {
        if(empty($this->userInfo)) {
            return $this->client->createAuthUrl();
        } else {
            return $this->getUrl('connect/pixelpin/disconnect');
        }
    }
}
