<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

/**
 * Interface TracksStreams
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface TracksStreams
{
	public function addReadStream( TransfersData $stream, ListensForStreamActivity $listener ) : void;

	public function addWriteStream( TransfersData $stream, ListensForStreamActivity $listener ) : void;

	public function removeAllStreams() : void;

	public function removeStream( TransfersData $stream ) : void;

	public function removeReadStream( TransfersData $stream ) : void;

	public function removeWriteStream( TransfersData $stream ) : void;

	public function start() : void;

	public function stop() : void;

	public function shutdown() : void;
}
