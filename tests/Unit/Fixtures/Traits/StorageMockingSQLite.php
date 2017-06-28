<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Storage\Interfaces\ConfiguresSQLiteStorage;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Storage\SQLiteStorage;

/**
 * Trait StorageMockingSQLite
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Fixtures\Traits
 */
trait StorageMockingSQLite
{
	/** @var StoresMessages */
	private $messageQueue;

	public function setUpStorage() : void
	{
		$config = new class() implements ConfiguresSQLiteStorage
		{
			public function getStoragePath() : string
			{
				return dirname( __DIR__, 4 ) . '/build/storage/test.sqlite3';
			}
		};

		$this->messageQueue = new SQLiteStorage( $config );
		$this->messageQueue->flushAllQueues();
	}

	public function tearDownStorage() : void
	{
		$this->messageQueue->flushAllQueues();
		$this->messageQueue = null;
	}
}
