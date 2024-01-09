<?php

namespace PowerTranz\Message;

class HostedPageResponse extends AbstractResponse 
{
    public function isSuccessful()
    {
        return false;
    }
    
    /**
     * Check if Redirect is Required
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return isset($this->transactionData->RedirectData) ? true : false;
    }

    /**
     * Redirect Data
     *
     * @return string
     */
    public function redirect()
    {
        return $this->transactionData->RedirectData;
    }
}
