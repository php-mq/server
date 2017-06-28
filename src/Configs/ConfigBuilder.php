<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Configs;

use PHPMQ\Server\Loggers\Interfaces\ConfiguresLogFileLogger;
use PHPMQ\Server\Servers\Interfaces\EstablishesActivityListener;
use PHPMQ\Server\Storage\Interfaces\ConfiguresRedisStorage;
use PHPMQ\Server\Storage\Interfaces\ConfiguresSQLiteStorage;

/**
 * Class ConfigBuilder
 * @package PHPMQ\Server\Configs
 */
final class ConfigBuilder
{
	/** @var \SimpleXMLElement */
	private $xml;

	public function __construct( string $configFile )
	{
		$this->xml = simplexml_load_file( $configFile );
	}

	public function getStorageType() : string
	{
	}

	public function getSQLiteStorageConfig() : ConfiguresSQLiteStorage
	{
	}

	public function getRedisStorageConfig() : ConfiguresRedisStorage
	{
	}

	public function getActiveLoggers() : array
	{
	}

	public function getLogFileLoggerConfig() : ConfiguresLogFileLogger
	{
	}

	public function getMessageQueueServerSocket() : EstablishesActivityListener
	{
	}

	public function getMaintenanceServerSocket() : EstablishesActivityListener
	{
	}
}
