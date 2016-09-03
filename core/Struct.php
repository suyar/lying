<?php
namespace core;
/**
 * 数据表的结构
 * @author Carol
 */
class Struct {
    public $name = null;
    public $fields = [];
    public $key = null;
    public function __construct($attributes = []) {
        $this->name = isset($attributes['name']) ? $attributes['name'] : null;
        $this->fields = isset($attributes['fields']) ? $attributes['fields'] : [];
        $this->key = isset($attributes['key']) ? $attributes['key'] : null;
    }
}