<?php

/**
 * Class LCB_NewsletterPopup_Block_Index
 */
class LCB_NewsletterPopup_Block_Index extends Mage_Core_Block_Template {
    
    /**
     * Get form action url
     * @return string
     */
    public function getFormActionUrl(){
        return $this->getUrl('newsletter/subscriber/new/');
    }
    
}
