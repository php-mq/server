<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Interfaces;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface ListensForActivity
 * @package PHPMQ\Server\Endpoint\Interfaces
 */
interface ListensForActivity extends LoggerAwareInterface
{
	public function start() : void;

	public function stop() : void;

	public function getEvents() : \Generator;
}
