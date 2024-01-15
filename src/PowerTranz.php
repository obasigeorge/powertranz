<?php

namespace PowerTranz;

use PowerTranz\Exception;
use PowerTranz\Support;
use PowerTranz\Message;
use PowerTranz\Interfaces\PowerTranzInterface;

class PowerTranz implements PowerTranzInterface {

    private $powerTranzId = NULL;
    private $powerTranzPwd = NULL;
    private $isTestMode = false;
    private $merchantResponseURL = NULL;
    private $orderNumberAutoGen = false;
    private $transactionNumberAutoGen = true;
    private $use3DS = true;
    private $checkFraud = false;

    private $transactionData = [];
    private $orderNumber = NULL;
    private $orderNumberSet = false;
    private $transactionNumber = NULL;
    private $transactionNumberSet = false;
    private $orderNumberPrefix = NULL;

    private $cardValidator = NULL;

    public function __construct() {}

    public function getName()
    {
        return Support\Constants::DRIVER_NAME;
    }

    /**
     * Set PowerTranz Id
     * 
     * @param string $id
     */
    public function setPWTId($id)
    {
        $this->powerTranzId = $id;
    }

    public function getPWTId()
    {
        return $this->powerTranzId ?? Support\Constants::CONFIG_KEY_PWTID;
    }

    /**
     * Set PowerTranz Password
     * 
     * @param string $pwd
     */
    public function setPWTPwd($pwd)
    {
        $this->powerTranzPwd = $pwd;
    }

    public function getPWTPwd()
    {
        return $this->powerTranzPwd ?? Support\Constants::CONFIG_KEY_PWTPWD;
    }

    /**
     * Set PowerTranz Mode
     * 
     * @param boolean $mode
     */
    public function setTestMode($mode = false)
    {
        $this->isTestMode = $mode;
    }

    /**
     * Enable Test Mode
     */
    public function enableTestMode()
    {
        $this->setTestMode(true);
    }

    /**
     * Set 3DS Mode
     * 
     * @param boolean $mode
     */
    public function set3DSMode($mode = true)
    {
        $this->use3DS = $mode;
    }

    /**
     * Set Fraud Check Mode
     * 
     * @param boolean $mode
     */
    public function setFraudCheckMode($mode = true)
    {
        $this->checkFraud = $mode;
    }

    public function getEndpoint()
    {
        return ($this->isTestMode) ? Support\Constants::PLATFORM_PWT_UAT : Support\Constants::PLATFORM_PWT_PROD;
    }

    /**
     * Set Merchant Callback URL
     * 
     * @param string $url
     */
    public function setMerchantResponseURL($url)
    {
        $this->merchantResponseURL = $url;
    }

    /**
     * Get Merchant Callback URL
     * 
     * @return string
     */
    public function getMerchantResponseURL()
    {
        return $this->merchantResponseURL ?? Support\Constants::CONFIG_KEY_MERCHANT_RESPONSE_URL;
    }

    /**
     * Set OrderNumber Auto Generation Mode
     * 
     * @param boolean $auto
     */
    public function setOrderNumberAutoGen($auto = false)
    {
        $this->orderNumberAutoGen = $auto;
    }

    /**
     * Set Order Number Prefix
     * 
     * @param string $prefix
     */
    public function setOrderNumberPrefix($prefix)
    {
        $this->orderNumberPrefix = $prefix;
    }

    public function getOrderNumberPrefix()
    {
        return $this->orderNumberPrefix ?? Support\Constants::GATEWAY_ORDER_IDENTIFIER_PREFIX;
    }

    /**
     * Set Order Number
     * 
     * @param string $num
     * @return null
     */
    public function setOrderNumber($num)
    {
        $this->orderNumber = $num;
        $this->orderNumberSet = true;
    }

    /**
     * Get Order Number
     * 
     * @return string
     */
    public function getOrderNumber()
    {
        if ($this->orderNumberAutoGen && !$this->orderNumberSet)
            $this->setOrderNumber("{$this->getOrderNumberPrefix()}-{$this->timestamp()}-{$this->guidv4()}");
        if (!$this->orderNumberSet)
            $this->setOrderNumber("{$this->getOrderNumberPrefix()}-{$this->timestamp()}-{$this->getTransactionNumber()}");

        return $this->orderNumber;
    }

    /**
     * Set Transaction Number Auto Generation Mode
     * 
     * @param boolean $auto
     * @return null
     */
    public function setTransactionNumberAutoGen($auto = true)
    {
        $this->orderNumberAutoGen = $auto;
    }

