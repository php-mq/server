<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Storage\Interfaces\ConfiguresMessageQueueRedis;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Storage\MessageQueueRedis;
use PHPMQ\Server\Storage\MessageQueueSQLite;

/**
 * Trait StorageMockingRedis
 * @package PHPMQ\Server\Tests\Unit\Fixtures\Traits
 */
trait StorageMockingRedis
{
	/** @var StoresMessages */
	private $messageQueue;

	public function setUpStorage() : void
	{
		$config = new class() implements ConfiguresMessageQueueRedis
		{
			public function getHost() : string
			{
				return '127.0.0.1';
			}

			public function getPort() : int
			{
				return 6379;
			}

			public function getDatabase() : int
			{
				return 0;
			}

			public function getTimeout() : float
			{
				return 2.0;
			}

			public function getPassword() : ?string
			{
				return null;
			}

			public function getPrefix() : ?string
			{
				return 'UnitTest:';
			}

			public function getBackgroundSaveBehaviour() : int
			{
				return 0;
			}
		};

		$this->messageQueue = new MessageQueueRedis( $config );
		$this->messageQueue->flushAllQueues();
	}

	public function tearDownStorage() : void
	{
		$this->messageQueue->flushAllQueues();
		$this->messageQueue = null;
	}
}
