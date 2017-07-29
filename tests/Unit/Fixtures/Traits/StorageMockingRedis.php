<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Storage\Interfaces\ConfiguresRedisStorage;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Storage\RedisStorage;

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
		$config = new class() implements ConfiguresRedisStorage
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

		$this->messageQueue = new RedisStorage( $config );
		$this->messageQueue->flushAllQueues();
	}

	public function tearDownStorage() : void
	{
		$this->messageQueue->flushAllQueues();
		$this->messageQueue = null;
	}
}
