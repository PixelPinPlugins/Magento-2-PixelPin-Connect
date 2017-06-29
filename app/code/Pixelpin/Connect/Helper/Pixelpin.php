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

namespace PixelPin\Connect\Helper;

class Pixelpin extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\ImageFactory
     */
    protected $imageFactory;
	
	protected $addresss;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ImageFactory $imageFactory,
		\Magento\Customer\Model\AddressFactory $addresss
    ) {
		$this->addresss = $addresss;
        $this->imageFactory = $imageFactory;
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context
        );
    }


    public function disconnect(\Magento\Customer\Model\Customer $customer) 
    {
        $this->customerSession->unsPixelpinConnectPixelpinUserinfo();
        
        $pictureFilename = $this->getBaseDir(\Magento\Store\Model\Store::URL_TYPE_MEDIA)
                .DS
                .'pixelpin'
                .DS
                .'connect'
                .DS
                .'pixelpin'
                .DS                
                .$customer->getPixelpinConnectPPid();
        
        if(file_exists($pictureFilename)) {
            @unlink($pictureFilename);
        }        
        
        $customer->setPixelPinConnectPPid(null)
        ->setPixelPinConnectPPtoken(null)
        ->save();   
    }
    
    public function connectByPixelpinId(
            \Magento\Customer\Model\Customer $customer,
            $pixelpinId,
            $token)
    {

        $customer->setPixelPinConnectPPid($pixelpinId)
                ->setPixelPinConnectPPtoken($token)
                ->save();
        
        $this->customerSession->setCustomerAsLoggedIn($customer);
    }
    
    public function connectByCreatingAccount(
            $email,
            $given_name,
            $family_name,
			$gender,
			$birthdate,
			$phone_number,
			$address,
            $pixelpinId,
            $token)
    {
		$jsonAddress = $address;
		
		$decodedAddress = json_decode($jsonAddress);
		
		$_customer = array (
			'given_name' => $given_name,
			'family_name' => $family_name,
			'email' => $email,
			'birthdate' => $birthdate,
			'gender' => $gender,
			'street_address' => $decodedAddress->street_address,
			'locality' => $decodedAddress->locality,
			'postal_code' => $decodedAddress->postal_code,
			'country' => $decodedAddress->country,
			'region' => $decodedAddress->region,
			'phone_number' => $phone_number,
		);
		
        $customer = $this->customerCustomerFactory->create();

        $customer->setEmail($_customer['email'])
                ->setFirstname($_customer['given_name'])
                ->setLastname($_customer['family_name'])
                ->setPixelPinConnectPPid($pixelpinId)
                ->setPixelPinConnectPPtoken($token)
                ->save();

        $customer->setConfirmation(null);
        $customer->save();
		
		if(!empty($decodedAddress->street_address)) {
            $customAddress = $this->addresss->create();
        
            $customAddress->setCustomerId($customer->getId())
                    ->setFirstname($_customer['given_name'])
                    ->setLastname($_customer['family_name'])
                    ->setCountryId($_customer['country'])
                    ->setPostcode($_customer['postal_code'])
                    ->setCity($_customer['locality'])
                    ->setTelephone($_customer['phone_number'])
                    ->setStreet($_customer['street_address'])
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
                $customAddress->save();
        }
				

        $this->customerSession->setCustomerAsLoggedIn($customer);            
    }
    
    public function loginByCustomer(\Magento\Customer\Model\Customer $customer)
    {
        if($customer->getConfirmation()) {
            $customer->setConfirmation(null);
            $customer->save();
        }

        $this->customerSession->setCustomerAsLoggedIn($customer);        
    }
    
    public function getCustomersByPixelpinId($pixelpinId)
    {
        $customer = $this->customerCustomerFactory->create();

        $collection = $customer->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('pixelpin_connect_ppid', $pixelpinId)
            ->setPageSize(1);

        if($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                $this->storeManager->getWebsite()->getId()
            );
        }

        if($this->customerSession->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id',
                array('neq' => $this->customerSession->getCustomerId())
            );
        }

        return $collection;
    }
    
    public function getCustomersByEmail($email)
    {
        $customer = $this->customerCustomerFactory->create();

        $collection = $customer->getCollection()
                ->addFieldToFilter('email', $email)
                ->setPageSize(1);

        if($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                $this->storeManager->getWebsite()->getId()
            );
        }  
        
        if($this->customerSession->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id',
                array('neq' => $this->customerSession->getCustomerId())
            );
        }        
        
        return $collection;
    }   
}