<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Validators;

use PHPMQ\Server\Validators\PHPVersionValidator;
use PHPUnit\Framework\TestCase;

final class PHPVersionValidatorTest extends TestCase
{
	public function testLowerVersionFails() : void
	{
		$validator = new PHPVersionValidator(
			'7.1.0',
			'7.0.0',
			'/usr/bin/php7.0',
			'v0.1.0-dev'
		);

		$expectedMessage = "PHPMQ v0.1.0-dev by Holger Woltersdorf and contributors.\n\n";
		$expectedMessage .= "This version of PHPMQ is supported on PHP >= 7.1.0\n";
		$expectedMessage .= "You are using PHP 7.0.0 (/usr/bin/php7.0).\n";

		$this->assertTrue( $validator->failed() );
		$this->assertEquals( [ $expectedMessage ], $validator->getMessages() );
	}

	public function testSameVersionSucceeds() : void
	{
		$validator = new PHPVersionValidator(
			'7.1.0',
			'7.1.0',
			'/usr/bin/php7.1',
			'v0.1.0-dev'
		);
		$this->assertFalse( $validator->failed() );
		$this->assertSame( [], $validator->getMessages() );
	}

	public function testGreaterVersionSucceeds() : void
	{
		$validator = new PHPVersionValidator(
			'7.1.0',
			'7.1.6',
			'/usr/bin/php7.1',
			'v0.1.0-dev'
		);
		$this->assertFalse( $validator->failed() );
		$this->assertSame( [], $validator->getMessages() );
	}
}
