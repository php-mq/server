<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Loggers\Monitoring\Types;

use PHPMQ\Server\Exceptions\RuntimeException;

/**
 * Class ServerMonitoringConfig
 * @package PHPMQ\Server\Loggers\Monitoring\Types
 */
final class ServerMonitoringConfig
{
	/** @var bool */
	private $isEnabled = false;

	/** @var string */
	private $queueName = '';

	public function enable(): void
	{
		$this->isEnabled = true;
	}

	public function disable(): void
	{
		$this->isEnabled = false;
	}

	public function isEnabled(): bool
	{
		return $this->isEnabled;
	}

	public function isDisabled(): bool
	{
		return !$this->isEnabled;
	}

	public function getQueueName(): string
	{
		return $this->queueName;
	}

	public function setQueueName( string $queueName ): void
	{
		$this->queueName = $queueName;
	}

	public static function fromCLIOptions( ?array $argv = null ): self
	{
		$config        = new self();
		$enableOptions = [ '-m', '--monitor' ];

		if ( null === $argv )
		{
			$argv = $_SERVER['argv'];
		}

		if ( count( array_intersect( $enableOptions, $argv ) ) === 1 )
		{
			$config->enable();
		}

		foreach ( $argv as $arg )
		{
			$matches = [];
			if ( !preg_match( '#^\-q(?:(.*))$#', $arg, $matches )
			     && !preg_match( '#^\-\-queue=(?:(.*))$#', $arg, $matches )
			)
			{
				continue;
			}

			if ( $matches[1] === '' )
			{
				throw new RuntimeException( 'No queue name defined for monitoring.' );
			}

			$config->setQueueName( $matches[1] );
			break;
		}

		return $config;
	}
}
