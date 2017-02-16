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
    protected $client = null;
    protected $oauth2 = null;
    protected $userInfo = null;

    /**
     * @var \Inchoo\SocialConnect\Model\Pixelpin\Client
     */
    protected $socialConnectPixelpinClient;

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
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_storeManager;

    protected $_logger;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PixelPin\Connect\Model\Pixelpin\Client $socialConnectPixelpinClient,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->socialConnectPixelpinClient = $socialConnectPixelpinClient;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->customerSession = $customerSession;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->_logger = $logger;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct() {
        parent::_construct();

        $this->client = $this->socialConnectPixelpinClient;
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
                $text = __('Connect');
            }
        } else {
            $text = __('Disconnect');
        }
        
        return $text;
    }

    public function _getCurrentUrl()
    {
        return $this->_storeManager->getStore()->getCurrentUrl();
    }

}
