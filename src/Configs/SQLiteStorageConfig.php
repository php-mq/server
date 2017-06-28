<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Configs;

use PHPMQ\Server\Storage\Interfaces\ConfiguresSQLiteStorage;

/**
 * Class SQLiteStorageConfig
 * @package PHPMQ\Server\Configs
 */
final class SQLiteStorageConfig implements ConfiguresSQLiteStorage
{
	/** @var string */
	private $storagePath;

	public function __construct( string $storagePath )
	{
		$this->storagePath = $storagePath;
	}

	public function getStoragePath() : string
	{
		return $this->storagePath;
	}
}
