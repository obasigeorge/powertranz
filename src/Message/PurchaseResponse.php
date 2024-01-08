<?php

namespace PowerTranz\Message;

class PurchaseResponse extends AbstractResponse {
    
    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return (boolval($this->transactionData->Approved) == true && intval($this->transactionData->IsoResponseCode) === 0) ? true : false;
    }

    public function getDataArray($amount = NULL)
    {
        $data = [
            'transactionNumber' => $this->transactionData->TransactionIdentifier,
            'amount' => $amount ?? $this->transactionData->TotalAmount,
        ];

        return $data;
    }
}