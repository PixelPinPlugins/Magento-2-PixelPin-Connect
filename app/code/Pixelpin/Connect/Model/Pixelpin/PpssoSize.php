<?php
namespace PixelPin\Connect\Model\Pixelpin;

class PpssoSize
{
    public function toOptionArray()
    {
        return [
            '0' => 'Large',
            '1' => 'Medium',
            '2' => 'Small'
        ];
    }
}