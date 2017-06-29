<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Constants;

/**
 * Class StorageType
 * @package PHPMQ\Server\Constants
 */
abstract class StorageType
{
	public const SQLITE = 'sqlite';

	public const REDIS  = 'redis';
}
