<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage\Interfaces;

use PHPMQ\Server\Interfaces\IdentifiesQueue;

/**
 * Interface ProvidesQueueStatus
 * @package PHPMQ\Server\Storage\Interfaces
 */
interface ProvidesQueueStatus
{
	public function getQueueName() : IdentifiesQueue;

	public function getCountTotal() : int;

	public function getCountUndispatched() : int;

	public function getCountDispatched() : int;
}
