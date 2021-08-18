<?php
/**
 * @author carolkey <su@revoke.cc>
 * @link https://github.com/carolkey/lying
 * @copyright 2018 Lying
 * @license MIT
 */

namespace lying\service;

/**
 * Class Router
 * @package lying\service
 */
class Router extends Service
{
    /**
     * @var array 存当前路由[module, controller, action]
     */
    private $_router;

    /**
     * @var bool 是否绑定模块
     */
    protected $binding;

    /**
     * @var string 默认模块
     */
    protected $module = 'index';

    /**
     * @var string 默认控制器
     */
    protected $controller = 'index';

    /**
     * @var string 默认方法
     */
    protected $action = 'index';

    /**
     * @var string 后缀
     */
    protected $suffix = false;

    /**
     * @var array 路由规则
     */
    protected $rule = [];

    /**
     * @var array 域名绑定模块
     */
    protected $host = [];

    /**
     * 判断域名是否绑定模块&解析请求路径
     */
    protected function init()
    {
        $host = \Lying::$maker->request->host();
        if (isset($this->host[$host])) {
            foreach ($this->host[$host] as $name => $value) {
                $this->$name = $value;
            }
            $this->binding = true;
        }
        $this->initRule();
        $this->parse();
    }

    /**
     * 预解析路由规则
     */
    private function initRule()
    {
        $rules = [];
        if (is_array($this->rule) && $this->rule) {
            foreach ($this->rule as $pattern => $rule) {
                $temp = [];
                $pattern = preg_replace('/\$$/', '', $pattern, 1, $temp['absolute']);
                $temp['pattern'] = $pattern;
                $temp['router'] = $rule[0];
                $temp['suffix'] = isset($rule[1]) ? $rule[1] : false;
                if (preg_match_all('/<([^<>]+)>/', $pattern, $matches)) {
                    foreach ($matches[1] as $k => $match) {
                        if (strpos($match, ':')) {
                            list($col, $reg) = explode(':', $match);
                            $temp['params'][] = ['reg'=>$reg, 'rep'=>$matches[0][$k], 'name'=>$col];
                        } else {
                            $temp['params'][] = ['reg'=>false, 'rep'=>$matches[0][$k], 'name'=>$match];
                        }
                    }
                } else {
                    $temp['params'] = [];
                }
                $rules[] = $temp;
            }
        }
        $this->rule = $rules;
    }

    /**
     * 解析路由规则
     * @param string $path 请求路径
     * @return bool 匹配到了返回true,没有匹配返回false
     */
    private function parseRule($path)
    {
        foreach ($this->rule as $rule) {
            //匹配后缀
            if ($rule['suffix']) {
                $len = strlen($rule['suffix']);
                if (substr_compare($path, $rule['suffix'], -$len, $len) === 0) {
                    $path = substr_replace($path, '', -$len, $len);
                } else {
                    continue;
                }
            }

            //匹配参数个数
            $pathArr = explode('/', $path);
            $count_path = count($pathArr);
            $patternArr = explode('/', $rule['pattern']);
            $count_pattern = count($patternArr);
            if ($count_path < $count_pattern || $rule['absolute'] && $count_path !== $count_pattern) {
                continue;
            }

            //匹配路由规则
            $cols = $rule['params'];
            $params = [];
            foreach ($patternArr as $k => $val) {
                $param = urldecode($pathArr[$k]);
                if (strpos($val, '<') === 0) {
                    $col = array_shift($cols);
                    if ($col['reg'] && !preg_match("/{$col['reg']}/", $param)) {
                        continue 2;
                    } else {
                        $params[$col['name']] = $param;
                    }
                } elseif (strcmp($val, $param) !== 0) {
                    continue 2;
                }
            }

            //删除已经用过的参数,剩余的参数并入GET参数
            $pathArr = array_slice($pathArr, $count_pattern);
            $more = [];
            while ($pathArr) {
                $more[] = array_shift($pathArr) . '=' . array_shift($pathArr);
            }
            $more && ($more = implode('&', $more)) && parse_str($more, $more);
            $_GET = array_merge($_GET, $more, $params);

            //分发路由
            $routerArr = explode('/', $rule['router']);
            $this->_router = [
                strtolower($this->binding ? $this->module : array_shift($routerArr)),
                strtolower(array_shift($routerArr)),
                strtolower(array_shift($routerArr)),
            ];
            return true;
        }
        return false;
    }

    /**
     * 常规解析路由
     * @param string $path 请求路径
     * @return bool 无论什么情况都返回true
     */
    private function parseNormal($path)
    {
        if ($path && $this->suffix) {
            $len = strlen($this->suffix);
            if (substr_compare($path, $this->suffix, -$len, $len) === 0) {
                $path = substr_replace($path, '', -$len, $len);
            }
        }

        //分发路由
        $pathArr = explode('/', $path);
        $this->_router = [
            strtolower($this->binding ? $this->module : (urldecode(array_shift($pathArr)) ?: $this->module)),
            strtolower(urldecode(array_shift($pathArr)) ?: $this->controller),
            strtolower(urldecode(array_shift($pathArr)) ?: $this->action),
        ];

        //剩余的参数并入GET参数
        $more = [];
        while ($pathArr) {
            $more[] = array_shift($pathArr) . '=' . array_shift($pathArr);
        }
        $more && ($more = implode('&', $more)) && parse_str($more, $more);
        $_GET = array_merge($_GET, $more);

        return true;
    }

    /**
     * 解析请求路由
     */
    private function parse()
    {
        $request = \Lying::$maker->request;
        $uri = $request->isCli() ? $request->getArgv(1, '/') : $request->uri();
        $parse = parse_url($uri);
        isset($parse['query']) && parse_str($parse['query'], $_GET);
        $path = trim($parse['path'], '/');
        $path && $this->parseRule($path) || $this->parseNormal($path);
    }

