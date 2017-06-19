<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

/**
 * Interface ListensForActivity
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface ListensForActivity
{
	public function start() : void;

	public function stop() : void;

	public function getEvents() : \Generator;
}
