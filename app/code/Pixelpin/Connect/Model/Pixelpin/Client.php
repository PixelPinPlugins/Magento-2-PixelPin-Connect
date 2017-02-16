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

class Client extends \Magento\Framework\DataObject
{
    const REDIRECT_URI_ROUTE = 'connect/customer/index';   
    const REQUEST_TOKEN_URI_ROUTE = 'connect/pixelpin/request';

    const OAUTH2_TOKEN_URI = 'https://login.pixelpin.io/connect/token';
    const OAUTH2_AUTH_URI = 'https://login.pixelpin.io/connect/authorize';
    const OAUTH2_SERVICE_URI = 'https://login.pixelpin.io/connect/'; 

    //https://ws3.pixelpin.co.uk/index.php/api/token/
    //https://login.pixelpin.co.uk/OAuth2/Flogin.aspx
    //https://ws3.pixelpin.co.uk/index.php/api/

    const XML_PATH_ENABLED = 'pixelpinlogin/general/enabled';
    const XML_PATH_CLIENT_ID = 'pixelpinlogin/general/client_id';
    const XML_PATH_CLIENT_SECRET = 'pixelpinlogin/general/client_secret';

    protected $clientId = null;
    protected $clientSecret = null;
    protected $redirectUri = null;
    protected $state = '';
    protected $token = null;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        \Psr\Log\LoggerInterface $logger
    )
     {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_logger = $logger;

         if(($this->isEnabled = $this->_isEnabled())) {
             $this->clientId = $this->_getClientId();
             $this->_url = $url;
             $this->clientSecret = $this->_getClientSecret();
             $this->redirectUri = $this->_url->sessionUrlVar(
                 $this->_url->getUrl(self::REDIRECT_URI_ROUTE)
             ); 
         }
            if(!empty($params['state'])) {
                $this->state = $params['state'];
            }
    }

    /*
    Calls _isEnabled() to check the store config to see if PixelPin Connect is enabled
    */
    public function isEnabled()
    {
        return (bool) $this->isEnabled;
    }

    /*
    Calls _getClientId() to retrieve the Client ID
    */
    public function getClientId()
    {
        return $this->clientId;
    }

    /*
    Calls _getClientSecret() to retrieve the Client Secret
    */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /*
    Retrieves the redirectUri from PixelPin/Client.php constructor.
    */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /*
    Retrieves the state from PixelPin/Client.php constructor.
    */
    public function getState()
    {
        return $this->state;
    }

    /*
    Sets the state from getState().
    */
    public function setState($state)
    {
        $this->state = $state;
    }

    /*
    Sets the Access Token from getAccessToken().
    */
    public function setAccessToken($token)
    {
       $this->token = json_decode($token);
       $this->_logger->addNotice($token);
    }

    /*
    Retrieves the Access token from fetchAccessToken() if no token exists.
    */
    public function getAccessToken()
    {
        if(empty($this->token)) {
            $this->fetchAccessToken();
        }

        return json_encode($this->token);
    }

    /*
    Creates the Authorisation URL.
    */
    public function createAuthUrl()
    {
        $url =  self::OAUTH2_AUTH_URI.'?'.
            http_build_query(
                array(
                    'scope' => 'openid profile email phone address',
                    'response_type' => 'code',
                    'redirect_uri' => $this->redirectUri,
                    'client_id' => $this->clientId,
                    'state' => $this->state,
                     )
            );
        return $url;
    }

    /*
    Creates the http request.
    */
    public function api($endpoint, $method = 'GET', $params = array())
    {

        if(empty($this->token)) {
            $this->fetchAccessToken();
       }

        $url = self::OAUTH2_SERVICE_URI.$endpoint;

        $method = strtoupper($method);

        $httpHeader = array();
        $httpHeader['Authorization'] = 'Bearer '.$this->token->access_token;

        $params = array_merge(array(
            'access_token' => $this->token->access_token
        ), $params);

        $response = $this->_httpRequest($url, $method, $params, $httpHeader);

        return $response;
    }

    /*
    Retrives the Access Token from 'https://ws3.pixelpin.co.uk/index.php/api/token/'
    */
    public function fetchAccessToken()
    {
        if(empty($_REQUEST['code'])) {
            throw new \Exception(
                __('Unable to retrieve access code.')
            );
        }

	    $tempCode = $_REQUEST['code'];

        $response = $this->_httpRequest(
            self::OAUTH2_TOKEN_URI,
            'POST',
            array(
                'code' => $_REQUEST['code'],
                'redirect_uri' => $this->redirectUri,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code'
            )
        ); 

        $response->created = time();

        $this->token = $response;
    }

    /*
    Retrieves the http request from 'https://ws3.pixelpin.co.uk/index.php/api/'
    */
    public function _httpRequest($url, $method = 'GET', $params = array(), $httpHeader = array())
    {
        $client = new \Zend_Http_Client($url, array('timeout' => 60));

        switch ($method) {
            case 'GET':
                $client->setParameterGet($params);
                $client->setHeaders($httpHeader);
                break;
            case 'POST':
                $client->setParameterPost($params);
                $client->setHeaders($httpHeader);
                break;
            case 'DELETE':
                break;
            default:
                throw new \Exception(
                    __('Required HTTP method is not supported.')
                );
        }
        $this->_logger->debug(json_encode($params));
        $response = $client->request($method);
        $this->_logger->debug($response->getStatus().' - '. $response->getBody());
        $decoded_response = json_decode($response->getBody());

        if($response->isError()) {
            $this->_logger->addNotice($response);
            $status = $response->getStatus();
            if(($status == 400 || $status == 401 || $status == 429)) {
                if(isset($decoded_response->error->message)) {
                    $message = $decoded_response->error->message;
                } else {
                    $message = __('Unspecified OAuth error occurred.');
                }

                throw new \Inchoo\SocialConnect\Model\PixelPin\Exception($message);
            } else {
                $message = sprintf(
                    __('HTTP error %d occurred while issuing request.'),
                    $status
                );

                throw new \Magento\Framework\Webapi\Exception($message);
            }
        }

        return $decoded_response;
    }

    /*
    Checks if PixelPin Connect has been enabled in the store configuration configured in the admin panel.
    */
    public function _isEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_ENABLED);
    }

    /*
    Retrieves the Client ID from the store configuration configured in the admin panel.
    */
    public function _getClientId()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);
    }

    /*
    Retrieves the Client Secret from the store configuration configured in the admin panel.
    */
    public function _getClientSecret()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);
    }
    /*
    Retrieves the Store Config 
    */
    public function _getStoreConfig($xmlPath)
    {
        return $this->scopeConfig->getValue($xmlPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

}


