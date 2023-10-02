<?php

namespace PowerTranz\Support;

class Constants
{
    const DRIVER_NAME = "PowerTranz - Payment Gateway";
    const PLATFORM_PWT_UAT = 'https://staging.ptranz.com/api/';
    const PLATFORM_PWT_PROD = 'https://gateway.ptranz.com/api/';

    const CONFIG_KEY_PWTID = 'PWTId';
    const CONFIG_KEY_PWTPWD = 'PWTpwd';
    const CONFIG_KEY_MERCHANT_RESPONSE_URL = 'merchantResponseURL';
    const CONFIG_KEY_WEBHOOK_URL = 'webHookURL';

    const AUTHORIZE_OPTION_3DS = 'ThreeDSecure';
    const GATEWAY_ORDER_IDENTIFIER_PREFIX = 'orderNumberPrefix';
    const GATEWAY_ORDER_IDENTIFIER_AUTOGEN = 'orderNumberAutoGen';
    const GATEWAY_ORDER_IDENTIFIER = 'orderIdentifier';


    const CONFIG_KEY_PWTCUR = 'facCurrencyList';

    const CONFIG_BILLING_STATE_CODE = 'MB';
    const CONFIG_COUNTRY_CURRENCY_CODE = '780';
}