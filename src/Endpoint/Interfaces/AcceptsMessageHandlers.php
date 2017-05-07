<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

/**
 * Interface AcceptsMessageHandlers
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface AcceptsMessageHandlers
{
	public function addMessageHandlers( HandlesMessage ...$messageHandlers ) : void;
}
