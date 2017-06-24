<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Commands\Constants;

/**
 * Class Command
 * @package PHPMQ\Server\Commands\Constants
 */
abstract class Command
{
	public const START_MONITOR    = 'monitor';

	public const SHOW_QUEUE       = 'show';

	public const FLUSH_QUEUE      = 'flush';

	public const FLUSH_ALL_QUEUES = 'flushall';

	public const QUIT_REFRESH     = 'q';

	public const HELP             = 'help';

	public const QUIT             = 'quit';

	public const EXIT             = 'exit';
}
