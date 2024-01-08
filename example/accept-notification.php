<?php
require_once '../autoload.php';

use PowerTranz\PowerTranz;

try {
    $gateway = new PowerTranz;
    $gateway->setTestMode(true);
    // Password is required to perform response signature verification
    $gateway->setPWTId('88800371');
    $gateway->setPWTPwd('3UjKgQyo5SzmUZtwTkeYAbMFzyxKB9UfnuFgOSuk8VgdpTXq0wBzMy3');

    #print_r($_POST);
    #exit();


    // Signature verification is performed implicitly once the gateway was initialized with the password.
    $response = $gateway->acceptNotification($_POST);

    if($response->isSuccessful()) {
        // authorize was successful, continue purchase the payment
        $paymentResponse = $gateway->purchase($response->getSpiToken());

        //return a JSON with response    //Aproved = true means payment successfull
        if ($paymentResponse->isSuccessful()) {
            $captureResponse = $gateway->capture($paymentResponse->getDataArray());
        }

        print_r($captureResponse->getData());

    } else {
        print_r(json_decode($_POST['Response']));
        // Transaction failed
        echo $response->getMessage();
    }
} catch (\Exception $e) {
    // an error occurred, catch it an show something to the customer
    print( $e->getMessage() );
}