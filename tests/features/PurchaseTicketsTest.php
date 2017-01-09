<?php

use App\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PurchaseTicketsTest extends TestCase
{
	use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);  // Tell the container to bind to the fake gateway
    }
    /** @test */
    function customer_can_purchase_concert_tickets()
    {
        // Arrange - create a concert
    	$concert = factory(Concert::class)->create(['ticket_price' => 3250]);

        // Act - Purchase concert tickets
    	$this->json('POST', "/concerts/{$concert->id}/orders", [
    		'email' => 'testJohn@example.com',
    		'ticket_quantity' => 3,
    		'payment_token' => $this->paymentGateway->getValidTestToken(),
    	]);

        // Assert
        $this->assertResponseStatus(201);   // http response for created

        //	- Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        //	- Make sure an order exists for this customer
        $order = $concert->orders()->where('email', 'testJohn@example.com')->first();
 		$this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);
        $this->assertArrayHasKey('email', $this->decodeResponseJson());
    }
}