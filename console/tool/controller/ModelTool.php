<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace console\tool\controller;

use lying\db\Connection;

/**
 * Class ModelTool
 * @package console\tool\controller
 */
class ModelTool extends BaseTool
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var string
     */
    protected $dbName;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->stdOut('Enter the service name of db(the default is \'db\'):', false);
        $this->dbName = $this->stdIn() ?: 'db';
        $this->db = \Lying::$maker->db($this->dbName);
    }

    /**
     * 创建数据库模型
     */
    public function create()
    {
        $tableArr = $this->getInputTables();
        list($namespace, $dir) = $this->getInputNamespace();

        $prefix = \Lying::config('service.' . $this->dbName . '.prefix', '');
        foreach ($tableArr as $table) {
            $tableName = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $table);
            $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))) . 'Model';
            $modelFile = $dir . DS . $modelName . '.php';
            if (file_exists($modelFile)) {
                $this->stdOut("Model `$modelName` already exists, whether to regenerate?(y/n):", false);
                if ($this->stdIn() !== 'y') {
                    $this->stdOut("Model `$modelName` is skipped");
                    continue;
                }
            }

            $cols = $this->db->schema()->getTableSchema($table)->columns;
            $content = implode(PHP_EOL, [
                '<?php',
                'namespace ' . $namespace . ';',
                '',
                'use lying\db\ActiveRecord;',
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
                'class ' . $modelName . ' extends ActiveRecord',
                '{',
                '',
                '}',
                '',
            ]);

            if (file_put_contents($modelFile, $content)) {
                $this->stdOut("Model `$modelName` is created!");
            } else {
                $this->stdOut("Failed to create model `$modelName`!");
            }
        }
    }

    /**
     * 更新数据库模型
     */
    public function update()
    {
        $tableArr = $this->getInputTables();
        list($namespace, $dir) = $this->getInputNamespace(false);

        $prefix = \Lying::config('service.' . $this->dbName . '.prefix', '');
        foreach ($tableArr as $table) {
            $tableName = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $table);
            $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))) . 'Model';
            $modelFile = $dir . DS . $modelName . '.php';
            if (!file_exists($modelFile)) {
                $this->stdOut("Model `$modelName` does not exist");
                continue;
            }

            $cols = $this->db->schema()->getTableSchema($table)->columns;
            $content = implode(PHP_EOL, [
                '/**',
                ' * Class ' . $modelName,
                ' * @package ' . $namespace,
                '',
            ]);
            foreach ($cols as $col) {
                $content .= (' * @property string $' . $col . PHP_EOL);
            }
            $content .= ' */';

            $old = file_get_contents($modelFile);
            if (preg_match('/\/\*\*.* \*\//s', $old, $matches)) {
                $new = str_replace($matches[0], $content, $old);
                if (file_put_contents($modelFile, $new)) {
                    $this->stdOut("Model `$modelName` is updated!");
                } else {
                    $this->stdOut("Failed to update model `$modelName`!");
                }
            } else {
                $this->stdOut("Failed to update model `$modelName`!");
            }
        }
    }

    /**
     * 获取用户手动输入的表名数组
     * @return array
     */
    private function getInputTables()
    {
        $tables = $this->db->schema()->getTableNames();
        $this->stdOut('Enter the table name(split width \'|\' or just enter for all):', false);
        $table = $this->stdIn();
        if ($table === '') {
            $tableArr = $tables;
        } else {
            $tableArr = explode('|', trim($table, '|'));
            foreach ($tableArr as $t) {
                in_array($t, $tables) || $this->stdErr("Table `$t` does not exist!");
            }
        }
        return $tableArr;
    }

    /**
     * 获取命名空间和对应文件夹
     * @param bool $cdir 是否创建文件夹
     * @return array
     */
    private function getInputNamespace($cdir = true)
    {
        $this->stdOut('Enter a namespace(use psr-0 with path `ROOT`):', false);
        $namespace = str_replace('/', '\\', trim($this->stdIn(), '/\\'));
        $namespace || $this->stdErr('Unable to use \'\' or / for namespace');
        $this->stdOut("Use namespace `$namespace`");
        $dir = DIR_ROOT . DS . str_replace('\\', DS, $namespace);
        if (is_dir($dir)) {
            $this->stdOut("Use directory `$dir`");
        } else if ($cdir && @mkdir($dir, 0777, true)) {
            $this->stdOut("Created directory `$dir`");
        } else if ($cdir) {
            $this->stdErr("Failed to create directory `$dir`");
        } else {
            $this->stdErr("Directory `$dir` does not exist!");
        }
        return [$namespace, $dir];
    }
}
