<?php

namespace PowerTranz\Message;

class ThreeDSResponse extends AbstractResponse {

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        if (in_array($this->transactionData->IsoResponseCode, ['3D0', '3D1']))
        {
            if (in_array($this->transactionData->RiskManagement->ThreeDSecure->Eci, ['01','02','05','06'])) {
                return isset($this->transactionData->SpiToken) ? true : false;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}