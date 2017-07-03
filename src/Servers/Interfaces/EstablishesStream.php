<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Interfaces;

use PHPMQ\Server\Endpoint\Interfaces\TransfersData;

/**
 * Class ServerSocket
 * @package PHPMQ\Server\Endpoint\Sockets
 */
interface EstablishesStream
{
	public function getStream() : TransfersData;
}
