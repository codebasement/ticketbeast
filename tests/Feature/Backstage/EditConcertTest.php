<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EditConcertTest extends TestCase
{
    use DatabaseMigrations;

    protected function validParams($overrides = [])
    {
    	return array_merge([
			'title' => 'New title',
	        'subtitle' => 'New subtitle',
	        'additional_information' => 'New additional information',
	        'date' => '2018-12-12',
	        'time' => '8:00pm',
	        'venue' => 'New venue',
	        'venue_address' => 'New venue address',
	        'city' => 'New city',
	        'state' => 'New state',
	        'zip' => '9999',
	        'ticket_price' => '120.00',
		], $overrides);
    }

    /** @test */
    function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
    	$this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function promoters_cannot_view_the_edit_form_for_their_own_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

    /** @test */
    function promoters_cannot_view_the_edit_form_for_other_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(404);
    }

	/** @test */
	function promoters_see_a_404_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
	{
	    $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/9999/edit");

        $response->assertStatus(404);
	}

	/** @test */
	function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert()
	{
	    $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $response = $this->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
	}

	/** @test */
	function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
	{
	    $response = $this->get("/backstage/concerts/9999/edit");

	    $response->assertStatus(302);
	    $response->assertRedirect('/login');
	}

	/** @test */
	function promoters_can_edit_their_own_unpublished_concerts()
	{
    	$this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
        	'user_id' => $user->id,
			'title' => 'Old title',
	        'subtitle' => 'Old subtitle',
	        'additional_information' => 'Old additional information',
	        'date' => Carbon::parse('2017-01-01 5:00pm'),
	        'venue' => 'Old venue',
	        'venue_address' => 'Old venue address',
	        'city' => 'Old city',
	        'state' => 'Old state',
	        'zip' => '00000',
	        'ticket_price' => 30000,
	    ]);
        $this->assertFalse($concert->isPublished());

		$response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
			'title' => 'New title',
	        'subtitle' => 'New subtitle',
	        'additional_information' => 'New additional information',
	        'date' => '2018-12-12',
	        'time' => '8:00pm',
	        'venue' => 'New venue',
	        'venue_address' => 'New venue address',
	        'city' => 'New city',
	        'state' => 'New state',
	        'zip' => '9999',
	        'ticket_price' => '120.00',
		]);

		$response->assertRedirect("/backstage/concerts");
		tap($concert->fresh(), function ($concert) {
			$this->assertEquals('New title', $concert->title);
			$this->assertEquals('New subtitle', $concert->subtitle);
			$this->assertEquals('New additional information', $concert->additional_information);
			$this->assertEquals(Carbon::parse('2018-12-12 8:00pm'), $concert->date);
			$this->assertEquals('New venue', $concert->venue);
			$this->assertEquals('New venue address', $concert->venue_address);
			$this->assertEquals('New city', $concert->city);
			$this->assertEquals('New state', $concert->state);
			$this->assertEquals('9999', $concert->zip);
			$this->assertEquals('12000', $concert->ticket_price);
		});
	}

	/** @test */
	function promoters_cannot_edit_other_unpublished_concerts()
	{		
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
        	'user_id' => $otherUser->id,
			'title' => 'Old title',
	        'subtitle' => 'Old subtitle',
	        'additional_information' => 'Old additional information',
	        'date' => Carbon::parse('2017-01-01 5:00pm'),
	        'venue' => 'Old venue',
	        'venue_address' => 'Old venue address',
	        'city' => 'Old city',
	        'state' => 'Old state',
	        'zip' => '00000',
	        'ticket_price' => 30000,
	    ]);
        $this->assertFalse($concert->isPublished());

		$response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
			'title' => 'New title',
	        'subtitle' => 'New subtitle',
	        'additional_information' => 'New additional information',
	        'date' => '2018-12-12',
	        'time' => '8:00pm',
	        'venue' => 'New venue',
	        'venue_address' => 'New venue address',
	        'city' => 'New city',
	        'state' => 'New state',
	        'zip' => '9999',
	        'ticket_price' => '120.00',
		]);

		$response->assertStatus(404);
		tap($concert->fresh(), function ($concert) {
			$this->assertEquals('Old title', $concert->title);
			$this->assertEquals('Old subtitle', $concert->subtitle);
			$this->assertEquals('Old additional information', $concert->additional_information);
			$this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
			$this->assertEquals('Old venue', $concert->venue);
			$this->assertEquals('Old venue address', $concert->venue_address);
			$this->assertEquals('Old city', $concert->city);
			$this->assertEquals('Old state', $concert->state);
			$this->assertEquals('00000', $concert->zip);
			$this->assertEquals('30000', $concert->ticket_price);
		});
	}

	/** @test */
	function promoters_cannot_edit_published_concerts()
	{
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create([
        	'user_id' => $user->id,
			'title' => 'Old title',
	        'subtitle' => 'Old subtitle',
	        'additional_information' => 'Old additional information',
	        'date' => Carbon::parse('2017-01-01 5:00pm'),
	        'venue' => 'Old venue',
	        'venue_address' => 'Old venue address',
	        'city' => 'Old city',
	        'state' => 'Old state',
	        'zip' => '00000',
	        'ticket_price' => 30000,
	    ]);
        $this->assertTrue($concert->isPublished());

		$response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
			'title' => 'New title',
	        'subtitle' => 'New subtitle',
	        'additional_information' => 'New additional information',
	        'date' => '2018-12-12',
	        'time' => '8:00pm',
	        'venue' => 'New venue',
	        'venue_address' => 'New venue address',
	        'city' => 'New city',
	        'state' => 'New state',
	        'zip' => '9999',
	        'ticket_price' => '120.00',
		]);

		$response->assertStatus(403);
		tap($concert->fresh(), function ($concert) {
			$this->assertEquals('Old title', $concert->title);
			$this->assertEquals('Old subtitle', $concert->subtitle);
			$this->assertEquals('Old additional information', $concert->additional_information);
			$this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
			$this->assertEquals('Old venue', $concert->venue);
			$this->assertEquals('Old venue address', $concert->venue_address);
			$this->assertEquals('Old city', $concert->city);
			$this->assertEquals('Old state', $concert->state);
			$this->assertEquals('00000', $concert->zip);
			$this->assertEquals('30000', $concert->ticket_price);
		});
	}

	/** @test */
	function guests_cannot_edit_concerts()
	{
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
        	'user_id' => $user->id,
			'title' => 'Old title',
	        'subtitle' => 'Old subtitle',
	        'additional_information' => 'Old additional information',
	        'date' => Carbon::parse('2017-01-01 5:00pm'),
	        'venue' => 'Old venue',
	        'venue_address' => 'Old venue address',
	        'city' => 'Old city',
	        'state' => 'Old state',
	        'zip' => '00000',
	        'ticket_price' => 30000,
	    ]);
        $this->assertFalse($concert->isPublished());

		$response = $this->patch("/backstage/concerts/{$concert->id}", [
			'title' => 'New title',
	        'subtitle' => 'New subtitle',
	        'additional_information' => 'New additional information',
	        'date' => '2018-12-12',
	        'time' => '8:00pm',
	        'venue' => 'New venue',
	        'venue_address' => 'New venue address',
	        'city' => 'New city',
	        'state' => 'New state',
	        'zip' => '9999',
	        'ticket_price' => '120.00',
		]);

		$response->assertRedirect('/login');
		tap($concert->fresh(), function ($concert) {
			$this->assertEquals('Old title', $concert->title);
			$this->assertEquals('Old subtitle', $concert->subtitle);
			$this->assertEquals('Old additional information', $concert->additional_information);
			$this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
			$this->assertEquals('Old venue', $concert->venue);
			$this->assertEquals('Old venue address', $concert->venue_address);
			$this->assertEquals('Old city', $concert->city);
			$this->assertEquals('Old state', $concert->state);
			$this->assertEquals('00000', $concert->zip);
			$this->assertEquals('30000', $concert->ticket_price);
		});
	}

    /** @test */
    function title_is_required()
    {
	   $user = factory(User::class)->create();
       $concert = factory(Concert::class)->create([
			'user_id' => $user->id,
			'title' => 'Old title',
	        'subtitle' => 'Old subtitle',
	        'additional_information' => 'Old additional information',
	        'date' => Carbon::parse('2017-01-01 5:00pm'),
	        'venue' => 'Old venue',
	        'venue_address' => 'Old venue address',
	        'city' => 'Old city',
	        'state' => 'Old state',
	        'zip' => '00000',
	        'ticket_price' => 30000,
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'title' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');
		tap($concert->fresh(), function ($concert) {
			$this->assertEquals('Old title', $concert->title);
			$this->assertEquals('Old subtitle', $concert->subtitle);
			$this->assertEquals('Old additional information', $concert->additional_information);
			$this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
			$this->assertEquals('Old venue', $concert->venue);
			$this->assertEquals('Old venue address', $concert->venue_address);
			$this->assertEquals('Old city', $concert->city);
			$this->assertEquals('Old state', $concert->state);
			$this->assertEquals('00000', $concert->zip);
			$this->assertEquals('30000', $concert->ticket_price);
		});
    }

    /** @test */
    function subtitle_is_optional()
    {
	   $user = factory(User::class)->create();
       $concert = factory(Concert::class)->create([
			'user_id' => $user->id,
			'title' => 'Old title',
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'subtitle' => '',
        ]));

		tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);
        	$response->assertRedirect("backstage/concerts");

        	$this->assertTrue($concert->user->is($user));

        	$this->assertNull($concert->subtitle);
		});
    }
	
	/** @test */
	function additional_information_is_optional()
	{
		$this->disableExceptionHandling();

		$user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'subtitle' => 'Old subtitle',
	    ]);
        $this->assertFalse($concert->isPublished());

		$response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
			'additional_information' => "",
		]));

		tap(Concert::first(), function ($concert) use ($response, $user) {
			$response->assertStatus(302);
			$response->assertRedirect("/backstage/concerts");

			$this->assertTrue($concert->user->is($user));

			$this->assertNull($concert->additional_information);
		});
	}


    /** @test */
    function date_is_required()
    {
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'date' => Carbon::parse('2017-01-01 5:00pm'),
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'date' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function date_must_be_a_valid_date()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'date' => Carbon::parse('2017-01-01 5:00pm'),
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'date' => 'not a date',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function time_is_required()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'date' => Carbon::parse('2017-01-01 5:00pm'),
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'time' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('time');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function time_must_be_a_valid_time()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'date' => Carbon::parse('2017-01-01 5:00pm'),
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'time' => 'not a time',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('time');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function venue_is_required()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'venue' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function venue_address_is_required()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'venue_address' => 'Old venue address',
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'venue_address' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue_address');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function city_is_required()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'city' => 'Old city',
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'city' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('city');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function state_is_required()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'state' => 'Old state',
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'state' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('state');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function zip_is_required()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'zip' => '00000',
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'zip' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('zip');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function ticket_price_is_required()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'ticket_price' => 30000,
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'ticket_price' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function ticket_price_must_be_numeric()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'ticket_price' => 30000,
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'ticket_price' => 'not a price',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(1, Concert::count());
    }

    /** @test */
    function ticket_price_must_be_at_least_5()
    {		
        $user = factory(User::class)->create();
		$concert = factory(Concert::class)->create([
			'user_id' => $user->id,
	        'ticket_price' => 30000,
	    ]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
        	'ticket_price' => '4.99',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(1, Concert::count());
    }

}