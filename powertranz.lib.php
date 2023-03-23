<?php
/**
 * @author Obasi Adande George
 * @copyright (c) 2023 Obasi Adande George
 */
class PowerTranz {

    private $powerTranzId = "PWTId";
    private $powerTranzPwd = "PWTPwd";
    private $isTestMode = false;
    private $merchantResponseURL = "merchantResponseURL";
    private $orderNumberAutoGen = false;
    private $transactionNumberAutoGen = true;
    private $use3DS = true;
    private $checkFraud = false;

    protected $platformPWTUAT = 'https://staging.ptranz.com/api/spi/';
    protected $platformPWTPROD = 'https://tbd.ptranz.com/api/spi/';

    private $transactionData = [];
    private $orderNumber = "orderNumber";
    private $orderNumberSet = false;
    private $transactionNumber = "transactionNumber";
    private $transactionNumberSet = false;
    private $orderNumberPrefix = "PWT";

    const DEFAULT_TRANSACTION_CURRENCY = "780";

    public function __construct() {}

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
        return $this->powerTranzId;
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
        return $this->powerTranzPwd;
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

    public function getTestMode()
    {
        return $this->isTestMode;
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
        return ($this->getTestMode()) ? $this->platformPWTUAT : $this->platformPWTPROD;
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
        return $this->merchantResponseURL;
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
        return $this->orderNumberPrefix;
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
    }

    /**
     * Get Order Number
     * 
     * @return string
     */
    public function getOrderNumber()
    {
        if ($this->orderNumberAutoGen && !$this->orderNumberSet)
            $this->setOrderNumber ("{$this->getOrderNumberPrefix()}{$this->guidv4()}");

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
     * @return PowerTranzResponse
     */
    public function authorize($transactionData)
    {
        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['LastName']);

        $this->transactionData['Source'] = [
            'CardPan' => CreditCard::number($transactionData['card']['number']),
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = self::curl($this->transactionData, 'auth');

        return new PowerTranzResponse( $response );
    }

    /**
     * Authorization Request using PowerTranz Token
     * 
     * @param array $transactionData
     * 
     * @return PowerTranzResponse
     */
    public function authorizeWithToken($transactionData)
    {
        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['LastName']);

        $this->transactionData['Tokenize'] = true;

        $this->transactionData['Source'] = [
            'Token' => $transactionData['card']['number'],
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->curl($this->transactionData, 'auth');

        return new PowerTranzResponse( $response );
    }

    /**
     * Authorization Request using Sentry Token
     * 
     * @param array $transactionData
     * 
     * @return PowerTranzResponse
     */
    public function authorizeWithSentryToken($transactionData)
    {
        $this->setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['LastName']);

        $this->transactionData['Tokenize'] = true;

        $this->transactionData['Source'] = [
            'Token' => $transactionData['card']['number'],
            'TokenType' => 'PG2',
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->curl($this->transactionData, 'auth');

        return new PowerTranzResponse( $response );
    }

    /**
     * Get Hosted Page
     * 
     * @param array $transactionData,
     * @param string $pageSet
     * @param string $pageName
     * 
     * @return PowerTranzResponse
     */
    public function getHostedPage($transactionData, $pageSet, $pageName)
    {
        $this->setData($transactionData);

        $this->transactionData['ExtendedData']['HostedPage'] = [
            'PageSet' => $pageSet,
            'PageName' => $pageName,
        ];

        $response = $this->curl($this->transactionData, 'auth');

        return new PowerTranzResponse( $response );
    }

    /**
     * @return PowerTranzResponse
     */
    public function acceptNotification($data)
    {
        // to-do
        // validate data response from callback
        return new PowerTranzResponse( $data );
    }

    /**
     * Complete Purchase Transaction
     * 
     * @param string $spitoken
     * 
     * @return PowerTranzResponse
     */
    public function purchase($spitoken)
    {
        $response = $this->curl($spitoken, 'payment');

        return new PowerTranzResponse( $response );
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
        $expiry = sprintf('%02d%02d', (strlen($transactionData['card']['expiryYear']) == 4) ? substr($transactionData['card']['expiryYear'], 2, 2) : $transactionData['card']['expiryYear'], $transactionData['card']['expiryMonth']);
        $holder = sprintf('%s %s', $transactionData['card']['firstName'], $transactionData['card']['LastName']);

        $this->setData($transactionData);

        $this->transactionData['Tokenize'] = true;
        $this->transactionData['ThreeDSecure'] = false;
        $this->transactionData['Source'] = [
            'CardPan' => CreditCard::number($transactionData['card']['number']),
            'CardCvv' => $transactionData['card']['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = $this->curl($this->transactionData, 'riskmgmt');

        return new PowerTranzResponse( $response );
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

        return new PowerTranzResponse( $response );
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

        return new PowerTranzResponse( $response );
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

        return new PowerTranzResponse( $response );
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
            'CurrencyCode' => $data['currency'] ?? self::DEFAULT_TRANSACTION_CURRENCY,
            'ThreeDSecure' => $this->use3DS,
            'FraudCheck' => $this->checkFraud,
            'Source' => [],
            'OrderIdentifier' => $this->getOrderNumber(),
            'BillingAddress' => [
                'FirstName' => $data['card']['firstName'] ?? '',
                'LastName' => $data['card']['LastName'] ?? '',
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
                'MerchantResponseUrl' => $this->getMerchantResponseURL(),
            ],
        ];
    }

    /** 
     * curl function
     * 
     * @param array|string $data
     * @param string $api
     * 
     * @return array
    */
    private function curl( $data, $api )
    {
        $postData = (is_array($data)) ? json_encode($data) : $data;

        // add API Segment iff necessary
        $url = $this->getEndpoint() . $api;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // Set HTTP Header for POST request 
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($postData),
            'PowerTranz-PowerTranzId: ' . $this->getPWTId(),
            'PowerTranz-PowerTranzPassword: ' . $this->getPWTPwd(),
            ]
        );

        try {
            $result = curl_exec($ch);

            if ($result === false)
                throw new Exception('Curl error: ' . curl_error($ch));
            
            $decoded = urldecode($result);
            $decoded = trim( $decoded );

            return json_decode( $decoded );
        } catch (Exception $e) {
            print( $e->getMessage());
        }

        curl_close($ch);
    }

