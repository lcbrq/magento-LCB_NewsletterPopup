<?php

/**
 * Newsletter popup helper for Magento store
 *
 * @category   LCB
 * @package    LCB_NewsletterPopup
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 * @author     Jigsaw Marcin Gierus <martin@lcbrq.com>
 */
class LCB_NewsletterPopup_Helper_Data extends Mage_Core_Helper_Abstract {
    
    /**
     * Check if particular rule exists
     */
    public function checkIfRuleExists() {
        if (Mage::getStoreConfig('general/popup_newsletter/enable_disable')) {
            $RoleName = Mage::getStoreConfig('general/popup_newsletter/discount_name');
            $couponCode = null;
            $rules = Mage::getModel('salesrule/rule')->getCollection()
                    ->addFieldToFilter('name', array('in' => $RoleName));

            if ($rules->getSize()) {
                $ruleId = $rules->getFirstItem()->getId();
                $couponCode = $this->generateCouponCode($ruleId);
            } else {
                $couponCode = $this->createShoppingCartRule($RoleName);
            }

            return $couponCode;
        }else{
            return null;
        }
    }

    /**
     * Create new rule
     */
    public function createShoppingCartRule($ruleName) {
        $actionType = Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION;
        $discount = Mage::getStoreConfig('general/popup_newsletter/discount_ammount');
        $groupIds = [0]; //0 is non-logged group

        $customerGroups = Mage::helper('customer')->getGroups();
        foreach ($customerGroups as $group) {
            array_push($groupIds, $group->getId());
        }
        
        $websitesIds = explode(',',Mage::getStoreConfig('general/popup_newsletter/discount_website'));

        $shoppingCartPriceRule = Mage::getModel('salesrule/rule');

        $shoppingCartPriceRule
                ->setName($ruleName)
                ->setDescription('Newsletter Promo')
                ->setIsActive(1)
                ->setWebsiteIds($websitesIds)
                ->setSimpleAction($actionType)
                ->setDiscountAmount($discount)
                ->setCouponType('2')
                ->setUseAutoGeneration('1')
                ->setCustomerGroupIds($groupIds)
                ->setStopRulesProcessing(0);

        $shoppingCartPriceRule->save();
        $id = $shoppingCartPriceRule->getId();

        //After the shipping cart rule is created also generate coupon code for it
        return $this->generateCouponCode($id);
    }

    /**
     * Generate coupon code for given rule
     */
    public function generateCouponCode($ruleId) {
        $rule = Mage::getModel('salesrule/rule')->load($ruleId);

        $generator = Mage::getModel('salesrule/coupon_massgenerator');
        $format = Mage::getStoreConfig('general/popup_newsletter/discount_code_format');
        
        if($format == 'alphanum'){
            $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC);
        }elseif($format == 'alpha'){
            $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHABETICAL);
        }elseif($format == 'num'){
            $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_NUMERIC);
        }
        
        $generator->setDash('');
        $generator->setLength(Mage::getStoreConfig('general/popup_newsletter/discount_code_lenght'));
        $generator->setPrefix(Mage::getStoreConfig('general/popup_newsletter/discount_code_prefix'));
        $generator->setSuffix(Mage::getStoreConfig('general/popup_newsletter/discount_code_sufix'));

        $rule->setCouponCodeGenerator($generator);
        $rule->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO);

        $coupon = $rule->acquireCoupon();
        $coupon->setUsageLimit(Mage::getStoreConfig('general/popup_newsletter/discount_code_usage'));
        $coupon->setTimesUsed(0);
        $coupon->setType(1);
        $coupon->save();
        $code = $coupon->getCode();

        return $code;
    }

}
