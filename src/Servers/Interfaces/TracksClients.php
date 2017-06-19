<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Interfaces;

/**
 * Interface TracksClients
 * @package PHPMQ\Server\Servers\Interfaces
 */
interface TracksClients
{
	public function add( CommunicatesWithServer $client );

	public function remove( CommunicatesWithServer $client );

	public function getActive() : iterable;

	public function getShuffled() : iterable;

	public function shutDown() : void;
}
