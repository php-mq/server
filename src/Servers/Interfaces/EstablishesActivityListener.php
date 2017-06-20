<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Interfaces;

/**
 * Class ServerSocket
 * @package PHPMQ\Server\Endpoint\Sockets
 */
interface EstablishesActivityListener
{
	public function getName() : string;

	public function startListening() : void;

	public function endListening() : void;

	public function getNewClient() : ?ProvidesClientInfo;
}
