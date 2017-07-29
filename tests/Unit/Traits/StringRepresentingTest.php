<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Traits;

use PHPMQ\Server\Interfaces\RepresentsString;
use PHPMQ\Server\Traits\StringRepresenting;
use PHPUnit\Framework\TestCase;

/**
 * Class StringRepresentingTest
 * @package PHPMQ\Server\Tests\Unit\Traits
 */
final class StringRepresentingTest extends TestCase
{
	public function testCanRepresentValueAsString() : void
	{
		$implementation = new class implements RepresentsString
		{
			use StringRepresenting;

			public function toString() : string
			{
				return 'Unit-Test';
			}
		};

		$this->assertSame( 'Unit-Test', (string)$implementation );
		$this->assertSame( 'Unit-Test', $implementation->toString() );
		$this->assertSame( '"Unit-Test"', json_encode( $implementation ) );
	}
}
