<?php

namespace Harsh\Accountdelete\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    protected $_messageManager;

    const ENABLE = 'accountdelete/general/enable_in_frontend';
    const EMAIL_SENDER = 'accountdelete/email/identity';
    const CONFIRMATION_EMAIL = 'accountdelete/email/confirmationtemplate';
    const NOTIFICATION_EMAIL = 'accountdelete/email/notificationtemplate';

    public function __construct(
    \Magento\Framework\App\Helper\Context $context, 
    \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, 
    \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, 
    \Magento\Store\Model\StoreManagerInterface $storeManager, 
    \Magento\Framework\Message\ManagerInterface $messageManager, 
    \Magento\Framework\Escaper $escaper
    ) {
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_escaper = $escaper;
        parent::__construct($context);
    }

    public function getEnable() {
        return $this->scopeConfig->getValue(self::ENABLE);
    }

    public function getConfirmationEmailTemplate() {
        return $this->scopeConfig->getValue(self::CONFIRMATION_EMAIL);
    }

    public function getNotificationEmailTemplate() {
        return $this->scopeConfig->getValue(self::NOTIFICATION_EMAIL);
    }

    public function getEmailSender() {
        if ($this->scopeConfig->getValue(self::EMAIL_SENDER)) {
            return $this->scopeConfig->getValue(self::EMAIL_SENDER);
        } else {
            return 'general';
        }
    }

    public function getSenderEmail() {
        return $email = $this->scopeConfig->getValue('trans_email/ident_' . $this->getEmailSender() . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSenderName() {
        return $name = $this->scopeConfig->getValue('trans_email/ident_' . $this->getEmailSender() . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultSenderEmail() {
        return $email = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultSenderName() {
        return $name = $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function sendConfirmationEmail($recepientName, $recepientEmail, $customerid) {

        try {

            $templateId = $this->getConfirmationEmailTemplate();

            $email = $this->getSenderEmail();

            $name = $this->getSenderName();

            $sender = [
                'name' => $this->_escaper->escapeHtml($name),
                'email' => $this->_escaper->escapeHtml($email),
            ];

            $enccustomerid = $customerid . '-qwerty';
            $emaildata['account_delete_url'] = $this->_urlBuilder->getUrl('accountdelete/index/delete/') . 'id/' . base64_encode($enccustomerid);

            $emaildata['customer_name'] = $this->_escaper->escapeHtml($recepientName);

            $this->inlineTranslation->suspend();

            $postObject = new \Magento\Framework\DataObject();

            $postObject->setData($emaildata);

            // Email to Customer 
            $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => $this->storeManager->getStore()->getId(),
                            ]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender)
                    ->addTo($this->_escaper->escapeHtml($recepientEmail), $this->_escaper->escapeHtml($recepientName))
                    ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_messageManager->addError($e->getMessage());
            return;
        }
    }

    public function sendNotificationEmail($recepientName, $recepientEmail, $customerid, $reason) {

        try {

            $templateId = $this->getNotificationEmailTemplate();

            $email = $this->getSenderEmail();

            $name = $this->getSenderName();

            $sender = [
                'name' => $this->getDefaultSenderName(),
                'email' => $this->getDefaultSenderEmail(),
            ];


            $emaildata['customer_name'] = $this->_escaper->escapeHtml($recepientName);

            if ($reason) {

                $emaildata['reason'] = $reason;
            }

            $emaildata['customer_email'] = $this->_escaper->escapeHtml($recepientEmail);

            $emaildata['customer_id'] = $this->_escaper->escapeHtml($customerid);

            $this->inlineTranslation->suspend();

            $postObject = new \Magento\Framework\DataObject();

            $postObject->setData($emaildata);

            // Email to Admin 
            $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => $this->storeManager->getStore()->getId(),
                            ]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender)
                    ->addTo($this->_escaper->escapeHtml($email), $this->_escaper->escapeHtml($name))
                    ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_messageManager->addError($e->getMessage());
            return;
        }
    }

}
