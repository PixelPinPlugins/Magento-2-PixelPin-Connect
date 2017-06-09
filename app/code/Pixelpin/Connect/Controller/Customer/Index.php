<?php
namespace PixelPin\Connect\Controller\Customer;

use Magento\Framework\Controller\ResultFactory; 

class Index extends \Magento\Framework\App\Action\Action {
	protected $referer = null;

    const REDIRECT_URI_ROUTE = '/checkout';
    const REDIRECT_URI_ROUTE2 = '';

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    protected $redirectUri = null;

    /**
     * @var \Inchoo\SocialConnect\Helper\Data
     */
    protected $socialConnectHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Inchoo\SocialConnect\Helper\Pixelpin
     */
    protected $socialConnectPixelpinHelper;

    /**
     * @var \Inchoo\SocialConnect\Model\Pixelpin\Client
     */
    protected $socialConnectPixelpinClient;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \PixelPin\Connect\Model\Pixelpin\Userinfo
     */
    protected $socialConnectPixelpinUserinfo;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $managerInterface;

    public $resultRedirect;


    //public $isCheckout = 'No';

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        \Magento\Framework\Session\Generic $generic,
        \PixelPin\Connect\Helper\Data $socialConnectHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        \PixelPin\Connect\Helper\Pixelpin $socialConnectPixelpinHelper,
        \PixelPin\Connect\Model\Pixelpin\Client $socialConnectPixelpinClient,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \PixelPin\Connect\Model\Pixelpin\Userinfo $socialConnectPixelpinUserinfo,
        \PixelPin\Connect\Model\Pixelpin\Redirect $redirect
    ) {
        $this->generic = $generic;
        $this->managerInterface = $managerInterface;
        $this->socialConnectHelper = $socialConnectHelper;
        $this->customerSession = $customerSession;
        $this->socialConnectPixelpinHelper = $socialConnectPixelpinHelper;
        $this->socialConnectPixelpinClient = $socialConnectPixelpinClient;
        $this->registry = $registry;
        $this->socialConnectPixelpinUserinfo = $socialConnectPixelpinUserinfo;
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->redirect = $redirect;
        $this->_url = $url;
        $this->redirectUri = $this->_url->sessionUrlVar(
                 $this->_url->getUrl(self::REDIRECT_URI_ROUTE)
             ); 
        $this->redirectUri2 = $this->_url->sessionUrlVar(
                 $this->_url->getUrl(self::REDIRECT_URI_ROUTE2)
             ); 
        parent::__construct(
            $context
        );
    }

    public function connectAction()
    {
        try {
            $this->_connectCallback();
        } catch (Exception $e) {
            $this->managerInterface->addError($e->getMessage());
        }

        if(!empty($this->referer)) {
            $client = $this->socialConnectPixelpinClient;

            $client->getRedirectUri($this->referer);           
        } else {
            return $this->redirectUri;         
        }
    }

    public function disconnectAction()
    {
        $customer = $this->customerSession->getCustomer();

        try {
            $this->_disconnectCallback($customer);
        } catch (Exception $e) {
            $this->managerInterface->addError($e->getMessage());
        }

        if(!empty($this->referer)) {
            $this->_redirectUrl($this->referer);
        } else {
            $this->socialConnectHelper->redirect404($this);
        }
    }

    protected function _disconnectCallback(\Magento\Customer\Model\Customer $customer) 
    {
        $this->referer = $this->getUrl('connect/customer/index');        
        
        $this->socialConnectPixelpinHelper->disconnect($customer);
        
        $this->managerInterface->addSuccess(
                __('You have successfully disconnected your Pixelpin account from our store account.')
            );
    }

    protected function _connectCallback() {

        $this->socialConnectPixelpinClient->_isEnabled();

        $this->socialConnectPixelpinClient->_getClientId();

        $this->socialConnectPixelpinClient->_getClientSecret();

        $errorCode = $this->getRequest()->getParam('error');
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        if(!($errorCode || $code) && !$state) {
            return;
        }
        
        $this->referer = $this->generic->getPixelpinRedirect();

        if(!$state) {
            return;
        }

        if($state != $this->generic->getPixelpinCsrf()) {
            return;
        }

        if($errorCode) {
            // Pixelpin API red light - abort
            if($errorCode === 'access_denied') {
                $this->managerInterface
                    ->addNotice(
                        __('Pixelpin Connect process aborted.')
                    );
                return;
            }

            throw new \Exception(
                sprintf(
                    __('Sorry, "%s" error occured. Please try again.'),
                    $errorCode
                )
            );


            return;
        }

        if ($code) {
            // Pixelpin API green light - proceed
            $client = $this->socialConnectPixelpinClient;

            $userInfo = $client->api('userinfo');

            $token = $client->getAccessToken();


            $customersByPixelpinId = $this->socialConnectPixelpinHelper
                ->getCustomersByPixelpinId($userInfo->sub);


            if($this->customerSession->isLoggedIn()) {
                // Logged in user
                if($customersByPixelpinId->count()) {
                    // Pixelpin account already connected to other account - deny
                    $this->managerInterface
                        ->addNotice(
                            __('Your Pixelpin account is already connected to one of our store accounts.')
                        );


                    return;
                }

                // Connect from account dashboard - attach
                $customer = $this->customerSession->getCustomer();

                $this->socialConnectPixelpinHelper->connectByPixelpinId(
                    $customer,
                    $userInfo->sub,
                    $token
                );

                $this->managerInterface->addSuccess(
                    __('Your Pixelpin account is now connected to your store account. You can now login using our Pixelpin Connect button or using store account credentials you will receive to your email address.')
                );


                return;
            }

            if($customersByPixelpinId->count()) {
                // Existing connected user - login
                $customer = $customersByPixelpinId->getFirstItem();

                $this->socialConnectPixelpinHelper->loginByCustomer($customer);

                $this->managerInterface
                    ->addSuccess(
                        __('You have successfully logged in using your Pixelpin account.')
                    );


                return;
            }

            try
            {
                $customersByEmail = $this->socialConnectPixelpinHelper
                    ->getCustomersByEmail($userInfo->email);

                if($customersByEmail->count())  {
                    // Email account already exists - attach, login
                    $customer = $customersByEmail->getFirstItem();
                    
                    $this->socialConnectPixelpinHelper->connectByPixelpinId(
                        $customer,
                        $userInfo->sub,
                        $token
                    );

                    $this->managerInterface->addSuccess(
                        __('We have discovered you already have an account at our store. Your Pixelpin account is now connected to your store account.')
                    );
                    return;
                }
            }  
            catch (\Exception $e)
            {
                $resultRedirect = $this->resultRedirectFactory->create();
                $this->managerInterface->addError(
                        __('Email required.')
                    );
                return $resultRedirect->setRefererOrBaseUrl();
            }
			
			if(empty($userInfo->email)) {
				$this->managerInterface->addError(
						__('Sorry, we require your email to register you. We could not retrieve your email from PixelPin. Please try again.')
					);
			}
			
			if(empty($userInfo->given_name)) {
				$this->managerInterface->addError(
						__('Sorry, we require your first name to register you. We could not retrieve your first name from PixelPin. Please try again.')
					);
			}
			
			if(empty($userInfo->family_name)) {
				$this->managerInterface->addError(
						__('Sorry, we require your last name to register you. We could not retrieve your last name from PixelPin. Please try again.')
					);
			}
			
			if(empty($userInfo->gender)) {
                $userInfo->gender = '';
            }

            if(empty($userInfo->birthdate)) {
                $userInfo->birthdate = '';
            }

            if(empty($userInfo->phone_number)) {
                $userInfo->phone_number = '';
            }

            if(empty($userInfo->address)) {
                $address = array(
                        "street_address" => "",
                        "locality" => "",
                        "postal_code" => "",
                        "country" => "",
                        "region" => "",
                    );

                $jsonAddress = json_encode($address);

                $userInfo->address = $jsonAddress;

                $this->managerInterface->addNotice(
						__('We\'ve noticed that you have no address set. We recommend adding a new address into your address book before proceeding.')
					);
            }

            // New connection - create, attach, login

            $this->socialConnectPixelpinHelper->connectByCreatingAccount(
                $userInfo->email,
                $userInfo->given_name,
                $userInfo->family_name,
				$userInfo->gender,
				$userInfo->birthdate,
				$userInfo->phone_number,
				$userInfo->address,
                $userInfo->sub,
                $token
            );

            $this->managerInterface->addSuccess(
                __('Your Pixelpin account is now connected to your new user account at our store. Now you can login using our Pixelpin Connect button.')
            );
        }
    }



    public function execute() {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try{
            $this->connectAction();
        } catch (Exception $e) {
            $this->managerInterface->addError($e->getMessage());
        }

        $whatPage = $this->customerSession->getMyValue();
        $find = 'checkout';
        $pos = strpos($whatPage, $find);

        if ($pos === false){
            $path = '/';
            $resultRedirect->setPath($path);
        } else {
            $path = 'checkout';
            $resultRedirect->setPath($path);
        }
        return $resultRedirect;
    }
}
?>