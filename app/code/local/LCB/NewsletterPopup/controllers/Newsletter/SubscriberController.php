<?php
require_once "Mage/Newsletter/controllers/SubscriberController.php";  
class LCB_NewsletterPopup_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController{

    public function postDispatch()
    {
        parent::postDispatch();
        Mage::dispatchEvent('controller_action_postdispatch_adminhtml', array('controller_action' => $this));
    }

    public function newAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getPost('email');

            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('newsletter/subscriber')->loadByEmail($email)->getId();              
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('This email address is already assigned to another user.'));
                }
                
                /**
                 * Edit by Jigsaw Marcin Gierus <martin@lcbrq.com>
                 * Check if guest already subscribed to newsletter
                 */
                $guestId = Mage::getModel('newsletter/subscriber')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                
                if($guestId !== null && $guestId != $customerSession->getId()) {
                    Mage::throwException($this->__('This email address is already assigned to another user.'));
                }
                
                /**
                 * End edit
                 */
                
                
                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $session->addSuccess($this->__('Confirmation request has been sent.'));
                }
                else {
                    $session->addSuccess($this->__('Thank you for your subscription.'));
                }
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription.'));
            }
        }
        $this->_redirectReferer();
    }
}
				