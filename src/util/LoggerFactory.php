<?php

namespace ngs\AdminTools\util;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    /** @var array */
    private static array $instances = [];

    /**
     * Returns logger instance
     *
     * @param $name
     * @param $className
     * @return Logger
     */
    public static function getLogger($name, $className, string $namespace = "")
    {
        if(!$namespace) {
            $loggerKey = md5($name . $className);
        }
        else {
            $loggerKey = md5($name . $className . $namespace);
        }


        if (isset(self::$instances[$loggerKey])) {
            return self::$instances[$loggerKey];
        }

        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler(self::getFile('info', $namespace), Logger::INFO));
        $logger->pushHandler(new StreamHandler(self::getFile('error', $namespace), Logger::ERROR));
        self::$instances[$loggerKey] = $logger;

        return $logger;
    }

    /**
     *
     * get log file check if already set path in
     * constant if not then create new one
     *
     * @param string $type
     * @param string $namespace
     * @return string
     */
    private static function getFile(string $type, string $namespace): string
    {
        $today = date('Y_m_d');
        $dir = NGS()->get('NGS_ROOT') . '/' . NGS()->get('DATA_DIR') . '/log/';
        if (NGS()->get('NGS_CMS_LOG_DIR')) {
            $dir = NGS()->get('NGS_CMS_LOG_DIR');
        }

        if (!is_dir($dir)) {
            if (!mkdir($dir) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }

        if($namespace) {
            $dir = $dir . '/' . $namespace . '/';
        }

        if (!is_dir($dir)) {
            if (!mkdir($dir) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }

        $file = $dir . '/' . $type . '_ngs_' . $today . '.log';
        if (file_exists($file)) {
            return $file;
        }
        if (!file_exists($file)) {
            @touch($file);
        }
        chown($file, 'www-data');
        return $file;
    }
}

