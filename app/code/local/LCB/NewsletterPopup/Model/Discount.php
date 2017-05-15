<?php

/**
 * @category   LCB
 * @package    LCB_NewsletterPopup
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 * @author     Jigsaw Marcin Gierus <martin@lcbrq.com>
 */
class LCB_NewsletterPopup_Model_Discount extends Mage_Core_Model_Abstract {
    
    public function toOptionArray(){
        return array(
            array(
                'value'=>'alphanum',
                'label'=>'Alfanumeryczny'
            ),
            array(
                'value'=>'alpha',
                'label'=>'Alfabetyczny'
            ),
            array(
                'value'=>'num',
                'label'=>'Numeryczny'
            )
        );
    }
}
