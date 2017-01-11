<?php

namespace App\Http\Controllers;

use App\Concert;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;

class ConcertOrdersController extends Controller
{
	private $paymentGateway;

	public function __construct(PaymentGateway $paymentGateway)
	{
		$this->paymentGateway = $paymentGateway;
	}

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

    	$this->validate(request(), [
    		'email' => ['required', 'email'],
    		'ticket_quantity' => ['required', 'integer', 'min:1'],
    		'payment_token' => ['required'],
    	]);

        try {

            // Creating the order
            $order = $concert->orderTickets(request('email'), request('ticket_quantity'));

            // Charging the customer
            $this->paymentGateway->charge(
                request('ticket_quantity') * $concert->ticket_price, 
                request('payment_token')
            );

            return response()->json([
                'id' => 5,
                'created_at' => '2015-01-01 12:12:50',
                'email' => 'john.feature@example.com',
                'ticket_quantity' => 3,
                'amount' => 9750,
            ], 201);

        } catch (PaymentFailedException $e) {
            $order->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
