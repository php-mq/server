<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage\Interfaces;

/**
 * Interface ConfiguresMessageQueue
 * @package PHPMQ\Server\Storage\Interfaces
 */
interface ConfiguresMessageQueue
{
	public function getMessageQueuePath() : string;
}
