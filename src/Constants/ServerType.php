<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Constants;

/**
 * Class ServerType
 * @package PHPMQ\Server\Constants
 */
abstract class ServerType
{
	public const MESSAGE_QUEUE = 'messagequeue';

	public const MAINTENANCE   = 'maintenance';
}
