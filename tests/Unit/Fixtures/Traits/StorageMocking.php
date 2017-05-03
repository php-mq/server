<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits;

use hollodotme\PHPMQ\Storage\Interfaces\ConfiguresMessageQueue;
use hollodotme\PHPMQ\Storage\Interfaces\StoresMessages;
use hollodotme\PHPMQ\Storage\MessageQueueSQLite;

/**
 * Trait StorageMocking
 * @package hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits
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
