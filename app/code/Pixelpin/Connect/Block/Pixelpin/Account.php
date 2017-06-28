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

class Account extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \PixelPin\Connect\Model\Pixelpin\Client
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

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PixelPin\Connect\Model\Pixelpin\Client $client,
        \PixelPin\Connect\Model\Pixelpin\Userinfo $userInfo,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->client = $client;
        $this->registry = $registry;
        $this->userInfo = $userInfo;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct() {
        parent::_construct();

        $this->client = $this->client;
        if(!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = $this->registry->registry('pixelpin_connect_pixelpin_userinfo');

        $this->setTemplate('pixelpin/connect/account.phtml');
		
		$url2 = $this->_storeManager->getStore()->getCurrentUrl();

        $find = 'index';

        $pos = strpos($url2, $find);

        if ($pos === true){
            $url = $this->_storeManager->getStore()->getCurrentUrl();
            $this->customerSession->setMyValue($url);
            return $url;
        }  
    }
	
	/**
	 * Checks if the user's info exists.
	 * 
	 * Used in the setTemplate. 
	 * 
	 * @return bool
	 */
    public function _hasUserInfo()
    {
        return (bool) $this->userInfo;
    }
	
	/**
	 * Gets the user's sub id.
	 * 
	 * Used in the setTemplate.
	 * 
	 * @return string $pixelpinId 
	 */
    public function _getPixelpinId()
    {
        return $this->userInfo->sub;
    }

	/**
	 * Gets the user's email.
	 * 
	 * Used in the setTemplate
	 * 
	 * @return string $email
	 */
    public function _getEmail()
    {
        return $this->userInfo->email;
    }

	/**
	 * Gets the user's first name.
	 * 
	 * Used in the setTemplate
	 * 
	 * @return string $firstName
	 */
    public function _getName()
    {
        return $this->userInfo->given_name;
    }

}