    /**
     * 返回此次请求的模块ID
     * @return string
     */
    public function module()
    {
        return $this->_router[0];
    }

    /**
     * 返回此次请求的控制器ID
     * @return string
     */
    public function controller()
    {
        return $this->_router[1];
    }

    /**
     * 返回此次请求的方法ID
     * @return string
     */
    public function action()
    {
        return $this->_router[2];
    }

    /**
     * URL生成
     * @param string $path 路径
     * @param array $params 参数数组,不合法的参数将被剔除
     * @param bool $host 是否携带完整域名,包含协议头,默认是
     * @param bool $normal 是否把参数设置成?a=1&b=2,默认否,优先pathinfo
     * @return string 返回生成的URL
     */
    public function createUrl($path, array $params = [], $host = true, $normal = false)
    {
        $host = $host ? \Lying::$maker->request->host(true) : '';
        if (strncmp($path, '/', 1) === 0 || !$path) {
            $path = rtrim($path, '?&');
            strpos($path, '.') || ($path = rtrim($path, '/') . '/');
            if ($query = http_build_query($params, '', '&', PHP_QUERY_RFC3986)) {
                $query = (strpos($path, '?') ? '&' : '?') . $query;
            }
            return $host . $path . $query;
        } else {
            $pathArr = explode('/', rtrim($path, '/'));
            switch (count($pathArr)) {
                case 3:
                    $routeArr = [$pathArr[0], $pathArr[1], $pathArr[2]];
                    break;
                case 2:
                    $routeArr = [$this->module(), $pathArr[0], $pathArr[1]];
                    break;
                case 1:
                    $routeArr = [$this->module(), $this->controller(), $pathArr[0]];
                    break;
                default:
                    $routeArr = $this->_router;
            }
            $this->binding && array_shift($routeArr);
            $requestUri = $this->buildRule($routeArr, $params, $normal) ?: $this->buildNormal($routeArr, $params, $normal);
            return $host . $requestUri;
        }
    }

    /**
     * 去掉PATH上默认的参数
     * @param array $routeArr 路由数组
     */
    private function rtrimDefault(array &$routeArr)
    {
        for ($i = count($routeArr) - 1; $i >= 0; $i--) {
            if ($i == 2) {
                if ($routeArr[$i] == $this->action) {
                    array_pop($routeArr);
                } else {
                    break;
                }
            } elseif ($i == 1) {
                if ($routeArr[$i] == ($this->binding ? $this->action : $this->controller)) {
                    array_pop($routeArr);
                } else {
                    break;
                }
            } elseif ($i == 0) {
                if ($routeArr[$i] == ($this->binding ? $this->controller : $this->module)) {
                    array_pop($routeArr);
                } else {
                    break;
                }
            }
        }
    }

    /**
     * 正常生成url
     * @param array $routeArr 路由数组
     * @param array $params 请求参数
     * @param bool $normal 是否把参数设置成?a=1&b=2
     * @return string 返回path参数
     */
    private function buildNormal($routeArr, array $params, $normal)
    {
        $params = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        if ($normal) {
            $this->rtrimDefault($routeArr);
            $route = implode('/', $routeArr);
            $route = $route ? ($this->suffix ? "/{$route}{$this->suffix}" : "/{$route}/") : '/';
            $params && ($route = "{$route}?{$params}");
        } else {
            if ($params) {
                $route = implode('/', $routeArr);
                $params = str_replace(['=','&'], '/', $params);
                $route = $this->suffix ? "/{$route}/{$params}{$this->suffix}" : "/{$route}/{$params}/";
            } else {
                $this->rtrimDefault($routeArr);
                $route = implode('/', $routeArr);
                $route = $route ? ($this->suffix ? "/{$route}{$this->suffix}" : "/{$route}/") : '/';
            }
        }
        return $route;
    }

    /**
     * 根据路由规则反解析
     * @param array $routeArr 路由数组
     * @param array $params 请求参数
     * @param bool $normal 是否把参数设置成?a=1&b=2
     * @return bool|string 成功返回path参数,失败返回false
     */
    private function buildRule($routeArr, array $params, $normal)
    {
        $route = implode('/', $routeArr);
        foreach ($this->rule as $rule) {
            if ($rule['router'] === $route) {
                $replace = [];
                foreach ($rule['params'] as $col) {
                    if (!isset($params[$col['name']]) || $col['reg'] && !preg_match("/{$col['reg']}/", $params[$col['name']])) {
                        continue 2;
                    }
                    $replace[] = urlencode($params[$col['name']]);
                }
                if ($replace) {
                    $cols = array_flip(array_column($rule['params'], 'name'));
                    $params = array_diff_key($params, $cols);
                    $reps = array_column($rule['params'], 'rep');
                    $route = str_replace($reps, $replace, $rule['pattern']);
                } else {
                    $route = $rule['pattern'];
                }
                $params = http_build_query($params, '',  '&', PHP_QUERY_RFC3986);
                if ($rule['absolute'] || $normal) {
                    $route = $rule['suffix'] ? "/{$route}{$rule['suffix']}" : "/{$route}/";
                    $params && ($route = "{$route}?{$params}");
                } else {
                    $params = str_replace(['=','&'], '/', $params);
                    $route = $params ? "/{$route}/{$params}" : "/{$route}";
                    $route = $rule['suffix'] ? "{$route}{$rule['suffix']}" : "{$route}/";
                }
                return $route;
            }
        }
        return false;
    }
}
