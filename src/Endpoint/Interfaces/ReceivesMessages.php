<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

/**
 * Interface ReceivesMessages
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface ReceivesMessages
{
	public function hasMessages() : bool;

	public function readMessages() : \Generator;
}
