<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Storage\Interfaces;

use hollodotme\PHPMQ\Interfaces\IdentifiesQueue;

/**
 * Interface ProvidesQueueStatus
 * @package hollodotme\PHPMQ\Storage\Interfaces
 */
interface ProvidesQueueStatus
{
	public function getQueueName() : IdentifiesQueue;

	public function getCountTotal() : int;

	public function getCountUndispatched() : int;

	public function getCountDispatched() : int;
}
