<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Session;

use Zanra\Framework\Session\Exception\SessionStartException;
use Zanra\Framework\Session\Flash\Flash;

/**
 * Zanra session
 *
 * @author Targalis
 *
 */
class Session implements SessionInterface
{
    /**
     * @var bool
     */
    private $started = false;

    /**
     * @var bool
     */
    private $closed = false;

    /**
     * @var string
     */
    private $flashname = null;

    /**
     * @var Flash
     */
    private $flash;

    /**
     * Constructor.
     */
    public function __Construct()
    {
        $this->flash = new Flash();
        $this->flashname = $this->flash->getName();
    }

    /**
     * @throws SessionStartException
     */
    public function start()
    {
        if ($this->started) {
            return;
        }

        // This prevents PHP from attempting to send the headers again
        // when session_write_close is called
        if ($this->closed) {
            ini_set('session.use_only_cookies', false);
            ini_set('session.use_cookies', false);
            ini_set('session.cache_limiter', null);
        }

        if (! session_start()) {
            throw new SessionStartException(
                sprintf('failed to start the session'));
        }

        $_SESSION[$this->flashname] = isset($_SESSION[$this->flashname]) ? $_SESSION[$this->flashname] : $this->flash;

        $this->closed = false;
        $this->started = true;
    }

    public function getId()
    {
        if (! $this->started) {
            $this->start();
        }

        return session_id();
    }

    public function setId($sessionId)
    {
        session_id($sessionId);
    }

    public function getName()
    {
        if (! $this->started) {
            $this->start();
        }

        return session_name();
    }

    public function setName($sessionName)
    {
        session_name($sessionName);
    }

    /**
     * @param string $key
     * @param object $val
     */
    public function set($key, $val)
    {
        if (! $this->started) {
            $this->start();
        }

        $_SESSION[$key] = $val;
    }

    /**
     * @param string $key
     *
     * @return object
     */
    public function get($key)
    {
        if (! $this->started) {
            $this->start();
        }

        $val = null;
        if (isset($_SESSION[$key])) {
            $val = $_SESSION[$key];
        }

        return $val;
    }

    public function close()
    {
        if (! $this->started) {
            return;
        }

        session_write_close();

        $this->closed = true;
        $this->started = false;
    }

    public function regenerate()
    {
        if (! $this->started) {
            $this->start();
        }

        session_regenerate_id();
    }
    
    public function destroy()
    {
        if (empty($_SESSION)) {
           return;
        }

        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        @session_destroy();

        $this->closed = false;
        $this->started = false;
    }

    /**
     * @return object
     */
    public function getFlash()
    {
        return $this->get($this->flashname);
    }
}
