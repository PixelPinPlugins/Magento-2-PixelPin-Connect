<?php
namespace PixelPin\Connect\Model\Pixelpin;

class PpssoColour
{
	/**
	 * Generates the redirect uri for the admin to view when enabling PixelPin OpenID Connect
	 * 
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
    public function toOptionArray()
    {
        return [
            '0' => 'Purple',
            '1' => 'Cyan',
            '2' => 'Pink',
            '3' => 'White'
        ];
    }
}