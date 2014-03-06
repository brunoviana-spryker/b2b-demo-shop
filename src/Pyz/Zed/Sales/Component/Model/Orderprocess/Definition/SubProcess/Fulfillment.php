<?php

namespace Pyz\Zed\Sales\Component\Model\Orderprocess\Definition\Subprocess;

use Pyz\Zed\Sales\Component\ConstantsInterface\Orderprocess;

/**
 * @property \Generated\Zed\Sales\Component\SalesFactory $factory
 * @property \ProjectA_Zed_Sales_Component_Model_Orderprocess_StateMachine_Setup $setup
 */
class Fulfillment extends \ProjectA_Zed_Sales_Component_Model_Orderprocess_Definition_Abstract implements
    Orderprocess
{

    /**
     * @param string $processName
     */
    public function __construct($processName = 'Fulfillment Subprocess')
    {
        parent::__construct($processName);
    }

    protected function createDefinition()
    {
        $this->getNewSetup();
        $this->addTransitions();
        $this->addMetaInfo();
        $this->addCommands();
        $this->addFlags();
    }

    protected function addTransitions()
    {
        $this->setup->addTransitionManual(self::STATE_INIT_FULFILLMENT_PROCESS, self::STATE_FULFILLED, self::EVENT_START_FULFILLMENT_EXPORT);
    }

    protected function addCommands()
    {
        $fulfillmentExportCommand = $this->factory->createModelOrderprocessCommandFulfillmentFulfillmentExportCommand();
        $decrementStockCommand = $this->factory->createModelOrderprocessCommandDecrementStock();
        $this->setup->addCommand(self::STATE_INIT_FULFILLMENT_PROCESS, self::EVENT_START_FULFILLMENT_EXPORT, $fulfillmentExportCommand);
        $this->setup->addCommand(self::STATE_INIT_FULFILLMENT_PROCESS, self::EVENT_START_FULFILLMENT_EXPORT, $decrementStockCommand);
    }

    protected function addFlags()
    {
        $this->setup->addStateMetaInfo(self::STATE_INIT_FULFILLMENT_PROCESS, self::FLAG_RESERVED, true);
    }

    protected function addMetaInfo()
    {
        $states =[
            self::STATE_INIT_FULFILLMENT_PROCESS,
            self::STATE_FULFILLED
        ];

        foreach ($states as $state) {
            $this->setup->addStateMetaInfo($state, 'group', $this->getName());
        }
    }
}
