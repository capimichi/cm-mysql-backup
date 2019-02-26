<?php

namespace CmMysqlBackup\Helper;

/**
 * Created by PhpStorm.
 * User: michele
 * Date: 26/02/2019
 * Time: 16:00
 */
class DirectoryHelper
{
    public static function delete($directory)
    {
        $i = new \DirectoryIterator($directory);
        foreach ($i as $f) {
            if ($f->isFile()) {
                unlink($f->getRealPath());
            } else if (!$f->isDot() && $f->isDir()) {
                self::delete($f->getRealPath());
            }
        }
        rmdir($directory);
    }
}