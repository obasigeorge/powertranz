<?php

namespace PowerTranz\Message;

class GenericResponse extends AbstractResponse {
    
    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return (intval($this->transactionData->IsoResponseCode) === 1) ? true : false;
    }
}