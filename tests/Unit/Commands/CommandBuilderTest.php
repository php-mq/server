<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Commands;

use PHPMQ\Server\Commands\CommandBuilder;
use PHPMQ\Server\Commands\Constants\Command;
use PHPMQ\Server\Commands\FlushAllQueuesCommand;
use PHPMQ\Server\Commands\FlushQueueCommand;
use PHPMQ\Server\Commands\HelpCommand;
use PHPMQ\Server\Commands\QuitCommand;
use PHPMQ\Server\Commands\QuitRefreshCommand;
use PHPMQ\Server\Commands\ShowQueueCommand;
use PHPMQ\Server\Commands\StartMonitorCommand;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPUnit\Framework\TestCase;

/**
 * Class CommandBuilderTest
 * @package PHPMQ\Server\Tests\Unit\Commands
 */
final class CommandBuilderTest extends TestCase
{
	use QueueIdentifierMocking;

	/**
	 * @param string $cmd
	 * @param string $expectedCommandName
	 *
	 * @dataProvider helpCommandProvider
	 */
	public function testCanGetHelpCommand( string $cmd, string $expectedCommandName ) : void
	{
		$builder = new CommandBuilder();

		/** @var HelpCommand $command */
		$command = $builder->buildCommand( $cmd );

		$this->assertInstanceOf( HelpCommand::class, $command );
		$this->assertSame( Command::HELP, $command->getName() );
		$this->assertSame( $expectedCommandName, $command->getCommandName() );
	}

	public function helpCommandProvider() : array
	{
		return [
			[
				'cmd'                 => 'help',
				'expectedCommandName' => '',
			],
			[
				'cmd'                 => 'help monitor',
				'expectedCommandName' => 'monitor',
			],
			[
				'cmd'                 => 'help some thing',
				'expectedCommandName' => 'some thing',
			],
			[
				'cmd'                 => 'help "show"',
				'expectedCommandName' => 'show',
			],
			[
				'cmd'                 => "help 'flush'",
				'expectedCommandName' => 'flush',
			],
		];
	}

	public function testCanGetStartMonitorCommand() : void
	{
		$builder = new CommandBuilder();

		/** @var StartMonitorCommand $command */
		$command = $builder->buildCommand( 'monitor' );

		$this->assertInstanceOf( StartMonitorCommand::class, $command );
		$this->assertSame( Command::START_MONITOR, $command->getName() );
	}

	/**
	 * @param string $cmd
	 * @param string $expectedQueueName
	 *
	 * @dataProvider showQueueCommandProvider
	 */
	public function testCanGetShowQueueCommand( string $cmd, string $expectedQueueName ) : void
	{
		$builder = new CommandBuilder();

		/** @var ShowQueueCommand $command */
		$command = $builder->buildCommand( $cmd );

		$expectedQueue = $this->getQueueName( $expectedQueueName );

		$this->assertInstanceOf( ShowQueueCommand::class, $command );
		$this->assertSame( Command::SHOW_QUEUE, $command->getName() );
		$this->assertTrue( $expectedQueue->equals( $command->getQueueName() ) );
	}

	public function showQueueCommandProvider() : array
	{
		return [
			[
				'cmd'               => 'show QueueName',
				'expectedQueueName' => 'QueueName',
			],
			[
				'cmd'               => 'show Queue Name',
				'expectedQueueName' => 'Queue Name',
			],
			[
				'cmd'               => 'show "Queue Name"',
				'expectedQueueName' => 'Queue Name',
			],
			[
				'cmd'               => "show 'Queue Name'",
				'expectedQueueName' => 'Queue Name',
			],
		];
	}

	/**
	 * @param string $cmd
	 * @param string $expectedQueueName
	 *
	 * @dataProvider flushQueueCommandProvider
	 */
	public function testCanGetFlushQueueCommand( string $cmd, string $expectedQueueName ) : void
	{
		$builder = new CommandBuilder();

		/** @var FlushQueueCommand $command */
		$command = $builder->buildCommand( $cmd );

		$expectedQueue = $this->getQueueName( $expectedQueueName );

		$this->assertInstanceOf( FlushQueueCommand::class, $command );
		$this->assertSame( Command::FLUSH_QUEUE, $command->getName() );
		$this->assertTrue( $expectedQueue->equals( $command->getQueueName() ) );
	}

	public function flushQueueCommandProvider() : array
	{
		return [
			[
				'cmd'               => 'flush QueueName',
				'expectedQueueName' => 'QueueName',
			],
			[
				'cmd'               => 'flush Queue Name',
				'expectedQueueName' => 'Queue Name',
			],
			[
				'cmd'               => 'flush "Queue Name"',
				'expectedQueueName' => 'Queue Name',
			],
			[
				'cmd'               => "flush 'Queue Name'",
				'expectedQueueName' => 'Queue Name',
			],
		];
	}

	public function testCanGetFlushAllQueuesCommand() : void
	{
		$builder = new CommandBuilder();

		/** @var FlushAllQueuesCommand $command */
		$command = $builder->buildCommand( 'flushall' );

		$this->assertInstanceOf( FlushAllQueuesCommand::class, $command );
		$this->assertSame( Command::FLUSH_ALL_QUEUES, $command->getName() );
	}

	public function testCanGetQuitCommand() : void
	{
		$builder = new CommandBuilder();

		/** @var QuitCommand $command */
		$command = $builder->buildCommand( 'quit' );

		$this->assertInstanceOf( QuitCommand::class, $command );
		$this->assertSame( Command::QUIT, $command->getName() );

		/** @var QuitCommand $command */
		$command = $builder->buildCommand( 'exit' );

		$this->assertInstanceOf( QuitCommand::class, $command );
		$this->assertSame( Command::QUIT, $command->getName() );
	}

	public function testCanGetQuitRefreshCommand() : void
	{
		$builder = new CommandBuilder();

		/** @var QuitRefreshCommand $command */
		$command = $builder->buildCommand( 'q' );

		$this->assertInstanceOf( QuitRefreshCommand::class, $command );
		$this->assertSame( Command::QUIT_REFRESH, $command->getName() );
	}

	/**
	 * @expectedException \PHPMQ\Server\Commands\Exceptions\UnknownCommandException
	 */
	public function testUnknownCommandThrowsException() : void
	{
		$builder = new CommandBuilder();
		$builder->buildCommand( 'unknown' );
	}
}
