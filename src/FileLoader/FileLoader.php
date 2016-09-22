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

use Zanra\Framework\FileLoader\FileLoaderInterface;
use Zanra\Framework\FileLoader\Exception\FileNotFoundException;
use Zanra\Framework\FileLoader\Exception\WrongFileExtensionException;

class FileLoader implements FileLoaderInterface
{
    private static $_instance = null;
	
    private function __Construct(){}
  
    private function getExtension($string) 
    {
        return strtolower(substr(strrchr($string,'.'),1));
    }
  
    private function iniFileParser($file)
    {
        $parser = parse_ini_file($file, true);
    
        return $parser;
    }
  
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
  
    public function load($var)
    {
        $parser = array();
    
        try {
            if (is_string($var)) {
                if (!file_exists($var))
                    throw new FileNotFoundException(
                        sprintf('File "%s" not found', $var));
                $extension = $this->getExtension($var);
                $parser = call_user_func_array(array($this, "{$extension}FileParser"), array($var));
            } elseif (is_array($var)) {
                $parser = $var;
            }
        } catch (Exception $e) {
            throw new WrongFileExtensionException(
                sprintf('No FileLoader function was found for "%s" extension', $extension));
        }
    
        return $this->toObject($parser);
    }
  
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new FileLoader();
        }
    
        return self::$_instance;
    }
}
