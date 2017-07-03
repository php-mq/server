<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Timers\Interfaces;

/**
 * Interface TimesExecution
 * @package PHPMQ\Server\Timers\Interfaces
 */
interface TimesExecution
{
	/**
	 * @return resource
	 */
	public function getStream();

	public function getInterval() : float;

	public function getCallback() : callable;

	public function isPeriodic() : bool;
}
