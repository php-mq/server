<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage\Interfaces;

/**
 * Interface ConfiguresMessageQueueSQLite
 * @package PHPMQ\Server\Storage\Interfaces
 */
interface ConfiguresMessageQueueSQLite
{
	public function getMessageQueuePath() : string;
}
