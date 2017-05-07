<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface DispatchesMessages
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface DispatchesMessages extends LoggerAwareInterface
{
	public function dispatchMessages( ConsumesMessages $client ) : void;
}
