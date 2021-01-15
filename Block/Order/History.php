<?php

namespace AHT\FilterOrder\Block\Order;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as OrderStatusCollection;

class History extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'AHT_FilterOrder::order/history.phtml';

    /**
     * @var CollectionFactory
     */
    protected $orderStatusCollection;

    protected $_orderCollectionFactory;

    /**
     * History constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param OrderStatusCollection $orderStatusCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        OrderStatusCollection $orderStatusCollection,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->orderStatusCollection=$orderStatusCollection;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
    }

    protected function getOrderCollectionFactory()
    {
        if ($this->_orderCollectionFactory === null) {
            $this->_orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->_orderCollectionFactory;
    }

    public function getOrders()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            if ($this->getNameRequest()==null) {
                if (!$this->orders) {
                    $this->orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                        '*'
                    )->addFieldToFilter(
                        'status',
                        ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
                    )->setOrder(
                        'created_at',
                        'desc'
                    );
                }
            } else {
                if (!$this->orders) {
                    $this->orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
                        '*'
                    )->addFieldToFilter(
                        'status',
                        ['in' => $this->getNameRequest()]
                    )->setOrder(
                        'created_at',
                        'desc'
                    );
                }
            }
        }
        return $this->orders;
    }

    public function getStatus()
    {
        $items = $this->_orderConfig->getVisibleOnFrontStatuses();
        $collection = $this->orderStatusCollection->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('status', $items);
        return $collection;
    }

    public function getNameRequest()
    {
        $colelction = $this->getRequest()->getParam('status');
        return $colelction;
    }
}
