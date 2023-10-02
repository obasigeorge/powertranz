<?php
require_once '../autoload.php';

use PowerTranz\PowerTranz;

$gateway = new PowerTranz;
// Password is required to perform response signature verification
$gateway->setPWTId('88800371');
$gateway->setPWTPwd('3UjKgQyo5SzmUZtwTkeYAbMFzyxKB9UfnuFgOSuk8VgdpTXq0wBzMy3');
    
// Signature verification is performed implicitly once the gateway was initialized with the password.
$response = $gateway->acceptNotification($_POST);

if($response->isSuccessful())
{       
    // authorize was succussful, continue purchase the payment    
     $paymentResponse = $gateway->purchase($response->getSpiToken());
    
    //return a JSON with response    //Aproved = true means payment successfull 
    print_r($paymentResponse->getData());
    
}
else 
{
    print_r( json_decode($_POST['Response']) );
    // Transaction failed
    // echo $response->getMessage();
}
