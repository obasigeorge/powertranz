<?php
/**
 * @author Obasi Adande George
 * @copyright (c) 2023 Obasi Adande George
 */
class PowerTranz {

    protected $powerTranzId = "PWTId";
    protected $powerTranzPwd = "PWTPwd";
    protected $isTestMode = false;
    protected $merchantResponseURL = "merchantResponseURL";
    protected $orderNumberAutoGen = false;
    protected $transactionNumberAutoGen = true;
    protected $use3DS = true;

    protected $platformPWTUAT = 'https://staging.ptranz.com/api/spi/';
    protected $platformPWTPROD = 'https://tbd.ptranz.com/api/spi/';

    protected $transactionData = [];
    protected $orderNumber = "orderNumber";
    protected $transactionNumber = "transactionNumber";
    protected $orderNumberPrefix = "PWT";

    const DEFAULT_TRANSACTION_CURRENCY = "780";

    public function __construct() {}

    /**
     * Set PowerTranz Id
     * 
     * @param string $id
     */
    public function setPWTId($id)
    {
        self::$powerTranzId = $id;
    }

    public function getPWTId()
    {
        return self::$powerTranzId;
    }

    /**
     * Set PowerTranz Password
     * 
     * @param string $pwd
     */
    public function setPWTPwd($pwd)
    {
        self::$powerTranzPwd = $pwd;
    }

    public function getPWTPwd()
    {
        return self::$powerTranzPwd;
    }

    /**
     * Set PowerTranz Mode
     * 
     * @param boolean $mode
     */
    public function setTestMode($mode = false)
    {
        self::$isTestMode = $mode;
    }

    public function getTestMode()
    {
        return self::$isTestMode;
    }

    /**
     * Set 3DS Mode
     * 
     * @param boolean $mode
     */
    public function set3DSMode($mode = true)
    {
        self::$use3DS = $mode;
    }

    public function getEndpoint()
    {
        return (self::getTestMode()) ? self::$platformPWTUAT : self::$platformPWTPROD;
    }

    /**
     * Set Merchant Callback URL
     * 
     * @param string $url
     */
    public function setMerchantURL($url)
    {
        self::$merchantResponseURL = $url;
    }

    /**
     * Set OrderNumber Auto Generation Mode
     * 
     * @param boolean $auto
     */
    public function setOrderNumberAutoGen($auto = false)
    {
        self::$orderNumberAutoGen = $auto;
    }

    /**
     * Set Order Number Prefix
     * 
     * @param string $prefix
     */
    public function setOrderNumberPrefix($prefix)
    {
        self::$orderNumberPrefix = $prefix;
    }

    public function getOrderNumberPrefix()
    {
        return self::$orderNumberPrefix;
    }

    /**
     * Set Order Number
     * 
     * @param string $num
     * @return null
     */
    public function setOrderNumber($num)
    {
        self::$orderNumber = $num;
    }

    /**
     * Get Order Number
     * 
     * @return string
     */
    public function getOrderNumber()
    {
        if (self::$orderNumberAutoGen)
            self::$orderNumber = "{$this->getOrderNumberPrefix()}{$this->guidv4()}";

        return self::$orderNumber;
    }

    /**
     * Set Transaction Number Auto Generation Mode
     * 
     * @param boolean $auto
     * @return null
     */
    public function setTransactionNumberAutoGen($auto = true)
    {
        self::$orderNumberAutoGen = $auto;
    }

    /**
     * Set Transaction Number
     * 
     * @param string $num
     * @return null
     */
    public function setTransactionNumber($num) 
    {
        self::$transactionNumber = $num;
    }

    /**
     * Get Transaction Number
     * 
     * @return string
     */
    public function getTransactionNumber()
    {
        if (self::$transactionNumberAutoGen)
            self::$transactionNumber = "{$this->guidv4()}";
        
        return  self::$transactionNumber;
    }

