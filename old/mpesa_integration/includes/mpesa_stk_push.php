<?php
/**
 * M-Pesa STK Push Integration
 * This class handles M-Pesa STK Push payment processing
 */

class MpesaSTKPush {
    private $consumer_key;
    private $consumer_secret;
    private $passkey;
    private $shortcode;
    private $mode;
    private $access_token;
    private $base_url;

    /**
     * Constructor
     * @param string $consumer_key M-Pesa Consumer Key
     * @param string $consumer_secret M-Pesa Consumer Secret
     * @param string $passkey M-Pesa Passkey
     * @param string $shortcode Business Shortcode
     * @param string $mode Mode (sandbox/live)
     */
    public function __construct($consumer_key, $consumer_secret, $passkey, $shortcode, $mode = 'sandbox') {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->passkey = $passkey;
        $this->shortcode = $shortcode;
        $this->mode = $mode;
        $this->base_url = $mode === 'sandbox' ? 
            'https://sandbox.safaricom.co.ke' : 
            'https://api.safaricom.co.ke';
        $this->access_token = $this->getAccessToken();
    }

    /**
     * Get M-Pesa Access Token
     * @return string Access token
     */
    private function getAccessToken() {
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new Exception('Failed to get access token. HTTP Code: ' . $http_code);
        }
        
        $result = json_decode($response);
        if (!isset($result->access_token)) {
            throw new Exception('Invalid access token response');
        }
        
        return $result->access_token;
    }

    /**
     * Initiate STK Push
     * @param string $phone Customer phone number
     * @param float $amount Payment amount
     * @param string $account_reference Account reference
     * @param string $transaction_desc Transaction description
     * @return object Response from M-Pesa
     */
    public function initiateSTKPush($phone, $amount, $account_reference, $transaction_desc) {
        // Validate phone number format
        if (!preg_match('/^254[0-9]{9}$/', $phone)) {
            throw new Exception('Invalid phone number format. Use format: 254XXXXXXXXX');
        }

        // Format amount to 2 decimal places
        $amount = number_format($amount, 2, '.', '');

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->base_url . '/mpesa/stkpush/v1/processrequest');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        ]);
        
        $curl_post_data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => 'https://yourdomain.com/mpesa_callback.php',
            'AccountReference' => $account_reference,
            'TransactionDesc' => $transaction_desc
        ];
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
        
        $curl_response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($http_code !== 200) {
            throw new Exception('Failed to initiate STK Push. HTTP Code: ' . $http_code);
        }
        
        $response = json_decode($curl_response);
        if (!isset($response->ResponseCode)) {
            throw new Exception('Invalid response from M-Pesa');
        }
        
        return $response;
    }
}
?> 