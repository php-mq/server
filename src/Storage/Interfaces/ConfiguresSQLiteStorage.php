<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage\Interfaces;

/**
 * Interface ConfiguresSQLiteStorage
 * @package PHPMQ\Server\Storage\Interfaces
 */
interface ConfiguresSQLiteStorage
{
	public function getStoragePath() : string;
}
