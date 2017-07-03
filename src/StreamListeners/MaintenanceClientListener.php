<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MaintenanceClientListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MaintenanceClientListener implements ListensForStreamActivity
{
	use LoggerAwareTrait;

	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
	{
		// TODO: Implement handleStreamActivity() method.
	}
}
