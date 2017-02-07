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

/**
 * Zanra session interface
 *
 * @author Targalis
 */
interface SessionInterface
{
    /**
     * Start a new session
     */
    public function start();

    /**
     * Close a session
     */
    public function close();

    /**
     * @param string $key
     * @param object $val
     */
    public function set($key, $val);

    /**
     * @param string $key
     */
    public function get($key);

    /**
     * Get sessionId
     */
    public function getId();

    /**
     * Set sessionId
     *
     * @param string $sessionId
     */
    public function setId($sessionId);

    /**
     * Get sessionName
     */
    public function getName();

    /**
     * Set sessionName
     *
     * @param string $sessionName
     */
    public function setName($sessionName);

    /**
     * Regenerate session
     */
    public function regenerate();

    /**
     * Destroy a session
     */
    public function destroy();

    /**
     * Get session flash object
     */
    public function getFlash();
}
