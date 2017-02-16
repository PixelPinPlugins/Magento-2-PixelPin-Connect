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
    protected $client = null;
    protected $userInfo = null;

    /**
     * @var \PixelPin\Connect\Model\Pixelpin\Client
     */
    protected $socialConnectPixelpinClient;

    /**
     * @var \PixelPin\Connect\Model\Pixelpin\Userinfo
     */
    protected $socialConnectPixelpinUserinfo;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $_logger;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \PixelPin\Connect\Model\Pixelpin\Client $socialConnectPixelpinClient,
        \PixelPin\Connect\Model\Pixelpin\Userinfo $socialConnectPixelpinUserinfo,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->socialConnectPixelpinClient = $socialConnectPixelpinClient;
        $this->registry = $registry;
        $this->socialConnectPixelpinUserinfo = $socialConnectPixelpinUserinfo;
        $this->_logger = $logger;
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

        //$this->userInfo = $this->socialConnectPixelpinUserinfo;

        //$this->userInfo->getUserInfo();

        $this->setTemplate('pixelpin/connect/account.phtml');
    }

    public function _hasUserInfo()
    {
        return (bool) $this->userInfo;
    }

    public function _getPixelpinId()
    {
        return $this->userInfo->id;
    }

    public function _getStatus()
    {
		return $this->htmlEscape($this->userInfo->firstName);
    }

    public function _getEmail()
    {
        return $this->userInfo->email;
    }

    public function _getPicture()
    {
        return null;
    }

    public function _getName()
    {
        return $this->userInfo->firstName;
    }

}