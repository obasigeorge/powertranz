``` php

require_once powertranz.lib.php;

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

    $cardData = [
        'number' => '4111111111111111', //Mandatory
        'expiryMonth' => '01', //Mandatory
        'expiryYear' => '2025',  ///Mandatory
        'cvv' => '123',   //Mandatory
        'firstName' => 'Jonh', //Mandatory
        'LastName' => 'Doe',   //Mandatory
        'email' => "johDoe@gmail.com", //optional
        'Address1' => 'main Avenue', // optional
        'Address2' => 'Main Avenue', // optional
        'City' => 'Marabella', // Mandatory
        'State' => '',   //Mandatory
        'Postcode' => '',  //Optional
        'Country' => '780',   //Mandatory GTQ
        'Phone' => '',  // Optional
    ];

    $transactionData = [
        'card' => $cardData,
        'currency' => '780',  // Mandatory  780
        'amount' => '1.00',   // Mandatory
        ///'TransactionId' => '2100001',  // is mandatory is setOrderNumberAutoGen is false
        "AddressMatch" => "false"   //Optional  
    ];

    $response = $gateway->authorize($transactionData);

    if($response->isRedirect())
    {
	    // Redirect to continue 3DS verification
        $response->redirect();
    }
    else 
    {
	    // 3DS transaction failed setup, show error reason.
        echo $response->getMessage();
    }
} catch (Exception $e){
    $e->getMessage();
}
```

***accept-notification.php***
Accept transaction response from PowerTranz.
```php
$gateway = new PowerTranz;
$gateway    
    // Password is required to perform response signature verification
    ->setPWTId('xxxxxxxx')
    ->setPWTPwd('xxxxxxxx')
    
// Signature verification is performed implicitly once the gateway was initialized with the password.
$response = $gateway->acceptNotification($_POST);

if($response->isSuccessful())
{       
    // authorize was succussful, continue purchase the payment    
     $paymentResponse = $gateway->purchase($response->getSpiToken());
    
    //return a JSON with response    //Aproveed = true means payment successfull 
    print_r($paymentResponse->getData());
    
}
else 
{
    // Transaction failed
    echo $response->getMessage();
}
```
