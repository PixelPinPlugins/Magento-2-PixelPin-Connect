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

class Register extends \Magento\Framework\View\Element\Template
{
    protected $numEnabled = 0;
    protected $numShown = 0;

    public $url;

    /**
     * @var \Inchoo\SocialConnect\Model\Pixelpin\Client
     */
    protected $client = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PixelPin\Connect\Model\Pixelpin\Client $client,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession, 
        array $data = []
    ) {
        $this->client = $client;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct() {
        parent::_construct();
		$this->client = $this->client;

        if( !$this->_pixelpinEnabled())
            return;
		
        if($this->_pixelpinEnabled()) {
            $this->numEnabled++;
        }

        $this->registry->register('pixelpin_connect_button_text3', __('Register Using PixelPin'));

        $this->setTemplate('register.phtml');

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
    public function _getCol()
    {
        return 'col-'.++$this->numShown;
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
       return (bool) $this->client->isEnabled();
    }

}