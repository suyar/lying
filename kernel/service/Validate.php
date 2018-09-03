<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Validate
 * @package lying\service
 */
class Validate extends Service
{
    protected static function rules(Validate $validate)
    {
        $validate->addRule('name', '', '', '')
            ->addRule();
    }


    public function addRule($column, $rule, $msg, $scene = null)
    {

        return $this;
    }

    public function check($scene = null)
    {

    }
}
