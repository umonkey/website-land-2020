<?php
/**
 * Task Queue, background task execution.
 *
 * @docs http://bugs.home.umonkey.net/wiki?name=%D0%9E%D1%87%D0%B5%D1%80%D0%B5%D0%B4%D1%8C+%D0%B7%D0%B0%D0%B4%D0%B0%D1%87
 *
 * @see vendor/umonkey/ufw1/src/Ufw1/Handlers/TaskQ.php
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\CommonHandler;

class TaskQ extends \Ufw1\Handlers\TaskQ
{
}
