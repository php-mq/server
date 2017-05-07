<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

/**
 * Interface ListensToClients
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface ListensToClients
{
	public function startListening() : void;

	public function endListening() : void;
}
