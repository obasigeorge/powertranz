<?php

namespace PowerTranz\Interfaces;

interface PowerTranzInterface {
    public function authorize($transactionData);
    public function authorizeWithToken($transactionData);
    public function authorizeWithSentryToken($transactionData);
    public function getHostedPage($transactionData, $pageSet, $pageName);
    public function acceptNotification($data);
    public function purchase($spitoken);
    public function tokenize($transactionData);
    public function void($transactionNumber);
    public function capture($transactionData);
    public function refund($transactionData);
}