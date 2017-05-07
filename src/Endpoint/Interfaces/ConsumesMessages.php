<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Protocol\Messages\MessageE2C;

/**
 * Interface ConsumesMessages
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface ConsumesMessages
{
	public function updateConsumptionInfo( ProvidesConsumptionInfo $consumptionInfo ) : void;

	public function getConsumptionInfo() : ProvidesConsumptionInfo;

	public function consumeMessage( MessageE2C $message ) : void;
}
