<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Validators;

use PHPMQ\Server\Validators\ConfigFileValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigFileValidatorTest
 * @package PHPMQ\Server\Tests\Unit\Validators
 */
final class ConfigFileValidatorTest extends TestCase
{
	public function testValidationFailsForBrokenXmlConfigFile() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/broken.xml' );

		$this->assertTrue( $validator->failed() );
		$this->assertNotEmpty( $validator->getMessages() );
	}

	public function testValidationFailsNotForValidXmlConfigFile() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/valid.xml' );

		$this->assertFalse( $validator->failed() );
		$this->assertEmpty( $validator->getMessages() );
	}

	public function testFailsIfMessageQueueServerIsNotConfigured() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/missing-message-queue-server.xml' );

		$this->assertTrue( $validator->failed() );
		$this->assertNotEmpty( $validator->getMessages() );
	}

	public function testFailsIfMessageQueueServerSocketTypeIsInvalid() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/invalid-message-queue-server-socket-type.xml' );

		$this->assertTrue( $validator->failed() );
		$this->assertNotEmpty( $validator->getMessages() );
	}

	public function testFailsIfMessageQueueServerNetworkSocketIsInvalid() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/invalid-message-queue-server-network-socket.xml' );

		$this->assertTrue( $validator->failed() );
		$this->assertNotEmpty( $validator->getMessages() );
	}

	public function testFailsIfMessageQueueServerUnixSocketIsInvalid() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/invalid-message-queue-server-unix-socket.xml' );

		$this->assertTrue( $validator->failed() );
		$this->assertNotEmpty( $validator->getMessages() );
	}

	public function testMaintenanceServerConfigIsOptional() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/valid-no-maintenance-server.xml' );

		$this->assertFalse( $validator->failed() );
		$this->assertEmpty( $validator->getMessages() );
	}

	public function testFailsIfMaintenanceServerSocketTypeIsInvalid() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/invalid-maintenance-server-socket-type.xml' );

		$this->assertTrue( $validator->failed() );
		$this->assertNotEmpty( $validator->getMessages() );
	}

	public function testFailsIfMaintenanceServerNetworkSocketIsInvalid() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/invalid-maintenance-server-network-socket.xml' );

		$this->assertTrue( $validator->failed() );
		$this->assertNotEmpty( $validator->getMessages() );
	}

	public function testFailsIfMaintenanceServerUnixSocketIsInvalid() : void
	{
		$validator = new ConfigFileValidator( __DIR__ . '/Fixtures/invalid-maintenance-server-unix-socket.xml' );

		$this->assertTrue( $validator->failed() );
		$this->assertNotEmpty( $validator->getMessages() );
	}
}
