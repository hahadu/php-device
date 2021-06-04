<?php


namespace Hahadu\DeviceInfo;


use Hahadu\DeviceInfo\Device\CPU;
use Hahadu\DeviceInfo\Device\Disk;
use Hahadu\DeviceInfo\Device\Memory;

class Device
{
    private $list = [];
    private $cpu;
    private $mem;
    private $version = 'V1.0.0';


    public function __construct(){
        $this->cpu = new CPU();
        $this->mem = new Memory();

    }
    static function init(){
        return new self();
    }

    public function get_list(){
        $this->get_info();
        $this->set_list();
        return $this->list;
    }

    private function set_list(){
        $this->list['version'] = $this->version;

        $this->list['author'] = $this->get_author();
        $this->list['user'] = @get_current_user();
        $this->list['hostname'] = gethostname();
        $this->list['hostip'] = ('/'==DIRECTORY_SEPARATOR) ? $_SERVER['SERVER_ADDR'] : @gethostbyname($_SERVER['SERVER_NAME']);
        $this->list['yourip'] = $_SERVER['REMOTE_ADDR'];
        $this->list['uname'] = @php_uname();
        $this->list['os'] = $this->os();

    }

    private function os(){
        return explode(" ", php_uname());
    }

    private function get_info(){

        $this->list['page']['time']['start'] = explode(' ', microtime());
        $this->list['time'] = time();

        if (($str = @file("/proc/uptime")) !== false){
            $str = explode(" ", implode("", $str));
            $this->list['uptime'] = trim($str[0]);
        }
        else{
            $this->list['uptime'] = 0;
        }

        $this->list['cpu'] = $this->cpu->list();
        $this->list['mem'] = $this->mem->list();

        if (($str = @file("/proc/loadavg")) !== false){
            $str = explode(" ", implode("", $str));
            $str = array_chunk($str, 4);
            $this->list['load_avg'] = $str[0];
        }
        else{
            $this->list['load_avg'] = array(0,0,0,'0/0');
        }

        $this->list['disk'] = Disk::init()->list();

        if (($strs = @file("/proc/net/dev")) !== false){
            $this->list['net']['count'] = count($strs) - 2;

            for ($i = 2; $i < count($strs); $i++ )
            {
                preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );
                $this->list['net']['interfaces'][$i-2]['name'] = $info[1][0];
                $this->list['net']['interfaces'][$i-2]['total_in'] = $info[2][0];
                $this->list['net']['interfaces'][$i-2]['total_out'] = $info[10][0];
            }
        }
        else{
            $this->list['net']['count'] = 0;
        }
    }


    private function get_author()
    {
        $model =  [
            'name' => 'hahadu',
            'page' => "http://github.com/hahadu",
            'email' => '582167246@qq.com',
        ];

        return $model;
    }

}