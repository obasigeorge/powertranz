``` php

require_once 'autoload.php';

use PowerTranz\PowerTranz;

try {
    $gateway = new PowerTranz;
    $gateway
        ->setTestMode(true)  // false to use productions links  , true to use test links 
        ->setPWTId('xxxxxxxx') 
        ->setPWTPwd('xxxxxxxx')
        // **Required and must be https://
        ->setMerchantResponseURL('https://localhost/accept-notification.php')
        // *** Autogen an order number  UUID V4
        ->setOrderNumberAutoGen(true);
        // Set Order Number Prefix - Default PWT
        // ->setOderNumberPrefix('some-string-of-chars')
        // Set Order Number
        // ->setOrderNumber('some-string-of-chars')

    $cardData = [
        'number' => '4111111111111111', //Optional
        'expiryMonth' => '01', //Optional
        'expiryYear' => '2025',  ///Optional
        'cvv' => '123',   //Optional
        'firstName' => 'Jonh', //Mandatory
        'lastName' => 'Doe',   //Mandatory
        'email' => "johDoe@gmail.com", //Optional
        'Address1' => 'main Avenue', //Optional
        'Address2' => 'Main Avenue', //Optional
        'City' => 'Marabella', //Mandatory
        'State' => '',   //Mandatory
        'Postcode' => '',  //Optional
        'Country' => '780',   //Mandatory 780
        'Phone' => '',  //Optional
    ];

    $transactionData = [
        'card' => $cardData,
        'currency' => '780',  //Mandatory  780
        'amount' => '1.00',   //Mandatory
        "AddressMatch" => "false",  //Optional  
        'validCardType' => [  //Optional
            'visa',
            'mastercard',
            'american-express',
            'diners-club',
            'discover',
            'jcb',
            'unionpay',
            'maestro',
            'elo',
            'mir',
            'hiper',
            'hipercard'
        ]  
    ];

    $response = $gateway->authorize($transactionData);
    // $response = $gateway->getHostedPage($transactionData, $pageSet, $pageName);

    if($response->isRedirect())
    {
	    // Redirect to continue 3DS verification
        print( $response->redirect() );
    }
    else 
    {
	    // 3DS transaction failed setup, show error reason.
        echo $response->getMessage();
    }
} catch (\Exception $e){
    $e->getMessage();
}
```

***accept-notification.php***
Accept transaction response from PowerTranz.
```php
require_once 'autoload.php';

use PowerTranz\PowerTranz;

try {
    $gateway = new PowerTranz;
    $gateway 
        ->setTestMode(true) // false to use productions links  , true to use test links 
        // Password is required to perform response signature verification
        ->setPWTId('xxxxxxxx')
        ->setPWTPwd('xxxxxxxx');
        
    // Signature verification is performed implicitly once the gateway was initialized with the password.
    $response = $gateway->acceptNotification($_POST);

    if($response->isSuccessful())
    {       
        // authorize was succussful, continue purchase the payment    
        $paymentResponse = $gateway->purchase($response->getSpiToken());
        
        //return a JSON with response    //Aproved = true means payment successfull
        if ($paymentResponse->isSuccessful()) {
            $captureResponse = $gateway->capture($paymentResponse->getDataArray());

            // debug
            print_r($paymentResponse->getData());
        }

        // debug
        print_r($paymentResponse->getData());
        
    }
    else 
    {
        // Transaction failed
        echo $response->getMessage();
    }
} catch (\Exception $e) {
    // an error occurred, catch it an show something to the customer
    $e->getMessage();
}
```
