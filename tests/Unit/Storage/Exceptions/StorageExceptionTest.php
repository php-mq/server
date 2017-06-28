<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Storage\Exceptions;

use PHPMQ\Server\Storage\Exceptions\StorageException;
use PHPUnit\Framework\TestCase;

final class StorageExceptionTest extends TestCase
{
	/**
	 * @param string $failedMethod
	 * @param string $expectedMessage
	 * @param int    $expectedCode
	 *
	 * @dataProvider failedMethodProvider
	 */
	public function testCanGetExceptionsFromFailedMethods(
		string $failedMethod,
		string $expectedMessage,
		int $expectedCode
	) : void
	{
		$previous  = new \Exception( 'Unit-Test', 1 );
		$exception = StorageException::fromMethodFailure( $failedMethod, $previous );

		$this->assertSame( $expectedMessage, $exception->getMessage() );
		$this->assertSame( $expectedCode, $exception->getCode() );
		$this->assertSame( $previous, $exception->getPrevious() );
	}

	public function failedMethodProvider() : array
	{
		return [
			[
				'failedMethod'    => 'enqueue',
				'expectedMessage' => 'Could not enqueue message.',
				'expectedCode'    => 100,
			],
			[
				'failedMethod'    => 'dequeue',
				'expectedMessage' => 'Could not dequeue message.',
				'expectedCode'    => 200,
			],
			[
				'failedMethod'    => 'markAsDispatched',
				'expectedMessage' => 'Could not mark message as dispatched.',
				'expectedCode'    => 300,
			],
			[
				'failedMethod'    => 'markAsUndispatched',
				'expectedMessage' => 'Could not mark message as undispatched.',
				'expectedCode'    => 400,
			],
			[
				'failedMethod'    => 'getUndispatched',
				'expectedMessage' => 'Could not get undispatched messages.',
				'expectedCode'    => 500,
			],
			[
				'failedMethod'    => 'flushQueue',
				'expectedMessage' => 'Could not flush queue.',
				'expectedCode'    => 600,
			],
			[
				'failedMethod'    => 'flushAllQueues',
				'expectedMessage' => 'Could not flush all queues.',
				'expectedCode'    => 700,
			],
			[
				'failedMethod'    => 'resetAllDispatched',
				'expectedMessage' => 'Could not reset all dispatched messages.',
				'expectedCode'    => 800,
			],
			[
				'failedMethod'    => 'unknownMethod',
				'expectedMessage' => 'Unit-Test',
				'expectedCode'    => 1,
			],
		];
	}
}
