<?php
namespace console\tool\controller;

use lying\db\Connection;
use lying\service\Controller;

class IndexCtrl extends Controller
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->db = \Lying::$maker->db();
    }

    /**
     * 选择工具
     */
    public function index()
    {
        $LOGO = <<<EOL
     __        __
    / / __ __ /_/__  __ ____
   / / / // // //  \/ // _  \
  / /_/ // // // /\  // // /
 /____\_  //_//_/ /_/_\_  /
    /____/          \____/
EOL;
        $this->cliPut("$LOGO\n");
        $this->cliPut("Model Builder: 1\n");
        $this->cliPut("Exit: 0\n");
        $this->cliPut("Type the number into the corresponding tool:");
        switch ($this->cliGet()) {
            case '0':
                $this->cliPut("Exit!");
                exit;
            case '1':
                $this->model();
                break;
            default:
                $this->cliPut("Unknown tool\n");
        }
    }

    /**
     * 模型工具
     */
    private function model()
    {
        $tables = $this->db->schema()->getTableNames();
        $this->cliPut('Please enter the table name:');
        $table = $this->cliGet();
        if ($table !== '' && !in_array($table, $tables)) {
            return $this->cliPut("Table $table does not exist!");
        }
        $this->cliPut('Please enter a namespace:');
        while (!($namespace = trim($this->cliGet(), '/\\'))) {
            $this->cliPut('Please enter a namespace:');
        }
        $namespace = str_replace('/', '\\', trim($namespace, '/\\'));
        $dir = DIR_ROOT . DS . str_replace('\\', DS, $namespace);
        if (!is_dir($dir)) {
            return $this->cliPut("Directory $dir does not exist");
        }
        if ($table === '') {
            foreach ($tables as $t) {
                $this->createModel($t, $namespace, $dir);
            }
        } else {
            $this->createModel($table, $namespace, $dir);
        }
    }

    /**
     * 创建一个模型
     * @param string $tableName 表全名
     * @param string $namespace 命名空间全名,开头不用
     * @param string $dir 命名空间所在路径
     */
    private function createModel($tableName, $namespace, $dir)
    {
        $cols = $this->db->schema()->getTableSchema($tableName)->columns;
        $tableName = preg_replace('/^'.preg_quote($this->db->prefix()).'/', '', $tableName);
        $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))).'Model';
        $file = $dir . DS . $modelName . '.php';
        if (file_exists($file)) {
            $this->cliPut("Model $modelName already exists, regenerated?(y/n):");
            while (!($continue = strtolower($this->cliGet()))) {
                $this->cliPut("Model $modelName already exists, regenerated?(y/n):");
            }
            if ($continue !== 'y') {
                $this->cliPut("Model $modelName is skipped\n");
                return;
            }
        }
        $content = implode(PHP_EOL, [
            '<?php',
            'namespace ' . $namespace . ';',
            '',
            '/**',
            ' * Class ' . $modelName,
            ' * @package ' . $namespace,
            '',
        ]);
        foreach ($cols as $col) {
            $content .= (' * @property string $' . $col . PHP_EOL);
        }
        $content .= implode(PHP_EOL, [
            ' */',
            'class ' . $modelName . ' extends \lying\db\ActiveRecord',
            '{',
            '',
            '}',
            '',
        ]);
        if (file_put_contents($file, $content)) {
            $this->cliPut('Model ' . $modelName . ' is created!' . "\n");
        } else {
            $this->cliPut("Fail to create model $modelName!\n");
        }
    }

    /**
     * CLI输入
     * @return string
     */
    private function cliGet()
    {
        return trim(fgets(STDIN));
    }

    /**
     * CLI输出
     * @param string $tips 输出的文字
     */
    private function cliPut($tips = '')
    {
        fwrite(STDOUT, $tips);
    }
}
