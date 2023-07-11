<?php

namespace Harsh\Accountdelete\Controller\Index;

 use Magento\Framework\Controller\ResultFactory; 

class Confirmationmail extends \Magento\Framework\App\Action\Action
{

    protected $customerSession;

    protected $accounthelper;


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Harsh\Accountdelete\Helper\Data $accounthelper
    ) {
        $this->customerSession = $customerSession;
        $this->accounthelper = $accounthelper;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }

        $data = $this->getRequest()->getPostValue();

        if(isset($data['reason'])){
            $reason = $data['reason'];
        } else {
            $reason = '';
        }

        try {
            $customername = $this->customerSession->getCustomer()->getName();
            $customeremail = $this->customerSession->getCustomer()->getEmail();
            $customerid = $this->customerSession->getCustomer()->getId();
            if($this->accounthelper->sendConfirmationEmail($customername, $customeremail, $customerid)){
                $this->accounthelper->sendNotificationEmail($customername, $customeremail, $customerid, $reason);
                $this->messageManager->addSuccess(__("To complete account delete process, please click on the link we've just sent you on your e-mail."));
            } else {
                $this->messageManager->addError(__("Something went wrong while requesting for account delete."));
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('accountdelete/index/index');
        } 
            catch (\Magento\Framework\Exception\RuntimeException $e){
            $this->messageManager->addError($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('accountdelete/index/index');
        }
            catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addException($e, __('Something went wrong while requesting for account delete.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('accountdelete/index/index');
        }
    }
}
