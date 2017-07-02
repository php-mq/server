<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use Psr\Log\LoggerAwareTrait;

/**
 * Class AbstractStreamListener
 * @package PHPMQ\Server\StreamListeners
 */
abstract class AbstractStreamListener implements ListensForStreamActivity
{
	use LoggerAwareTrait;

	public function getListener() : \Closure
	{
		return function ( $stream, TracksStreams $loop )
		{
			$this->handleStreamActivity( $stream, $loop );
		};
	}

	abstract protected function handleStreamActivity( $stream, TracksStreams $loop ) : void;
}
