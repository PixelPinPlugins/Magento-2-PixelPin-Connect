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

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Cms\Helper\Page
     */
    protected $cmsPageHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Helper\Page $cmsPageHelper
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->cmsPageHelper = $cmsPageHelper;
        parent::__construct(
            $context
        );
    }
	
	/**
	 * When Called, redirects user to a 404 page.
	 * 
	 * @param type $frontController
	 */
    public function redirect404($frontController)
    {
        $frontController->getResponse()
            ->setHeader('Status','404 File not found');

        $pageId = $this->scopeConfig->getValue('web/default/cms_no_route', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$this->cmsPageHelper->prepareResultPage($frontController, $pageId)) {
            $frontController->_forward('defaultNoRoute');
}
    }
}
