<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\Translator;

/**
 * Zanra TranslatorInterface
 *
 * @author Targalis
 *
 */
interface TranslatorInterface
{
    /**
     * Return translation directory
     *
     * @return string
     */
    public function getTranslationDir();

    /**
     * Set translation directory
     *
     * @param string $translationDir
     */
    public function setTranslationDir($translationDir);

    /**
     * @param string $message
     * @param string $locale
     *
     * @return string
     */
    public function translate($message, array $params = array(), $locale = null);
}
