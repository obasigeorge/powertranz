<?php

namespace PowerTranz\Message;

abstract class AbstractResponse {

    public Object $transactionData;

    /**
     * Power Tranz Response Constructor
     * 
     * @param array $data
     * 
     * @return AbstractResponse
     */
    public function __construct($data)
    {
        $this->transactionData = $data;
    }

    /**
     * Get Response Code
     * 
     * @return string
     */
    public function getResponseCode()
    {
        return $this->transactionData->RiskManagement->ThreeDSecure->ResponseCode ?? '';
    }

    /**
     * Get ISO Response Code
     * 
     * @return string
     */
    public function getIsoResponseCode()
    {
        return $this->transactionData->IsoResponseCode ?? '';
    }

    /**
     * Get Response Message
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->transactionData->ResponseMessage ?? '';
    }

    /**
     * Get Error Message
     * 
     * @return array
     */
    public function getErrorMessages()
    {
        $errors = [];
        if (isset($this->transactionData->Errors))
        {
            foreach ($this->transactionData->Errors as $error)
            {
                $errors[] = ['code' => $error->Code, 'message' => $error->Message];
            }
        }

        return $errors;
    }

    /**
     * Get Entire Transaction Response
     * 
     * @return array
     */
    public function getData()
    {
        return json_encode($this->transactionData);
    }

    /**
     * Get Transaction Number
     * 
     * @return string
     */
    public function getTransactionNumber()
    {
        return $this->transactionData->TransactionIdentifier ?? '';
    }

    /**
     * Get Order Number
     * 
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->transactionData->OrderIdentifier ?? '';
    }

    /**
     * Get SPI Token
     * 
     * @return string
     */
    public function getSpiToken()
    {
        return $this->transactionData->SpiToken ?? '';
    }

    /**
     * Get Authorization Code
     * 
     * @return string
     */
    public function getAuthorizationCode()
    {
        return $this->transactionData->AuthorizationCode ?? '';
    }

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return false;
    }
}