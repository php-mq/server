<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Storage\Interfaces\ConfiguresMessageQueueSQLite;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Storage\MessageQueueSQLite;
use Psr\Log\NullLogger;

/**
 * Trait StorageMockingSQLite
 * @package PHPMQ\Server\Tests\Unit\Fixtures\Traits
 */
trait StorageMockingSQLite
{
	/** @var StoresMessages */
	private $messageQueue;

	public function setUpStorage() : void
	{
		$config = new class() implements ConfiguresMessageQueueSQLite
		{
			public function getMessageQueuePath() : string
			{
				return ':memory:';
			}
		};

		$this->messageQueue = new MessageQueueSQLite( $config );
		$this->messageQueue->setLogger( new NullLogger() );
		$this->messageQueue->flushAllQueues();
	}

	public function tearDownStorage() : void
	{
		$this->messageQueue->flushAllQueues();
		$this->messageQueue = null;
	}
}
