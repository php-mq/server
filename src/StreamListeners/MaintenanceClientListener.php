<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;

/**
 * Class MaintenanceClientListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MaintenanceClientListener extends AbstractStreamListener
{
	protected function handleStreamActivity( $stream, TracksStreams $loop ) : void
	{
		// TODO: Implement handleStreamActivity() method.
	}
}
