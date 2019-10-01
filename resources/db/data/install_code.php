<?php

$datas = [
    [
        'title' => 'RBKMoney',
        'model' => 'WalletRbkPS_Model_PaymentMethodsRbk',
        'type' => 'url',
        'state_name' => 't',
        'url' => 'walletrbkps/mobile_walletrbk/find',
        'code' => 'WalletRbkPS',
    ]
];

foreach ($datas as $data) {
    $method = new Wallet_Model_PaymentSystems();
    $method
        ->setData($data)
        ->insertOnce(['code']);
}
