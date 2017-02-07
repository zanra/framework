<?php

/**
 * This file is part of the Zanra Framework package.
 *
 * (c) Targalis Group <targalisgroup@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zanra\Framework\FileLoader;

use Zanra\Framework\FileLoader\Exception\FileNotFoundException;
use Zanra\Framework\FileLoader\Exception\IllegalArgumentException;
use Zanra\Framework\FileLoader\Exception\WrongFileExtensionException;

/**
 * Zanra FileLoader
 *
 * @author Targalis
 */
class FileLoader implements FileLoaderInterface
{
    /**
     * @param $string
     *
     * @return string
     */
    private function getExtension($string)
    {
        return strtolower(substr(strrchr($string, '.'), 1));
    }

    /**
     * @param $file
     *
     * @return array
     */
    private function iniFileParser($file)
    {
        $parser = parse_ini_file($file, true);

        return $parser;
    }

    /**
     * @param array $array
     *
     * @return \stdClass
     */
    private function toObject(array $array)
    {
        $obj = new \stdClass;
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $obj->{$k} = $this->toObject($v);
            } else {
                $obj->{$k} = $v;
            }
        }

        return $obj;
    }

    /**
     * @param string $var
     *
     * @return \stdClass
     *
     * @throws FileNotFoundException
     * @throws WrongFileExtensionException
     */
    public function load($var)
    {
        $parser = array();

        if (is_string($var)) {
            if (! file_exists($var)) {
                throw new FileNotFoundException(
                    sprintf('File "%s" not found', $var)
                );
            }

            if (is_dir($var)) {
                throw new IllegalArgumentException(
                    sprintf('%s is not a file path', $var)
                );
            }

            $extension = $this->getExtension($var);

            if (false === method_exists($this, "{$extension}FileParser")) {
                throw new WrongFileExtensionException(
                    sprintf('No FileLoader function was found for "%s" extension', $extension)
                );
            }

            $parser = call_user_func_array(array($this, "{$extension}FileParser"), array($var));

        } elseif (is_array($var)) {
            $parser = $var;
        }

        return $this->toObject($parser);
    }
}
