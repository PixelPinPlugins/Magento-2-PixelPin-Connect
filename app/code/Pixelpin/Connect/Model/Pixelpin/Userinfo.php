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

namespace PixelPin\Connect\Model\Pixelpin;

class Userinfo
{
    protected $client = null;
    protected $userInfo = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Inchoo\SocialConnect\Model\Pixelpin\Client
     */
    protected $socialConnectPixelpinClient;

    /**
     * @var \Inchoo\SocialConnect\Helper\Pixelpin
     */
    protected $socialConnectPixelpinHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;


    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \PixelPin\Connect\Model\Pixelpin\Client $socialConnectPixelpinClient,
        \PixelPin\Connect\Helper\Pixelpin $socialConnectPixelpinHelper,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->customerSession = $customerSession;
        $this->socialConnectPixelpinClient = $socialConnectPixelpinClient;
        $this->socialConnectPixelpinHelper = $socialConnectPixelpinHelper;
        $this->generic = $generic;
        if(!$this->customerSession->isLoggedIn())
            return;

        $this->client = $this->socialConnectPixelpinClient;
        if(!($this->client->isEnabled())) {
            return;
        }

        $customer = $this->customerSession->getCustomer();
        if(($socialconnectTid = $customer->getInchooSocialconnectPPid()) &&
                ($socialconnectTtoken = $customer->getInchooSocialconnectPPtoken())) {
					
            $helper = $this->socialConnectPixelpinHelper;

            try{
                $this->client->setAccessToken($socialconnectTtoken);
                $this->userInfo = $this->client->api('userinfo'); 

            }  catch (Inchoo_SocialConnect_PixelpinOAuthException $e) {
                $helper->disconnect($customer);
                $this->generic->addNotice($e->getMessage());                
            } catch(Exception $e) {
                $helper->disconnect($customer);
                $this->generic->addError($e->getMessage());
            }

        }
    }

    /*
    Gets User Info from PixelPin/Client api(): 'https://login.pixelpin.io/connect/userinfo'
    */
    public function getUserInfo()
    {
        return $this->userInfo;
    }
}