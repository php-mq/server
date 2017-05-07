<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Storage\Interfaces\ConfiguresMessageQueue;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Storage\MessageQueueSQLite;

/**
 * Trait StorageMocking
 * @package PHPMQ\Server\Tests\Unit\Fixtures\Traits
 */
trait StorageMocking
{
	/** @var StoresMessages */
	private $messageQueue;

	public function setUpStorage() : void
	{
		$config = new class() implements ConfiguresMessageQueue
		{
			public function getMessageQueuePath() : string
			{
				return ':memory:';
			}
		};

		$this->messageQueue = new MessageQueueSQLite( $config );
		$this->messageQueue->flushAllQueues();
	}

	public function tearDownStorage() : void
	{
		$this->messageQueue->flushAllQueues();
		$this->messageQueue = null;
	}
}
