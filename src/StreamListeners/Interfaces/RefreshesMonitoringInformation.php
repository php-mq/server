<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners\Interfaces;

use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\IdentifiesQueue;

/**
 * Class ServerMonitor
 * @package PHPMQ\Server\Monitoring
 */
interface RefreshesMonitoringInformation
{
	public function refresh( IdentifiesQueue $queueName, TransfersData $stream ) : void;
}
