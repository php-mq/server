<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Logging\Monitoring\Types;

use PHPMQ\Server\Loggers\Monitoring\Types\ServerMonitoringConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class ServerMonitoringConfigTest
 * @package PHPMQ\Server\Tests\Unit\Logging\Monitoring\Types
 */
final class ServerMonitoringConfigTest extends TestCase
{
	public function testCanEnableServerMonitoring(): void
	{
		$config = new ServerMonitoringConfig();

		$this->assertFalse( $config->isEnabled() );
		$this->assertTrue( $config->isDisabled() );

		$config->enable();

		$this->assertTrue( $config->isEnabled() );
		$this->assertFalse( $config->isDisabled() );
	}

	public function testCanDisableServerMonitoring(): void
	{
		$config = new ServerMonitoringConfig();
		$config->enable();

		$this->assertTrue( $config->isEnabled() );
		$this->assertFalse( $config->isDisabled() );

		$config->disable();

		$this->assertFalse( $config->isEnabled() );
		$this->assertTrue( $config->isDisabled() );
	}

	/**
	 * @param array  $argv
	 * @param bool   $expectedEnabled
	 * @param string $expectedQueueName
	 *
	 * @dataProvider argvProvider
	 */
	public function testCanInitServerMonitoringConfigFromCLIOptions(
		?array $argv,
		bool $expectedEnabled,
		string $expectedQueueName
	): void
	{
		$config = ServerMonitoringConfig::fromCLIOptions( $argv );

		$this->assertSame( $expectedEnabled, $config->isEnabled() );
		$this->assertSame( $expectedQueueName, $config->getQueueName() );
	}

	public function argvProvider(): array
	{
		return [
			[
				'argv'              => [
					0 => '/path/to/script.php',
					1 => '-m',
				],
				'expectedEnabled'   => true,
				'expectedQueueName' => '',
			],
			[
				'argv'              => [
					0 => '/path/to/script.php',
					1 => '--monitor',
				],
				'expectedEnabled'   => true,
				'expectedQueueName' => '',
			],
			[
				'argv'              => [
					0 => '/path/to/script.php',
					1 => '-x',
				],
				'expectedEnabled'   => false,
				'expectedQueueName' => '',
			],
			[
				'argv'              => [
					0 => '/path/to/script.php',
				],
				'expectedEnabled'   => false,
				'expectedQueueName' => '',
			],
			[
				'argv'              => null,
				'expectedEnabled'   => false,
				'expectedQueueName' => '',
			],
			[
				'argv'              => [
					0 => '/path/to/script.php',
					1 => '-m',
				    2 => '-qTestQueue'
				],
				'expectedEnabled'   => true,
				'expectedQueueName' => 'TestQueue',
			],
			[
				'argv'              => [
					0 => '/path/to/script.php',
					1 => '-m',
				    2 => '--queue=TestQueue'
				],
				'expectedEnabled'   => true,
				'expectedQueueName' => 'TestQueue',
			],
		];
	}
}
