<?php
namespace module\test\controller;

use lying\service\Controller;
use lying\service\Logger;

/**
 * Class LoggerCtrl
 * @package module\test\controller
 */
class LoggerCtrl extends Controller
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->logger = \Lying::$maker->logger();
    }

    public function index()
    {
        $links = [
            'record' => url('record'),
        ];
        foreach ($links as $key => $link) {
            echo '<a target="_blank" href="' . $link . '">' . $key . '</a><br>';
        }
    }

    public function record()
    {
        $result = $this->logger->record(['name'=>'lying', 'sex'=>1, 'check'=>false, 'action'=>function() {}, 'info'=>[1,2,3]]);
        var_dump($result);
    }
}
