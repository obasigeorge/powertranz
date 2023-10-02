<?php

namespace PowerTranz\Message;

class ThreeDSResponse extends AbstractResponse {

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return (in_array($this->transactionData->IsoResponseCode, ['3D0', '3D1'])) ? true : false;
    }
}