    /**
     * 
     * 
     * @param array $transactionData
     * 
     * @return PowerTranzResponse
     */
    public function authorize($transactionData)
    {
        self::setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['expiryYear']) == 4) ? substr($transactionData['expiryYear'], 2, 2) : $transactionData['expiryYear'], $transactionData['expiryMonth']);
        $holder = sprintf('%s %s', $transactionData['firstName'], $transactionData['LastName']);

        self::$transactionData['Source'] = [
            'CardPan' => CreditCard::number($transactionData['number']),
            'CardCvv' => $transactionData['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = self::curl(self::$transactionData, 'auth');

        return new PowerTranzResponse( $response );
    }

    /**
     * 
     * 
     * @param array $transactionData
     * 
     * @return PowerTranzResponse
     */
    public function authorizeWithToken($transactionData)
    {
        self::setData($transactionData);

        $expiry = sprintf('%02d%02d', (strlen($transactionData['expiryYear']) == 4) ? substr($transactionData['expiryYear'], 2, 2) : $transactionData['expiryYear'], $transactionData['expiryMonth']);
        $holder = sprintf('%s %s', $transactionData['firstName'], $transactionData['LastName']);

        self::$transactionData['Tokenize'] = true;

        self::$transactionData['Source'] = [
            'Token' => CreditCard::number($transactionData['number']),
            'CardCvv' => $transactionData['cvv'],
            'CardExpiration' => $expiry,
            'CardholderName' => $holder,
        ];

        $response = self::curl(self::$transactionData, 'auth');

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
        self::setData($transactionData);

        self::$transactionData['ExtendedData']['HostedPage'] = [
            'PageSet' => $pageSet,
            'PageName' => $pageName,
        ];

        $response = self::curl(self::$transactionData, 'auth');

        return new PowerTranzResponse( $response );
    }

    /**
     * @return PowerTranzResponse
     */
    public function acceptNotification($data)
    {
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
        $response = self::curl($spitoken, 'payment');

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
        $expiry = sprintf('%02d%02d', (strlen($transactionData['expiryYear']) == 4) ? substr($transactionData['expiryYear'], 2, 2) : $transactionData['expiryYear'], $transactionData['expiryMonth']);
        $holder = sprintf('%s %s', $transactionData['firstName'], $transactionData['LastName']);

        self::$transactionData = [
            'TransactionIdentifier' => self::getTransactionNumber(),
            'TotalAmount' => 0,
            'CurrencyCode' => $transactionData['currency'] ?? self::DEFAULT_TRANSACTION_CURRENCY,
            'Tokenize' => true,
            'ThreeDSecure' => false,
            'Source' => [
                'CardPan' => CreditCard::number($transactionData['number']),
                'CardCvv' => $transactionData['cvv'],
                'CardExpiration' => $expiry,
                'CardholderName' => $holder,
            ],
            'OrderIdentifier' => self::getOrderNumber(),
        ];

        $response = $this->curl(self::$transactionData, 'RiskMgmt');

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
        self::$transactionData = [
            'TransactionIdentifier' => $transactionNumber,
            'ExternalIdentifier' => 'null',
            'TerminalCode' => '',
            'TerminalSerialNumber' => '',
            'AutoReversal' => false,
        ];

        $response = $this->curl(self::$transactionData, 'void');

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
        self::$transactionData = [
            'TransactionIdentifier' => $transactionData['transactionNumber'],
            'Refund' => true,
            'TotalAmount' => $transactionData['amount'] ?? 0,
        ];

        $response = $this->curl(self::$transactionData, 'refund');

        return new PowerTranzResponse( $response );
    }

    /**
     * Set transactionData variable
     * 
     * @param array $data
     */
    private function setData( $data )
    {
        self::$transactionData = [
            'TransactionIdentifier' => self::getTransactionNumber(),
            'TotalAmount' => $data['amount'] ?? 0,
            'CurrencyCode' => $data['currency'] ?? self::DEFAULT_TRANSACTION_CURRENCY,
            'ThreeDSecure' => self::$use3DS,
            'Source' => [],
            'OrderIdentifier' => self::getOrderNumber(),
            'BillingAddress' => [
                'FirstName' => $data['firstName'] ?? '',
                'LastName' => $data['LastName'] ?? '',
                'Line1' => $data['Address1'] ?? '',
                'Line2' => $data['Address2'] ?? '',
                'City' => $data['City'] ?? '',
                'State' => $data['State'] ?? '',
                'PostalCode' => $data['Postcode'] ?? '',
                'CountryCode' => $data['Country'] ?? '',
                'EmailAddress' => $data['email'] ?? '',
                'PhoneNumber' => $data['Phone'] ?? '',
            ],
            'AddressMatch' => $data['AddressMatch'] ?? false, 
            'ExtendedData' => [
                'MerchantResponseUrl' => self::$merchantResponseURL ?? '',
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
        $url = self::getEndpoint() . $api;

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
            'PowerTranz-PowerTranzId: ' . self::getPWTId(),
            'PowerTranz-PowerTranzPassword: ' . self::getPWTPwd(),
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
        if (!defined('PHP_VERSION_ID'))
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
        self::$transactionData = $data;
    }

    /**
     * Check if Redirect is Required
     * 
     * @return boolean
     */
    public function isRedirect()
    {
        return isset(self::$transactionData['RedirectData']) ? true : false;
    }

    /**
     * Redirect Data
     * 
     * @return string
     */
    public function redirect()
    {
        return self::$transactionData['RedirectData'];
    }

    /**
     * Get Response Code
     * 
     * @return string
     */
    public function getCode()
    {
        return self::$transactionData['IsoResponseCode'] ?? '';
    }

    /**
     * Get Response Message
     * 
     * @return string
     */
    public function getMessage()
    {
        return self::$transactionData['ResponseMessage'] ?? '';
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
        return self::$transactionData['TransactionIdentifier'] ?? '';
    }

    /**
     * Get Order Number
     * 
     * @return string
     */
    public function getOrderNumber()
    {
        return self::$transactionData['OrderIdentifier'] ?? '';
    }

    /**
     * Get SPI Token
     * 
     * @return string
     */
    public function getSpiToken()
    {
        return self::$transactionData['SpiToken'] ?? '';
    }

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return (intval(self::$transactionData['ResponseCode']) === 1) ? true : false;
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

    public static function number( $cc )
    {
        // remove all non-numeric characters
        preg_match_all('/([0-9])/', $cc, $matches);

        // Return number 
        return implode('', $matches[1]);
    }
}
