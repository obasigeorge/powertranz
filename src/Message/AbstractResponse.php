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
        return $this->transactionData->ResponseCode ?? '';
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
     * @return boolean
     */
    public function isSuccessful()
    {
        return false;
    }
}