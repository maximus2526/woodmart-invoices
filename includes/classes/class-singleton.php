<?php

/**
 * Singleton pattern class.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

namespace WoodMart\Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Singleton pattern class.
 *
 * @since 1.0.0
 */
class Invoices_Singleton {

	/**
	 * Instance of this static object.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Whether the instance is initialized.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $initialized = false;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.0.0
	 * @return object Current object instance.
	 */
	public static function get_instance() {
		$subclass = static::class;
		if ( ! isset( self::$instances[ $subclass ] ) ) {
			self::$instances[ $subclass ] = new static();
		}
		return self::$instances[ $subclass ];
	}

	/**
	 * Prevent singleton class clone.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
	}

	/**
	 * Prevent singleton class initialization.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Prevent unserializing.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
	}

	/**
	 * Check if the instance is initialized.
	 *
	 * @since 1.0.0
	 * @return bool Whether the instance is initialized.
	 */
	public function is_initialized() {
		return $this->initialized;
	}

	/**
	 * Mark the instance as initialized.
	 *
	 * @since 1.0.0
	 */
	protected function set_initialized() {
		$this->initialized = true;
	}
}
