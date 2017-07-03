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
	public function addStream( TransfersData $stream, ListensForStreamActivity $listener ) : void;

	public function removeAllStreams() : void;

	public function removeStream( TransfersData $stream ) : void;

	public function start() : void;

	public function stop() : void;
}
