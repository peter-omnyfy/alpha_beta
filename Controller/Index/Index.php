<?php
/**
 * @copyright Copyright (c) 2021 Alpha (https://alipha.beta/)
 * @package Alpha_Beta
 */

namespace Alpha\Beta\Controller\Index;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        RequestInterface  $request,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $lastCustomerId = $this->request->getParam('lastCustomerId');
        if ($lastCustomerId == "") {
            /** @var \Magento\Framework\Controller\Result\Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $result->setPath('/');
        }

        $customers = $this->getNextTenCustomers($lastCustomerId);

        if (empty($customers)) {
            /** @var \Magento\Framework\Controller\Result\Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $result->setPath('*/*');
        }

        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
    }

    /**
     * @param $lastCustomerId
     * @return array||bool
     */
    public function getNextTenCustomers($lastCustomerId)
    {
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customers */
        $customers = $this->collectionFactory->create();
        $customers->getSelectSql()->where('entity_id > $lastCustomerId');

        $result = [];

        foreach ($customers as $customer) {
            $result[$customer->getId()] = $customer;
            if(count($result) == 10)
                break;
        }

        if(empty($result))
            return false;

        return $result;
    }
}
