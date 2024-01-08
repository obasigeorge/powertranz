<?php
require_once '../autoload.php';

use PowerTranz\PowerTranz;

try {
    $gateway = new PowerTranz;
    $gateway->setTestMode(true);  // false to use productions links  , true to use test links 
    $gateway->setPWTId('88800371');
    $gateway->setPWTPwd('3UjKgQyo5SzmUZtwTkeYAbMFzyxKB9UfnuFgOSuk8VgdpTXq0wBzMy3');
    // **Required and must be https://
    $gateway->setMerchantResponseURL('https://localhost-development.tstt.co.tt/example/accept-notification.php');
    
    // *** Autogen an order number  UUID V4
    // $gateway->setOrderNumberAutoGen(true);
    // Set Order Number Prefix - Default PWT
    $gateway->setOrderNumberPrefix('ESERVICE-');

    // Set Order Number
    // $gateway->setOrderNumber('ESERVICE-20230927000001');

    $cardData = [
        'number' => '4012010000020070', //Optional
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
        'amount' => '10.00',   //Mandatory
        "AddressMatch" => "false",   //Optional  
        "validCardType" => [
            'visa',
            'mastercard'
        ] // Optional
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
    // an error occurred, catch it an show something to the customer
    print( $e->getMessage() );
}