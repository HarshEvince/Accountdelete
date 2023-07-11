<?php

namespace Harsh\Accountdelete\Controller\Index;

use Magento\Framework\Controller\ResultFactory; 

class Delete extends \Magento\Framework\App\Action\Action
{

    protected $request;

    protected $customermodel;

    protected $_registry;

    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Customer $customermodel,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        $this->customermodel = $customermodel;
        $this->customerSession = $customerSession;
        $this->_registry = $registry;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {   if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addError(__("Please sign-in your account and click on the link from email."));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('customer/account/login');
        }
        $customer_id = explode('-', base64_decode($this->request->getParam('id')));
        if($customer_id[0] && $customer_id[0] == $this->customerSession->getCustomer()->getId()){
            $customer = $this->customermodel->load($customer_id[0]);
            $this->_registry->register('isSecureArea', true);
            if($customer->delete()){
                $this->messageManager->addSuccess(__("Your account deleted successfully."));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('customer/account/login');
            }
        } else {
                $this->messageManager->addError(__("No any customer found."));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('customer/account/login');
        }

    }
}
