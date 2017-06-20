<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers;

/**
 * Class AdminServer
 * @package PHPMQ\Server\Servers
 */
final class AdminServer extends AbstractServer
{
	public function getEvents() : \Generator
	{
		yield null;
	}
}
