<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Validators;

use PHPMQ\Server\Validators\CompositeValidator;
use PHPMQ\Server\Validators\Interfaces\ValidatesEnvironment;
use PHPUnit\Framework\TestCase;

final class CompositeValidatorTest extends TestCase
{
	public function testIsFailedOnFirstValidatorThatFails() : void
	{
		$compositeValidator = new CompositeValidator();
		$compositeValidator->addValidators(
			$this->getValidator( false, [] ),
			$this->getValidator( true, [ '1st Failure' ] ),
			$this->getValidator( true, [ '2nd Failure' ] )
		);

		$this->assertTrue( $compositeValidator->failed() );
		$this->assertEquals( [ '1st Failure' ], $compositeValidator->getMessages() );
	}

	private function getValidator( bool $fails, array $messages ) : ValidatesEnvironment
	{
		return new class($fails, $messages) implements ValidatesEnvironment
		{
			private $failed;

			private $messages;

			public function __construct( bool $failed, array $messages )
			{
				$this->failed   = $failed;
				$this->messages = $messages;
			}

			public function failed() : bool
			{
				return $this->failed;
			}

			public function getMessages() : array
			{
				return $this->messages;
			}
		};
	}

	public function testSucceedsIfAllValidatorsPassed() : void
	{
		$compositeValidator = new CompositeValidator();
		$compositeValidator->addValidators(
			$this->getValidator( false, [] ),
			$this->getValidator( false, [] ),
			$this->getValidator( false, [] )
		);

		$this->assertFalse( $compositeValidator->failed() );
		$this->assertEquals( [], $compositeValidator->getMessages() );
	}
}
