<?php

namespace VMMHelper;

defined('ABSPATH') || exit;

class SessionCookie {
    /**
     * Instance of the class.
     *
     * @var SessionCookie
     */
    private static $_instance = null;

    /**
     * Customer ID.
     *
     * @var int
     */
    private $_customer_id;

    /**
     * Session Data.
     *
     * @var array
     */
    private $_data = [];

    /**
     * Dirty when the session needs saving.
     *
     * @var bool
     */
    private $_dirty = false;

    /**
     * Private constructor to prevent multiple instances.
     */
    private function __construct() {
        // Start the session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Load session data if available
        if (isset($_SESSION['VMM_helper_data'])) {
            $this->_data = $_SESSION['VMM_helper_data'];
        }
    }

    /**
     * Get instance of the class.
     *
     * @return SessionCookie
     */
    public static function get_instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Get customer ID.
     *
     * @return int
     */
    public function get_customer_id() {
        return $this->_customer_id;
    }

    /**
     * Set customer ID.
     *
     * @param int $customer_id
     */
    public function set_customer_id($customer_id) {
        $this->_customer_id = $customer_id;
    }

    /**
     * Get session variable.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get($key, $default = null) {
        $key = sanitize_key($key);
        return isset($this->_data[$key]) ? maybe_unserialize($this->_data[$key]) : $default;
    }

    /**
     * Set session variable.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value) {
        $key = sanitize_key($key);
        if ($value !== $this->get($key)) {
            $this->_data[$key] = maybe_serialize($value);
            $this->_dirty = true;
            $this->save_session(); // Save the data immediately
        }
    }

    /**
     * Save session data if it's dirty.
     */
    public function save_session() {
        if ($this->_dirty) {
            $_SESSION['VMM_helper_data'] = $this->_data; // Store session data in $_SESSION
            $this->_dirty = false;
        }
    }

    /**
     * Magic get method.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * Magic set method.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value) {
        $this->set($key, $value);
    }

    /**
     * Magic isset method.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key) {
        return isset($this->_data[sanitize_key($key)]);
    }

    /**
     * Magic unset method.
     *
     * @param string $key
     */
    public function __unset($key) {
        $key = sanitize_key($key);
        if (isset($this->_data[$key])) {
            unset($this->_data[$key]);
            $this->_dirty = true;
            $this->save_session(); // Save the data immediately
        }
    }

    /**
     * Cleanup session data.
     */
    public function cleanup_sessions() {
        $_SESSION = []; // Clear all session variables
        session_destroy(); // Destroy the session
        $this->_data = [];
        $this->_dirty = false;
    }
}
