<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Storage\Interfaces;

/**
 * Interface ConfiguresMessageQueue
 * @package hollodotme\PHPMQ\Storage\Interfaces
 */
interface ConfiguresMessageQueue
{
	public function getMessageQueuePath() : string;
}