    /**
     * Generate an Unique Identifier
     * 
     * @param string|null $data
     * 
     * @return string
     */
    private function guidv4($data = null) {
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

class PowerTranzResponse {

    private $transactionData = [];

    /**
     * Power Tranz Response Constructor
     * 
     * @param array $data
     * 
     * @return PowerTranzResponse
     */
    public function __construct($data)
    {
        $this->transactionData = $data;
    }

    /**
     * Check if Redirect is Required
     * 
     * @return boolean
     */
    public function isRedirect()
    {
        return isset($this->transactionData['RedirectData']) ? true : false;
    }

    /**
     * Redirect Data
     * 
     * @return string
     */
    public function redirect()
    {
        return $this->transactionData['RedirectData'];
    }

    /**
     * Get Response Code
     * 
     * @return string
     */
    public function getCode()
    {
        return $this->transactionData['IsoResponseCode'] ?? '';
    }

    /**
     * Get Response Message
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->transactionData['ResponseMessage'] ?? '';
    }

    /**
     * Get Entire Transaction Response
     * 
     * @return array
     */
    public function getData()
    {
        return json_encode(self::$transactionData);
    }

    /**
     * Get Transaction Number
     * 
     * @return string
     */
    public function getTransactionNumber()
    {
        return $this->transactionData['TransactionIdentifier'] ?? '';
    }

    /**
     * Get Order Number
     * 
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->transactionData['OrderIdentifier'] ?? '';
    }

    /**
     * Get SPI Token
     * 
     * @return string
     */
    public function getSpiToken()
    {
        return $this->transactionData['SpiToken'] ?? '';
    }

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return (intval($this->transactionData['ResponseCode']) === 1) ? true : false;
    }
}


class CreditCard 
{
    /**
     * Replaces all but the first and last four digits with x's in the given credit card number
     * 
     * @param int|string $cc The credit card number to mask
     *   
     * @return string The masked credit card number
     */
    public static function mask( $cc )
    {
        // replace all digits with X except for the first and last four.
        $cc = preg_replace('/(?!^.?)[0-9](?!(.){0,3}$)/', 'X', $cc);
        
        // Return the masked Credit Card #
        return $cc;
    }

    /**
     * Add dashes to a credit card number.
     *
     * @param int|string $cc The credit card number to format with dashes.
     * 
     * @return string The credit card with dashes.
     */
    public static function format( $cc )
    {
        // Clean out extra data that might be in the cc
        $cc = str_replace(array('-',' '),'',$cc);

        // Get the CC Length
        $cc_length = strlen($cc);

        // Initialize the new credit card to contian the last four digits
        $newCreditCard = substr($cc,-4);

        // Walk backwards through the credit card number and add a dash after every fourth digit
        for ($i=$cc_length-5; $i>=0; $i--)
        {
            // If on the fourth character add a dash
            if((($i+1)-$cc_length)%4 == 0){
                $newCreditCard = '-'.$newCreditCard;
            }
            // Add the current character to the new credit card
            $newCreditCard = $cc[$i].$newCreditCard;
        }

        // Return the formatted credit card number
        return $newCreditCard;
    }

    /**
     * Remove all non numeric characters from a credit card number
     * 
     * @param int|string $cc
     * 
     * @return int
     */
    public static function number( $cc )
    {
        // remove all non-numeric characters
        preg_match_all('/([0-9])/', $cc, $matches);

        // Return number 
        return implode('', $matches[1]);
    }
}
