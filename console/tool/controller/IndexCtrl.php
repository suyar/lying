<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace console\tool\controller;

/**
 * Class IndexCtrl
 * @package console\tool\controller
 */
class IndexCtrl extends BaseTool
{
    /**
     * LOGO
     */
    private static $LOGO = <<<EOL
     __        __
    / / __ __ /_/__  __ ____
   / / / // // //  \/ // _  \
  / /_/ // // // /\  // // /
 /____\_  //_//_/ /_/_\_  /
    /____/          \____/
EOL;

    /**
     * @var array
     */
    private static $TOOLS = [
        1 => ['Model Create', [ModelTool::class, 'create']],
        2 => ['Model Update', [ModelTool::class, 'update']],
        0 => ['Exit'],
    ];

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->stdOut(self::$LOGO);
    }

    /**
     * 选择工具
     */
    public function index()
    {
        foreach (self::$TOOLS as $id => $tool) {
            $this->stdOut("{$id}: {$tool[0]}");
        }
        $this->stdOut("Type the number into the corresponding tool:", false);
        $toolId = $this->stdIn();
        if ($toolId === '0') {
            exit(0);
        } if (isset(self::$TOOLS[$toolId])) {
            call_user_func([new self::$TOOLS[$toolId][1][0](), self::$TOOLS[$toolId][1][1]]);
        } else {
            $this->stdErr("Unknown tool");
        }
    }
}
