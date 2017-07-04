<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Streams;

use PHPMQ\Server\Interfaces\IdentifiesStream;
use PHPMQ\Server\Streams\StreamId;
use PHPMQ\Server\Traits\StringRepresenting;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamIdTest
 * @package PHPMQ\Server\Tests\Unit\Streams
 */
final class StreamIdTest extends TestCase
{
	public function testCanRepresentStreamIdAsString() : void
	{
		$streamId = new StreamId( 'Unit-Test' );

		$this->assertSame( 'Unit-Test', $streamId->toString() );
		$this->assertSame( 'Unit-Test', (string)$streamId );
	}

	public function testCanCheckForEquality() : void
	{
		$streamId1 = new StreamId( 'Unit-Test' );
		$streamId2 = new StreamId( 'Unit-Test' );
		$streamId3 = new StreamId( 'Test-Unit' );
		$streamId4 = new class implements IdentifiesStream
		{
			use StringRepresenting;

			public function toString() : string
			{
				return 'Unit-Test';
			}

			public function equals( IdentifiesStream $other ) : bool
			{
				return (get_class( $other ) === self::class && $this->toString() === $other->toString());
			}
		};

		$this->assertTrue( $streamId1->equals( $streamId2 ) );
		$this->assertTrue( $streamId2->equals( $streamId1 ) );
		$this->assertFalse( $streamId1->equals( $streamId3 ) );
		$this->assertFalse( $streamId3->equals( $streamId1 ) );
		$this->assertFalse( $streamId2->equals( $streamId3 ) );
		$this->assertFalse( $streamId3->equals( $streamId2 ) );
		$this->assertFalse( $streamId1->equals( $streamId4 ) );
		$this->assertFalse( $streamId1->equals( $streamId4 ) );
		$this->assertFalse( $streamId4->equals( $streamId1 ) );
	}
}