    /**
     * Set Transaction Number
     * 
     * @param string $num
     * @return null
     */
    public function setTransactionNumber($num) 
    {
        $this->transactionNumber = $num;
        $this->transactionNumberSet = true;
    }

    /**
     * Get Transaction Number
     * 
     * @return string
     */
    public function getTransactionNumber()
    {
        if ($this->transactionNumberAutoGen && !$this->transactionNumberSet)
            $this->setTransactionNumber("{$this->guidv4()}");
        
        return $this->transactionNumber;
    }

    /**
     * Authorization Request using Full Card Pan
     * 
     * @param array $transactionData
     * 
     * @return Authorize3DSResponse
     */
    public function authorize($transactionData)
    {
        $this->validateCreditCard($transactionData);

        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = $transactionData['card']['name'] ?? sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['lastName']);

        $this->transactionData['Source'] = [
            'CardPan' => Support\CreditCard::number($transactionData['card']['number']),
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->curl($this->transactionData, 'spi/auth');

        return new Message\Authorize3DSResponse( $response );
    }

    /**
     * Authorization Request using PowerTranz Token
     * 
     * @param array $transactionData
     * 
     * @return Authorize3DSResponse
     */
    public function authorizeWithToken($transactionData)
    {
        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = $transactionData['card']['name'] ?? sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['lastName']);

        $this->transactionData['Tokenize'] = true;

        $this->transactionData['Source'] = [
            'Token' => $transactionData['card']['number'],
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->curl($this->transactionData, 'spi/auth');

        return new Message\Authorize3DSResponse( $response );
    }

    /**
     * Authorization Request using Sentry Token
     * 
     * @param array $transactionData
     * 
     * @return Authorize3DSResponse
     */
    public function authorizeWithSentryToken($transactionData)
    {
        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = $transactionData['card']['name'] ?? sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['LastName']);

        $this->transactionData['Tokenize'] = true;

        $this->transactionData['Source'] = [
            'Token' => $transactionData['card']['number'],
            'TokenType' => 'PG2',
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->curl($this->transactionData, 'spi/auth');

        return new Message\Authorize3DSResponse( $response );
    }

    /**
     * Get Hosted Page
     * 
     * @param array $transactionData,
     * @param string $pageSet
     * @param string $pageName
     * 
     * @return HostedPageResponse
     */
    public function getHostedPage($transactionData, $pageSet, $pageName)
    {
        $this->setData($transactionData);

        $this->transactionData['ExtendedData']['HostedPage'] = [
            'PageSet' => $pageSet,
            'PageName' => $pageName,
        ];

        $response = $this->curl($this->transactionData, 'spi/auth');

        return new Message\HostedPageResponse( $response );
    }

    /**
     * @return ThreeDSResponse
     */
    public function acceptNotification($data)
    {
        // to-do
        // validate data response from callback
        return new Message\ThreeDSResponse( json_decode($data['Response']) );
    }

    /**
     * Complete Purchase Transaction
     * 
     * @param string $spitoken
     * 
     * @return PurchaseResponse
     */
    public function purchase($spitoken)
    {
        $response = $this->curl("\"{$spitoken}\"", 'spi/payment', 'text/plain');

        return new Message\PurchaseResponse( $response );
    }

    /**
     * Tokenize a Card Pan
     * 
     * @param array $transactiondata
     * 
     * @return PowerTranzResponse
     */
    public function tokenize($transactionData)
    {
        $this->validateCreditCard($transactionData);
        
        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = $transactionData['card']['name'] ?? sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['LastName']);

        $this->setData($transactionData);

        $this->transactionData['Tokenize'] = true;
        $this->transactionData['ThreeDSecure'] = false;
        $this->transactionData['Source'] = [
            'CardPan' => Support\CreditCard::number($transactionData['card']['number']),
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->curl($this->transactionData, 'riskmgmt');

        return new Message\GenericResponse( $response );
    }

    /**
     * Void Transaction
     * 
     * @param string $transactionNumber
     * 
     * @return PowerTranzResponse
     */
    public function void($transactionNumber)
    {
        $this->transactionData = [
            'TransactionIdentifier' => $transactionNumber,
            'ExternalIdentifier' => 'null',
            'TerminalCode' => '',
            'TerminalSerialNumber' => '',
            'AutoReversal' => false,
        ];

        $response = $this->curl($this->transactionData, 'void');

        return new Message\GenericResponse( $response );
    }

