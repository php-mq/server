<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Types;

use PHPMQ\Server\Monitoring\Types\QueueInfo;
use PHPUnit\Framework\TestCase;

final class QueueInfoTest extends TestCase
{
	public function testCanGetQueueName(): void
	{
		$queueInfo = new QueueInfo( 'Unit-Test-Queue', [] );

		$this->assertSame( 'Unit-Test-Queue', $queueInfo->getQueueName() );
	}

	public function testCanGetQueueSize(): void
	{
		$messageInfos = [
			[ 'size' => 100 ],
			[ 'size' => 200 ],
			[ 'size' => 300 ],
		];
		$queueInfo    = new QueueInfo( 'Unit-Test-Queue', $messageInfos );

		$this->assertSame( 600, $queueInfo->getSize() );
	}

	public function testCanGetMessageCount(): void
	{
		$messageInfos = [
			[ 'size' => 100 ],
			[ 'size' => 200 ],
			[ 'size' => 300 ],
		];
		$queueInfo    = new QueueInfo( 'Unit-Test-Queue', $messageInfos );

		$this->assertSame( 3, $queueInfo->getMessageCount() );
	}

	public function testCanGetMessageInfos(): void
	{
		$messageInfos = [
			[ 'size' => 100 ],
			[ 'size' => 200 ],
			[ 'size' => 300 ],
		];
		$queueInfo    = new QueueInfo( 'Unit-Test-Queue', $messageInfos );

		$this->assertSame( $messageInfos, $queueInfo->getMessageInfos() );
	}
}
