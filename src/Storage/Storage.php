<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Storage;

use PHPMQ\Server\Configs\ConfigBuilder;
use PHPMQ\Server\Configs\Exceptions\InvalidStorageConfigException;
use PHPMQ\Server\Constants\StorageType;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;

/**
 * Class Storage
 * @package PHPMQ\Server\Storage
 */
abstract class Storage
{
	public static function fromConfigBuilder( ConfigBuilder $configBuilder ) : StoresMessages
	{
		switch ( $configBuilder->getStorageType() )
		{
			case StorageType::SQLITE:
			{
				$storageConfig = $configBuilder->getSQLiteStorageConfig();

				return new SQLiteStorage( $storageConfig );
			}

			case StorageType::REDIS:
			{
				$storageConfig = $configBuilder->getRedisStorageConfig();

				return new RedisStorage( $storageConfig );
			}
			default:
				throw new InvalidStorageConfigException( 'Storage type unknown: ' . $configBuilder->getStorageType() );
		}
	}
}
