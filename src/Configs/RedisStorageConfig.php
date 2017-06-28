<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Configs;

use PHPMQ\Server\Storage\Interfaces\ConfiguresRedisStorage;

/**
 * Class RedisStorageConfig
 * @package PHPMQ\Server\Configs
 */
final class RedisStorageConfig implements ConfiguresRedisStorage
{
	/** @var string */
	private $host;

	/** @var int */
	private $port;

	/** @var int */
	private $database;

	/** @var float */
	private $timeout;

	/** @var null|string */
	private $password;

	/** @var null|string */
	private $prefix;

	/** @var int */
	private $backgroundSaveBehaviour;

	public function __construct(
		string $host,
		int $port,
		int $database,
		float $timeout,
		?string $password,
		?string $prefix,
		int $backgroundSaveBehaviour
	)
	{
		$this->host                    = $host;
		$this->port                    = $port;
		$this->database                = $database;
		$this->timeout                 = $timeout;
		$this->password                = $password;
		$this->prefix                  = $prefix;
		$this->backgroundSaveBehaviour = $backgroundSaveBehaviour;
	}

	public function getHost() : string
	{
		return $this->host;
	}

	public function getPort() : int
	{
		return $this->port;
	}

	public function getDatabase() : int
	{
		return $this->database;
	}

	public function getTimeout() : float
	{
		return $this->timeout;
	}

	public function getPassword() : ?string
	{
		return $this->password;
	}

	public function getPrefix() : ?string
	{
		return $this->prefix;
	}

	public function getBackgroundSaveBehaviour() : int
	{
		return $this->backgroundSaveBehaviour;
	}
}
