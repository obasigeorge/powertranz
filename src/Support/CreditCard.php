<?php

namespace PowerTranz\Support;

class CreditCard 
{
    /**
     * Replaces all but the first and last four digits with x's in the given credit card number
     * 
     * @param int|string $cc The credit card number to mask
     *   
     * @return string The masked credit card number
     */
    public static function mask( $cc )
    {
        // replace all digits with X except for the first and last four.
        $cc = preg_replace('/(?!^.?)[0-9](?!(.){0,3}$)/', 'X', $cc);
        
        // Return the masked Credit Card #
        return $cc;
    }

    /**
     * Add dashes to a credit card number.
     *
     * @param int|string $cc The credit card number to format with dashes.
     * 
     * @return string The credit card with dashes.
     */
    public static function format( $cc )
    {
        // Clean out extra data that might be in the cc
        $cc = str_replace(array('-',' '),'',$cc);

        // Get the CC Length
        $cc_length = strlen($cc);

        // Initialize the new credit card to contian the last four digits
        $newCreditCard = substr($cc,-4);

        // Walk backwards through the credit card number and add a dash after every fourth digit
        for ($i=$cc_length-5; $i>=0; $i--)
        {
            // If on the fourth character add a dash
            if((($i+1)-$cc_length)%4 == 0){
                $newCreditCard = '-'.$newCreditCard;
            }
            // Add the current character to the new credit card
            $newCreditCard = $cc[$i].$newCreditCard;
        }

        // Return the formatted credit card number
        return $newCreditCard;
    }

    /**
     * Remove all non numeric characters from a credit card number
     * 
     * @param int|string $cc
     * 
     * @return string
     */
    public static function number( $cc )
    {
        // remove all non-numeric characters
        preg_match_all('/([0-9])/', $cc, $matches);

        // Return number 
        return implode('', $matches[1]);
    }
}