    /**
     * Capture a specific amount of a transaction
     * 
     * @param array $transactionData
     * 
     * @return PowerTranzResponse
     */
    public function capture($transactionData)
    {
        $this->transactionData = [
            'TransactionIdentifier' => $transactionData['transactionNumber'],
            'TotalAmount' => $transactionData['amount'] ?? 0,
        ];

        $response = $this->curl($this->transactionData, 'capture');

        return new Message\GenericResponse( $response );
    }

    /**
     * Refund Transaction
     * 
     * @param array $transactionData
     * 
     * @return PowerTranzResponse
     */
    public function refund($transactionData)
    {
        $this->transactionData = [
            'TransactionIdentifier' => $transactionData['transactionNumber'],
            'Refund' => true,
            'TotalAmount' => $transactionData['amount'] ?? 0,
        ];

        $response = $this->curl($this->transactionData, 'refund');

        return new Message\GenericResponse( $response );
    }

    /**
     * Validate credit card
     * 
     * @param array $data
     */
    private function validateCreditCard( $data )
    {
        $this->cardValidator = isset($data['validCardType']) ? Support\CreditCardValidator::make($data['validCardType']) : Support\CreditCardValidator::make();

        if (!$this->cardValidator->isValid($data['card']['number']))
            throw new Exception\InvalidCreditCard('Invalid Credit Card Number Supplied');
    }

    /**
     * Set transactionData variable
     * 
     * @param array $data
     */
    private function setData( $data )
    {
        $this->transactionData = [
            'TransactionIdentifier' => $this->getTransactionNumber(),
            'TotalAmount' => $data['amount'] ?? 0,
            'CurrencyCode' => $data['currency'] ?? Support\Constants::CONFIG_COUNTRY_CURRENCY_CODE,
            'ThreeDSecure' => $this->use3DS,
            'FraudCheck' => $this->checkFraud,
            'OrderIdentifier' => $this->getOrderNumber(),
            'BillingAddress' => [
                'FirstName' => $data['card']['firstName'] ?? '',
                'LastName' => $data['card']['lastName'] ?? '',
                'Line1' => $data['card']['Address1'] ?? '',
                'Line2' => $data['card']['Address2'] ?? '',
                'City' => $data['card']['City'] ?? '',
                'State' => $data['card']['State'] ?? '',
                'PostalCode' => $data['card']['Postcode'] ?? '',
                'CountryCode' => $data['card']['Country'] ?? '',
                'EmailAddress' => $data['card']['email'] ?? '',
                'PhoneNumber' => $data['card']['Phone'] ?? '',
            ],
            'AddressMatch' => $data['AddressMatch'] ?? false, 
            'ExtendedData' => [
                'ThreeDSecure' => [
                    'ChallengeWindowSize' => 4,
                    'ChallengeIndicator' => '01',
                ],
                'MerchantResponseUrl' => $this->getMerchantResponseURL(),
            ],
        ];
    }

    /** 
     * curl function
     * 
     * @param array|string $data
     * @param string $api
     * @param string $accept
     * @param string $method
     * 
     * @return array
    */
    private function curl( $data, $api, $accept = 'application/json', $method = 'POST' )
    {
        $postData = (is_array($data)) ? json_encode($data) : $data;

        // add API Segment iff necessary
        $url = "{$this->getEndpoint()}{$api}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 150); 

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        } elseif ($method == 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Set HTTP Header for request 
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: {$accept}",
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData),
            "PowerTranz-PowerTranzId: {$this->getPWTId()}",
            "PowerTranz-PowerTranzPassword: {$this->getPWTPwd()}",
            ]
        );

        try {
            $result = curl_exec($ch);

            if (!curl_errno($ch))
            {
                switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE))
                {
                    case 200: // OK
                        break;
                    default:
                        throw new Exception\GatewayHTTPException("Gateway Communication error: ({$http_code})" . curl_error($ch));
                        break;
                }
            }

            $decoded = urldecode($result);
            $decoded = trim($decoded);

            return json_decode($decoded);
        } catch (\Exception $e) {
            throw new Exception\GatewayHTTPException($e->getMessage());
        }

        curl_close($ch);
    }

    /**
     * Generate timestamp
     * 
     * @param null
     * 
     * @return string
     */
    private function timestamp()
    {
        $utimestamp = microtime(true);
        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, 'YmdHisu'), $timestamp);
    }

    /**
     * Generate an Unique Identifier
     * 
     * @param string|null $data
     * 
     * @return string
     */
    private function guidv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $version = explode('.', PHP_VERSION);
        
        if ($version[0] == 5)
            $data = $data ?? openssl_random_pseudo_bytes(16);
        else
            $data = $data ?? random_bytes(16);
        
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}