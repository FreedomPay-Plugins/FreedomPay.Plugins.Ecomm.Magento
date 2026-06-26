<?php

namespace Citipay\HPP\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchInterface;

/**
 * Adds API end points in core_config_data table
 */
class InitializeApiEndPoints implements DataPatchInterface
{
    // XML paths for test and live endpoints for Citi Pay
    private const KEY_CP_TEST_API_END_POINT = 'payment/citipay_hpp/test_api_end_point';
    private const KEY_CP_LIVE_API_END_POINT = 'payment/citipay_hpp/live_api_end_point';
    private const KEY_SOAP_CP_TEST_API_END_POINT = 'payment/citipay_hpp/test_soap_api_end_point';
    private const KEY_SOAP_CP_LIVE_API_END_POINT = 'payment/citipay_hpp/live_soap_api_end_point';

    // Configuration values for test and live endpoints for Citi Pay
    private const VALUE_CP_TEST_API_END_POINT =
        'https://payments.uat.freedompay.com/checkoutservice/checkoutservice.svc';
    private const VALUE_CP_LIVE_API_END_POINT =
        'https://payments.freedompay.com/checkoutservice/checkoutservice.svc';
    private const VALUE_SOAP_CP_TEST_API_END_POINT =
        'https://cs.uat.freedompay.com/Freeway/Service.asmx';
    private const VALUE_SOAP_CP_LIVE_API_END_POINT =
        'https://cs.uat.freedompay.com/Freeway/Service.asmx';//to be changed to prod url

    // Scope value constant
    private const SCOPE_VALUE = 'default';

    // Scope ID value constant
    private const SCOPE_ID_VALUE = 0;

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Adds API end points in core_config_data table
     *
     * @return PatchInterface|ModuleDataSetupInterface
     */
    public function apply(): PatchInterface|ModuleDataSetupInterface
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $configTable = $this->moduleDataSetup->getTable('core_config_data');

        $data = [
            [
                'scope'     =>  self::SCOPE_VALUE,
                'scope_id'  =>  self::SCOPE_ID_VALUE,
                'path'      =>  self::KEY_CP_TEST_API_END_POINT,
                'value'     =>  self::VALUE_CP_TEST_API_END_POINT
            ],
            [
                'scope'     =>  self::SCOPE_VALUE,
                'scope_id'  =>  self::SCOPE_ID_VALUE,
                'path'      =>  self::KEY_CP_LIVE_API_END_POINT,
                'value'     =>  self::VALUE_CP_LIVE_API_END_POINT
            ],
            [
                'scope'     =>  self::SCOPE_VALUE,
                'scope_id'  =>  self::SCOPE_ID_VALUE,
                'path'      =>  self::KEY_SOAP_CP_TEST_API_END_POINT,
                'value'     =>  self::VALUE_SOAP_CP_TEST_API_END_POINT
            ],
            [
                'scope'    =>   self::SCOPE_VALUE,
                'scope_id' =>   self::SCOPE_ID_VALUE,
                'path'     =>   self::KEY_SOAP_CP_LIVE_API_END_POINT,
                'value'    =>   self::VALUE_SOAP_CP_LIVE_API_END_POINT
            ]
        ];
        $connection->insertOnDuplicate(
            $configTable,
            $data,
            ['value']
        );

        return $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
