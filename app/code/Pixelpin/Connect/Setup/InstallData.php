<?php
/* app/code/Atwix/TestAttribute/Setup/InstallData.php */
 
namespace PixelPin\Connect\Setup;
 
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    private $eavConfig;
 
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }
 
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
 
        /**
         * Add attributes to the eav/attribute
         */
 
        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'pixelpin_connect_ppid',
            [
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false,
            'sort_order' => 75
            ],
            'pixelpin_connect_pptoken',
            [
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false,
            'sort_order' => 75
            ]
        );
        $sampleAttribute = $this->eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'pixelpin_connect_ppid');
        $sampleAttribute->setData(
            'used_in_forms',
            ['customer_account']
        );
        $sampleAttribute->save();
    }
}