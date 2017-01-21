<?php

namespace App\Billing;

use Stripe\Charge;

class StripePaymentGateway implements PaymentGateway
{
	private $apiKey;

	public function __construct($apiKey)
	{
		$this->apiKey = $apiKey;
	}

    public function charge($amount, $token)
    {
    	Charge::create([
    		'amount' => $amount,
    		'source' => $token,
    		'currency' => 'aud',
    	], ['api_key' => $this->apiKey]);
    }
}
