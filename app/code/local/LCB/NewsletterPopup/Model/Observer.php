<?php

/**
 * Class LCB_NewsletterPopup_Model_Observer
 */
class LCB_NewsletterPopup_Model_Observer {

    /**
     * @param \Varien_Event_Observer $observer
     * @return \Varien_Event_Observer
     */
    public function subscribedToNewsletter(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $subscriber = $event->getDataObject();
        $data = $subscriber->getData();
        $statusChange = $subscriber->getIsStatusChanged();

        if ($data['subscriber_status'] == 1 && $statusChange == true) {
            Mage::getModel('core/cookie')->set('newsletterPopupShown', 'true', 31536000);
        }

        return $observer;
    }

}
