<?php
require __DIR__ . '/BaseLying.php';

class Lying extends Lying\BaseLying
{
}
spl_autoload_register([Lying::class, 'autoload']);
set_exception_handler([\lying\core\Exception::class, 'exceptionHandler']);
set_error_handler([lying\core\Exception::class, 'errorHandler']);
register_shutdown_function([\lying\core\Exception::class, 'shutdownHandler']);



