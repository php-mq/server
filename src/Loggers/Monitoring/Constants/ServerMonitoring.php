<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers\Monitoring\Constants;

/**
 * Class ServerMonitoring
 * @package PHPMQ\Server\Loggers\Monitoring\Constants
 */
abstract class ServerMonitoring
{
	public const CLIENT_CONNECTED     = 'clientConnected';

	public const CLIENT_DISCONNECTED  = 'clientDisconnected';

	public const MESSAGE_ENQUEUED     = 'messageEnqueued';

	public const MESSAGE_DEQUEUED     = 'messageDequeued';

	public const MESSAGE_DISPATCHED   = 'messageDispatched';

	public const MESSAGE_UNDISPATCHED = 'messageUndispatched';

	public const QUEUE_FLUSHED        = 'queueFlushed';

	public const ALL_QUEUES_FLUSHED   = 'allQueuesFlushed';
}
