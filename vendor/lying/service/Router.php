<?php
namespace lying\service;

class Router extends Service
{
    public function parse()
    {
        $request = $this->get('request');
        $uri = $request->uri();
        $parse = parse_url($uri);
        $_GET = [];
        if (isset($parse['query'])) {
            parse_str($parse['query'], $_GET);
        }
        
        $host = $request->host();
        $conf = $this->get('config')->load('router');
        
        $this->resolve($parse['path']);
        
        if (isset($conf[$host])) {
            $m = $conf[$host]['module'];
            var_dump($m);
        }
    }
    
    public function resolve($path)
    {
        var_dump($path);
    }
}