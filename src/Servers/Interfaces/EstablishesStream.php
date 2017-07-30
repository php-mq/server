<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Interfaces;

use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class ServerSocket
 * @package PHPMQ\Server\Servers\Interfaces
 */
interface EstablishesStream
{
	public function getStream() : TransfersData;
}
