<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

/**
 * Interface AcceptsMessageHandlers
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface AcceptsMessageHandlers
{
	public function addMessageHandlers( HandlesMessage ...$messageHandlers ) : void;
}
