<?php
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field as FormField;

namespace PixelPin\Connect\Model\Pixelpin;

class Redirect extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
	protected $_url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        \Magento\Framework\UrlInterface $url
    )
    {
    	$this->_url = $url;
        $this->storeManager = $storeManager;
    	parent::__construct($context);
    }

    /*
    Creates the valid Oauth URI for the developer in the admin configuration to see.
    */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $homeUrl = $this->storeManager->getStore()->getBaseUrl();
        $html_id = $element->getHtmlId();
        $redirectUrl = 'connect/customer/index';
        $html = '<input style="opacity:1;" readonly id="' . $html_id . '" class="input-text admin__control-text" value="'. $homeUrl . $redirectUrl . '" onclick="this.select()" type="text">';

        return $html;
    }
}