<?php

namespace App\Http\Controllers;

use App\Concert;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;

class ConcertOrdersController extends Controller
{
	private $paymentGateway;

	public function __construct(PaymentGateway $paymentGateway)
	{
		$this->paymentGateway = $paymentGateway;
	}
    protected function store($concertId)
    {
    	$this->validate(request(), [
    		'email' => 'required',
    	]);
    	
    	$concert = Concert::find($concertId);

    	// Charging the customer
    	$this->paymentGateway->charge(
    		request('ticket_quantity') * $concert->ticket_price, 
    		request('payment_token')
    	);

    	// Creating the order
    	$order = $concert->orderTickets(request('email'), request('ticket_quantity'));

    	return response()->json([], 201);
    }
}
