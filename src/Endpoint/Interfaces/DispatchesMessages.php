<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface DispatchesMessages
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface DispatchesMessages extends LoggerAwareInterface
{
	public function dispatchMessages( ConsumesMessages $client ) : void;
}
