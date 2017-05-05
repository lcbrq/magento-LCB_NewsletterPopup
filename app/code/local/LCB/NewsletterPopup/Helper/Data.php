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

    const RULE_NAME = 'newsletter_discount';

    /**
     * Check if particular rule exists
     */
    public function checkIfRuleExists() {
        $couponCode = null;
        $rules = Mage::getModel('salesrule/rule')->getCollection()
                ->addFieldToFilter('name', array('in' => self::RULE_NAME));

        if ($rules->getSize()) {
            $ruleId = $rules->getFirstItem()->getId();
            $couponCode = $this->generateCouponCode($ruleId);
        } else {
            $couponCode =  $this->createShoppingCartRule(self::RULE_NAME);
        }

        return $couponCode;
    }

    /**
     * Create new rule
     */
    public function createShoppingCartRule($ruleName) {
        $actionType = 'by_percent';
        $discount = 5;
        $groupIds = [0]; //0 is non-logged group
        
        $customerGroups = Mage::helper('customer')->getGroups();
        foreach ($customerGroups as $group){
            array_push($groupIds,$group->getId());
        }

        $shoppingCartPriceRule = Mage::getModel('salesrule/rule');

        $shoppingCartPriceRule
                ->setName($ruleName)
                ->setDescription('Newsletter Promo')
                ->setIsActive(1)
                ->setWebsiteIds(array(1))
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

        $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC);
        $generator->setDash('');
        $generator->setLength(6);
        $generator->setPrefix('');
        $generator->setSuffix('');

        $rule->setCouponCodeGenerator($generator);
        $rule->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO);

        $coupon = $rule->acquireCoupon();
        $coupon->setUsageLimit(1);
        $coupon->setTimesUsed(0);
        $coupon->setType(1);
        $coupon->save();
        $code = $coupon->getCode();

        return $code;
    }

}
