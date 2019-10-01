<?php

/**
 * Class WalletRbkPS_Model_PaymentMethodsRbk
 *
 */
class WalletRbkPS_Model_PaymentMethodsRbk extends Core_Model_Default
{
    /**
     * WalletYandexPS_Model_PaymentMethodsRbk constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'WalletRbkPS_Model_Db_Table_PaymentMethodsRbk';
        return $this;
    }
}