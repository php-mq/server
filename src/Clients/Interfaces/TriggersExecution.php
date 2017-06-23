<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Clients\Interfaces;

/**
 * Interface TriggersExecution
 * @package PHPMQ\Server\Clients\Interfaces
 */
interface TriggersExecution
{
	public function getName(): string;
}
