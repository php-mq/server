<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Storage\Exceptions;

use PHPMQ\Server\Exceptions\RuntimeException;

/**
 * Class StorageException
 * @package PHPMQ\Server\Storage\Exceptions
 */
final class StorageException extends RuntimeException
{
	private const ERROR_CODE_ENQUEUE           = 100;

	private const ERROR_CODE_DEQUEUE           = 200;

	private const ERROR_CODE_MARK_DISPATCHED   = 300;

	private const ERROR_CODE_MARK_UNDISPATCHED = 400;

	private const ERROR_CODE_GET_UNDISPATCHED  = 500;

	private const ERROR_CODE_FLUSH_QUEUE       = 600;

	private const ERROR_CODE_FLUSH_ALL_QUEUES  = 700;

	private const ERROR_CODE_RESET_DISPATCHED  = 800;

	public static function fromMethodFailure( string $method, \Throwable $previous ) : self
	{
		switch ( $method )
		{
			case 'enqueue':
				return new self( 'Could not enqueue message.', self::ERROR_CODE_ENQUEUE, $previous );

			case 'dequeue':
				return new self( 'Could not dequeue message.', self::ERROR_CODE_DEQUEUE, $previous );

			case 'markAsDispatched':
				return new self( 'Could not mark message as dispatched.', self::ERROR_CODE_MARK_DISPATCHED, $previous );

			case 'markAsUndispatched':
				return new self(
					'Could not mark message as undispatched.',
					self::ERROR_CODE_MARK_UNDISPATCHED,
					$previous
				);

			case 'getUndispatched':
				return new self( 'Could not get undispatched messages.', self::ERROR_CODE_GET_UNDISPATCHED, $previous );

			case 'flushQueue':
				return new self( 'Could not flush queue.', self::ERROR_CODE_FLUSH_QUEUE, $previous );

			case 'flushAllQueues':
				return new self( 'Could not flush all queues.', self::ERROR_CODE_FLUSH_ALL_QUEUES, $previous );

			case 'resetAllDispatched':
				return new self(
					'Could not reset all dispatched messages.',
					self::ERROR_CODE_RESET_DISPATCHED,
					$previous
				);
		}

		return new self( $previous->getMessage(), $previous->getCode(), $previous );
	}
}
