<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Configs;

use PHPMQ\Server\Constants\LoggerType;
use PHPMQ\Server\Constants\ServerType;
use PHPMQ\Server\Constants\StorageType;
use PHPMQ\Server\Endpoint\Types\UnixDomainSocket;
use PHPMQ\Server\Loggers\Interfaces\ConfiguresLogFileLogger;
use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Server\Servers\Types\NetworkSocket;
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

	/** @var string */
	private $configDir;

	public function __construct( string $configFile )
	{
		$this->xml       = simplexml_load_file( $configFile );
		$this->configDir = dirname( $configFile );
	}

	public function getStorageType() : string
	{
		return $this->xml->xpath( '//storage/*' )[0]->getName();
	}

	public function getSQLiteStorageConfig() : ConfiguresSQLiteStorage
	{
		$parentNode  = $this->xml->xpath( '//storage/' . StorageType::SQLITE )[0];
		$storagePath = $this->getConfigValue( $parentNode, 'storagePath' );

		return new SQLiteStorageConfig( $storagePath );
	}

	private function getConfigValue(
		\SimpleXMLElement $parentNode,
		string $configName,
		string $attributeName = 'value'
	) : string
	{
		$configNode = $parentNode->xpath( 'config[@name="' . $configName . '"]' )[0];

		return (string)$configNode->attributes()[ $attributeName ];
	}

	public function getRedisStorageConfig() : ConfiguresRedisStorage
	{
		$parentNode              = $this->xml->xpath( '//storage/' . StorageType::REDIS )[0];
		$host                    = $storagePath = $this->getConfigValue( $parentNode, 'host' );
		$port                    = $storagePath = $this->getConfigValue( $parentNode, 'port' );
		$database                = $storagePath = $this->getConfigValue( $parentNode, 'database' );
		$password                = $storagePath = $this->getConfigValue( $parentNode, 'password' );
		$prefix                  = $storagePath = $this->getConfigValue( $parentNode, 'prefix' );
		$timeout                 = $storagePath = $this->getConfigValue( $parentNode, 'timeout' );
		$backgroundSaveBehaviour = $storagePath = $this->getConfigValue( $parentNode, 'backgroundSaveBehaviour' );

		return new RedisStorageConfig(
			$host,
			(int)$port,
			(int)$database,
			(float)$timeout,
			$password ?: null,
			$prefix ?: null,
			(int)$backgroundSaveBehaviour
		);
	}

	public function getActiveLoggers() : array
	{
		$activeLoggers = [];

		foreach ( $this->xml->xpath( '//logging/*' ) as $node )
		{
			$activeLoggers[] = $node->getName();
		}

		return $activeLoggers;
	}

	public function getLogFileLoggerConfig() : ConfiguresLogFileLogger
	{
		$parentNode  = $this->xml->xpath( '//logging/' . LoggerType::LOG_FILE )[0];
		$logFilePath = $this->getConfigValue( $parentNode, 'logFilePath' );
		$logLevel    = $this->getConfigValue( $parentNode, 'logFilePath', 'loglevel' );

		$logFile = (string)realpath( $this->configDir . DIRECTORY_SEPARATOR . $logFilePath );

		return new LogFileLoggerConfig( $logFile, $logLevel );
	}

	public function getMessageQueueServerSocketAddress() : IdentifiesSocketAddress
	{
		$parentNode = $this->xml->xpath( '//servers/' . ServerType::MESSAGE_QUEUE )[0];

		return $this->getServerSocketAddress( $parentNode );
	}

	private function getServerSocketAddress( \SimpleXMLElement $parentNode ) : IdentifiesSocketAddress
	{
		$addressTypeNode = $parentNode->xpath( '*' )[0];

		if ( $addressTypeNode->getName() === 'network' )
		{
			$host = $this->getConfigValue( $addressTypeNode, 'host' );
			$port = $this->getConfigValue( $addressTypeNode, 'port' );

			return new NetworkSocket( $host, (int)$port );
		}

		$path = $this->getConfigValue( $addressTypeNode, 'path' );

		return new UnixDomainSocket( $path );
	}

	public function getMaintenanceServerSocketAddress() : IdentifiesSocketAddress
	{
		$parentNode = $this->xml->xpath( '//servers/' . ServerType::MAINTENANCE )[0];

		return $this->getServerSocketAddress( $parentNode );
	}
}
