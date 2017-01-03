<?php
namespace lying\session;

class CacheSession implements \SessionHandlerInterface
{
    public function open($save_path, $session_name)
    {
        echo "open:", $save_path, "<br>", $session_name, "<br>";
        return true;
    }
    
    public function close(){
        return true;
    }
    
    public function read($session_id)
    {
        echo "read:", $session_id, "<br>";
        return '666';
    }
    
    public function write($session_id, $session_data)
    {
        echo "write:", $session_id, "<br>", $session_data, "<br>";
        return true;
    }
    
    public function destroy($session_id)
    {
        echo "destroy:", $session_id, "<br>";
        return true;
    }
    
    public function gc($maxlifetime)
    {
        var_dump($maxlifetime);
        return true;
    }
}