<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Servers\Interfaces;

/**
 * Interface ProvidesClientInfo
 * @package PHPMQ\Server\Servers\Interfaces
 */
interface ProvidesClientInfo
{
	public function getName() : string;

	public function getSocket();
}
