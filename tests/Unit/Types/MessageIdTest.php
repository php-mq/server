<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Client\Tests\Unit\Types;

use PHPMQ\Server\Types\MessageId;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageIdTest
 * @package PHPMQ\Server\Tests\Unit\Types
 */
final class MessageIdTest extends TestCase
{
	public function testCanCheckIfQueueNamesAreEqual() : void
	{
		$messageId1 = new MessageId( 'Unit-Test-ID-1' );
		$messageId2 = new MessageId( 'Unit-Test-ID-1' );
		$messageId3 = new MessageId( 'Unit-Test-ID-2' );

		$this->assertTrue( $messageId1->equals( $messageId2 ) );
		$this->assertTrue( $messageId2->equals( $messageId1 ) );
		$this->assertFalse( $messageId1->equals( $messageId3 ) );
		$this->assertFalse( $messageId2->equals( $messageId3 ) );
		$this->assertFalse( $messageId3->equals( $messageId1 ) );
		$this->assertFalse( $messageId3->equals( $messageId2 ) );
	}

	public function testCanGetValueAsString() : void
	{
		$messageId = new MessageId( 'Unit-Test-ID' );

		$this->assertSame( 'Unit-Test-ID', (string)$messageId );
		$this->assertSame( 'Unit-Test-ID', $messageId->toString() );
		$this->assertSame( '"Unit-Test-ID"', json_encode( $messageId ) );
	}
}
