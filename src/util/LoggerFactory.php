<?php

namespace ngs\NgsAdminTools\util;

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
    public static function getLogger($name, $className)
    {
        $loggerKey = md5($name . $className);

        if (isset(self::$instances[$loggerKey])) {
            return self::$instances[$loggerKey];
        }

        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler(self::getFile('info'), Logger::INFO));
        $logger->pushHandler(new StreamHandler(self::getFile('error'), Logger::ERROR));
        self::$instances[$loggerKey] = $logger;

        return $logger;
    }

    /**
     *
     * get log file check if already set path in
     * constant if not then create new one
     *
     * @param string $type
     * @return string
     */
    private static function getFile(string $type): string
    {
        $today = date('Y_m_d');
        $dir = NGS()->get('NGS_ROOT') . '/' . NGS()->get('DATA_DIR') . '/log/';
        if (NGS()->get('NGS_CMS_LOG_DIR')) {
            $dir = NGS()->get('NGS_CMS_LOG_DIR');
        }
        $file = $dir . '/' . $type . '_ngs_' . $today . '.log';
        if (file_exists($file)) {
            return $file;
        }
        if (!is_dir($dir)) {
            if (!mkdir($dir) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }
        if (!file_exists($file)) {
            @touch($file);
        }
        chown($file, 'www-data');
        return $file;
    }
}

