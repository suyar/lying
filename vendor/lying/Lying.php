<?php

final class Lying
{
    public static function autoload($className)
    {
        var_dump(self::class);
    }
}
spl_autoload_register([Lying::class, 'autoload']);


