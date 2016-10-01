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

use Zanra\Framework\Session\SessionInterface;
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
     * @var \Zanra\Framework\Session\Flash\Flash
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
     * (non-PHPdoc)
     *
     * @see \Zanra\Framework\Session.SessionInterface::start()
     */
    public function start()
    {
        if ($this->started) {
            return true;
        }

        // This prevents PHP from attempting to send the headers again
        // when session_write_close is called
        if ($this->closed) {
            ini_set('session.use_only_cookies', false);
            ini_set('session.use_cookies', false);
            ini_set('session.cache_limiter', null);
        }

        if (!session_start()) {
            throw new SessionStartException(
                sprintf('failed to start the session'));
        }

        $_SESSION[$this->flashname] = isset($_SESSION[$this->flashname]) ? $_SESSION[$this->flashname] : $this->flash;

        $this->closed = false;
        $this->started = true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zanra\Framework\Session.SessionInterface::close()
     */
    public function close()
    {
        if (!$this->started) {
            return true;
        }

        session_write_close();

        $this->closed = true;
        $this->started = false;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zanra\Framework\Session.SessionInterface::set()
     */
    public function set($key, $val)
    {
        if (!$this->started) {
            $this->start();
        }

        $_SESSION[$key] = $val;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zanra\Framework\Session.SessionInterface::get()
     */
    public function get($key)
    {
        if (!$this->started) {
            $this->start();
        }

        $val = null;
        if (isset($_SESSION[$key])) {
            $val = $_SESSION[$key];
        }

        return $val;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zanra\Framework\Session.SessionInterface::destroy()
     */
    public function destroy()
    {
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

        session_destroy();

        $this->closed = false;
        $this->started = false;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zanra\Framework\Session.SessionInterface::getFlash()
     */
    public function getFlash()
    {
        return $this->get($this->flashname);
    }
}
