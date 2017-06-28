<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Configs;

use PHPMQ\Server\Configs\ConfigBuilder;
use PHPMQ\Server\Loggers\Interfaces\ConfiguresLogFileLogger;
use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Server\Storage\Interfaces\ConfiguresRedisStorage;
use PHPMQ\Server\Storage\Interfaces\ConfiguresSQLiteStorage;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigBuilderTest
 * @package PHPMQ\Server\Tests\Unit\Configs
 */
final class ConfigBuilderTest extends TestCase
{
	public function testCanGetSQLiteStorageConfig() : void
	{
		$file          = __DIR__ . '/Fixtures/sqlite.config.xml';
		$configBuilder = new ConfigBuilder( $file );

		$sqliteStorageConfig = $configBuilder->getSQLiteStorageConfig();

		$this->assertSame( 'sqlite', $configBuilder->getStorageType() );
		$this->assertInstanceOf( ConfiguresSQLiteStorage::class, $sqliteStorageConfig );
		$this->assertSame( ':memory:', $sqliteStorageConfig->getStoragePath() );
	}

	public function testCanGetRedisStorageConfig() : void
	{
		$file          = __DIR__ . '/Fixtures/redis.config.xml';
		$configBuilder = new ConfigBuilder( $file );

		$redisStorageConfig = $configBuilder->getRedisStorageConfig();

		$this->assertSame( 'redis', $configBuilder->getStorageType() );
		$this->assertInstanceOf( ConfiguresRedisStorage::class, $redisStorageConfig );
		$this->assertSame( '127.0.0.1', $redisStorageConfig->getHost() );
		$this->assertSame( 6379, $redisStorageConfig->getPort() );
		$this->assertSame( 1, $redisStorageConfig->getDatabase() );
		$this->assertSame( 2.0, $redisStorageConfig->getTimeout() );
		$this->assertNull( $redisStorageConfig->getPassword() );
		$this->assertSame( 'PHPMQ:', $redisStorageConfig->getPrefix() );
		$this->assertSame( 0, $redisStorageConfig->getBackgroundSaveBehaviour() );
	}

	public function testCanGetLoggerConfigs() : void
	{
		$file          = __DIR__ . '/Fixtures/loggers.config.xml';
		$configBuilder = new ConfigBuilder( $file );

		$expectedActiveLoggers = [ 'logfile', 'output' ];

		$logFileLoggerConfig = $configBuilder->getLogFileLoggerConfig();

		$this->assertEquals( $expectedActiveLoggers, $configBuilder->getActiveLoggers() );
		$this->assertInstanceOf( ConfiguresLogFileLogger::class, $logFileLoggerConfig );
		$this->assertSame( __DIR__ . '/Fixtures/../build/logs/phpmq.log', $logFileLoggerConfig->getLogFilePath() );
		$this->assertSame( 'debug', $logFileLoggerConfig->getLogLevel() );
	}

	public function testCanGetMessageQueueServerNetworkSocketAddress() : void
	{
		$file          = __DIR__ . '/Fixtures/messagequeue.network.config.xml';
		$configBuilder = new ConfigBuilder( $file );

		$address = $configBuilder->getMessageQueueServerSocketAddress();

		$this->assertInstanceOf( IdentifiesSocketAddress::class, $address );
		$this->assertSame( 'tcp://127.0.0.1:9100', $address->getSocketAddress() );
	}

	public function testCanGetMessageQueueServerUnixSocketAddress() : void
	{
		$file          = __DIR__ . '/Fixtures/messagequeue.unix.config.xml';
		$configBuilder = new ConfigBuilder( $file );

		$address = $configBuilder->getMessageQueueServerSocketAddress();

		$this->assertInstanceOf( IdentifiesSocketAddress::class, $address );
		$this->assertSame( 'unix:///tmp/phpmq.sock', $address->getSocketAddress() );
	}

	public function testCanGetMaintenanceServerNetworkSocketAddress() : void
	{
		$file          = __DIR__ . '/Fixtures/maintenance.network.config.xml';
		$configBuilder = new ConfigBuilder( $file );

		$address = $configBuilder->getMaintenanceServerSocketAddress();

		$this->assertInstanceOf( IdentifiesSocketAddress::class, $address );
		$this->assertSame( 'tcp://127.0.0.1:9101', $address->getSocketAddress() );
	}

	public function testCanGetMaintenanceServerUnixSocketAddress() : void
	{
		$file          = __DIR__ . '/Fixtures/maintenance.unix.config.xml';
		$configBuilder = new ConfigBuilder( $file );

		$address = $configBuilder->getMaintenanceServerSocketAddress();

		$this->assertInstanceOf( IdentifiesSocketAddress::class, $address );
		$this->assertSame( 'unix:///tmp/phpmq.maintenance.sock', $address->getSocketAddress() );
	}
}
