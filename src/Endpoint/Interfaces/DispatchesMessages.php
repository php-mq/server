<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Endpoint\Interfaces;

/**
 * Interface DispatchesMessages
 * @package hollodotme\PHPMQ\Endpoint\Interfaces
 */
interface DispatchesMessages
{
	public function dispatchMessages( ConsumesMessages $client ) : void;
}
