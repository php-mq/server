<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Storage;

use PHPMQ\Server\Configs\ConfigBuilder;
use PHPMQ\Server\Storage\RedisStorage;
use PHPMQ\Server\Storage\SQLiteStorage;
use PHPMQ\Server\Storage\Storage;
use PHPUnit\Framework\TestCase;

final class StorageTest extends TestCase
{
	public function testCanGetSQLiteStorageFromConfigBuilder() : void
	{
		$configBuilder = new ConfigBuilder( dirname( __DIR__ ) . '/Configs/Fixtures/sqlite.config.xml' );
		$storage       = Storage::fromConfigBuilder( $configBuilder );

		$this->assertInstanceOf( SQLiteStorage::class, $storage );
	}

	public function testCanGetRedisStorageFromConfigBuilder() : void
	{
		$configBuilder = new ConfigBuilder( dirname( __DIR__ ) . '/Configs/Fixtures/redis.config.xml' );
		$storage       = Storage::fromConfigBuilder( $configBuilder );

		$this->assertInstanceOf( RedisStorage::class, $storage );
	}

	/**
	 * @expectedException \PHPMQ\Server\Configs\Exceptions\InvalidStorageConfigException
	 */
	public function testInvalidStorageConfigThrowsException() : void
	{
		$configBuilder = new ConfigBuilder( dirname( __DIR__ ) . '/Configs/Fixtures/storage.invalid.config.xml' );
		Storage::fromConfigBuilder( $configBuilder );
	}
}
