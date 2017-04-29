<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

/**
 * Interface ListensToClients
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface ListensToClients
{
	public function startListening() : void;
}
