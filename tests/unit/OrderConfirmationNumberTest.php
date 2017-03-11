<?php

use App\OrderConfirmationNumber;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderConfirmationNumberTest extends TestCase
{
	// ABCDEFGHJKLMNPQRSTUVWXYZ
	// 23456789
	// Max possible combinations given the above 32 characters
	// 32^(string length)
	// So if we choose string length of 16, that gives us 1.2x10^24
	// Must be 16 characters long
	//
    // Can only contain uppercase letters and numbers
	//
    // Cannot contain ambiguous characters (I, 1, O, 0)
	//
	// Must be unique

	/** @test */
	function confirmation_numbers_must_be_16_characters_long()
	{
	    $confirmationNumber = (new OrderConfirmationNumber)->generate();

	    $this->assertEquals(16, strlen($confirmationNumber));
	}

	/** @test */
	function confirmation_numbers_can_only_contain_uppercase_letters_and_numbers()
	{
	    $confirmationNumber = (new OrderConfirmationNumber)->generate();

	    $this->assertRegExp('/^[A-Z0-9]+$/', $confirmationNumber);
	}

	/** @test */
	function confirmation_numbers_cannot_contain_ambiguous_characters()
	{
	    $confirmationNumber = (new OrderConfirmationNumber)->generate();

	    $this->assertFalse(strpos($confirmationNumber, 'I'));
	    $this->assertFalse(strpos($confirmationNumber, '1'));
	    $this->assertFalse(strpos($confirmationNumber, '0'));
	    $this->assertFalse(strpos($confirmationNumber, 'O'));
	}

	/** @test */
	function confirmation_numbers_must_be_unique()
	{
	    $confirmationNumbers = collect(range(1, 500))->map(function () {
	    	return (new OrderConfirmationNumber)->generate();
	    });
	    
	    $this->assertCount(500, $confirmationNumbers->unique());
	}
}