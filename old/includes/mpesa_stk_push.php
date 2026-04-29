<?php
class MpesaSTKPush {
    private $consumer_key;
    private $consumer_secret;
    private $passkey;
    private $shortcode;
    private $mode;
    private $access_token;
    private $base_url;

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

    private function getAccessToken() {
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response);
        return $result->access_token;
    }

    public function initiateSTKPush($phone, $amount, $account_reference, $transaction_desc) {
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
        curl_close($curl);
        
        return json_decode($curl_response);
    }
}
?> 