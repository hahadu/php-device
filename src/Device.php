<?php


namespace Hahadu\DeviceInfo;


class Device
{
    private $list = [];


    public function __construct(){

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
        $this->list['version'] = '1.1.0';
        $this->list['model'] = $this->get_device_model();
        $this->list['user'] = @get_current_user();
        $this->list['hostname'] = gethostname();
        $this->list['hostip'] = ('/'==DIRECTORY_SEPARATOR) ? $_SERVER['SERVER_ADDR'] : @gethostbyname($_SERVER['SERVER_NAME']);
        $this->list['yourip'] = $_SERVER['REMOTE_ADDR'];
        $this->list['uname'] = @php_uname();
        $this->list['os'] = explode(" ", php_uname());

        if (($str = @file("/proc/cpuinfo")) !== false){
            $str = implode("", $str);
            @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
            @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);
            @preg_match_all("/model\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $pimodel);

            if (false !== is_array($model[1])){
                $this->list['cpu']['count'] = sizeof($model[1]);
                $bogomips[1][0] = ' | Bogomips:'.$bogomips[1][0];
                if($this->list['cpu']['count'] == 1){
                    $this->list['cpu']['model'] = $model[1][0].$bogomips[1][0];
                }
                else{
                    $this->list['cpu']['model'] = $model[1][0].$bogomips[1][0].' ×'.$this->list['cpu']['count'];
                }
            }

            if (false !== is_array($pimodel[1])){
                $this->list['model']['pimodel'] = $pimodel[1][0];
            }
        }
        else{
            $this->list['cpu']['count'] = 1;
            $this->list['cpu']['model'] = '';
            $this->list['model']['pimodel'] = '';
        }


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

        if (($str = @file("/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq")) !== false){
            $this->list['cpu']['freq'] = (int)$str[0];
        }
        else{
            $this->list['cpu']['freq'] = 0;
        }

        // CPU Core
        if (($str = @file("/proc/stat")) !== false){
            $str = str_replace("  ", " ", $str);
            $info = explode(" ", implode("", $str));
            $this->list['cpu']['stat'] = array('user'=>$info[1],
                'nice'=>$info[2],
                'sys' => $info[3],
                'idle'=>$info[4],
                'iowait'=>$info[5],
                'irq' => $info[6],
                'softirq' => $info[7]
            );
        }
        else{
            $this->list['cpu']['stat'] = array('user'=>0,
                'nice'=>0,
                'sys' => 0,
                'idle'=> 0,
                'iowait'=> 0,
                'irq' => 0,
                'softirq' => 0
            );
        }


        if (($str = @file("/sys/class/thermal/thermal_zone0/temp")) !== false){
            $this->list['cpu']['temp'] = $str;
        }
        else{
            $this->list['cpu']['temp'] = 0;
        }


        if (($str = @file("/proc/meminfo")) !== false){
            $str = implode("", $str);

            preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
            preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

            $this->list['mem']['total'] = round($buf[1][0]/1024, 2);
            $this->list['mem']['free'] = round($buf[2][0]/1024, 2);
            $this->list['mem']['buffers'] = round($buffers[1][0]/1024, 2);
            $this->list['mem']['cached'] = round($buf[3][0]/1024, 2);
            $this->list['mem']['cached_percent'] = (floatval($this->list['mem']['cached'])!=0)?round($this->list['mem']['cached']/$this->list['mem']['total']*100,2):0;
            $this->list['mem']['used'] = $this->list['mem']['total']-$this->list['mem']['free'];
            $this->list['mem']['percent'] = (floatval($this->list['mem']['total'])!=0)?round($this->list['mem']['used']/$this->list['mem']['total']*100,2):0;
            $this->list['mem']['real']['used'] = $this->list['mem']['total'] - $this->list['mem']['free'] - $this->list['mem']['cached'] - $this->list['mem']['buffers'];
            $this->list['mem']['real']['free'] = round($this->list['mem']['total'] - $this->list['mem']['real']['used'],2);
            $this->list['mem']['real']['percent'] = (floatval($this->list['mem']['total'])!=0)?round($this->list['mem']['real']['used']/$this->list['mem']['total']*100,2):0;
            $this->list['mem']['swap']['total'] = round($buf[4][0]/1024, 2);
            $this->list['mem']['swap']['free'] = round($buf[5][0]/1024, 2);
            $this->list['mem']['swap']['used'] = round($this->list['mem']['swap']['total']-$this->list['mem']['swap']['free'], 2);
            $this->list['mem']['swap']['percent'] = (floatval($this->list['mem']['swap']['total'])!=0)?round($this->list['mem']['swap']['used']/$this->list['mem']['swap']['total']*100,2):0;
        }
        else{
            $this->list['mem']['total'] = 0;
            $this->list['mem']['free'] = 0;
            $this->list['mem']['buffers'] = 0;
            $this->list['mem']['cached'] = 0;
            $this->list['mem']['cached_percent'] = 0;
            $this->list['mem']['used'] = 0;
            $this->list['mem']['percent'] = 0;
            $this->list['mem']['real']['used'] = 0;
            $this->list['mem']['real']['free'] = 0;
            $this->list['mem']['real']['percent'] = 0;
            $this->list['mem']['swap']['total'] = 0;
            $this->list['mem']['swap']['free'] = 0;
            $this->list['mem']['swap']['used'] = 0;
            $this->list['mem']['swap']['percent'] = 0;
        }


        if (($str = @file("/proc/loadavg")) !== false){
            $str = explode(" ", implode("", $str));
            $str = array_chunk($str, 4);
            $this->list['load_avg'] = $str[0];
        }
        else{
            $this->list['load_avg'] = array(0,0,0,'0/0');
        }

        $this->list['disk']['total'] = round(@disk_total_space(".")/(1024*1024*1024),3);
        $this->list['disk']['free'] = round(@disk_free_space(".")/(1024*1024*1024),3);
        $this->list['disk']['used'] = $this->list['disk']['total'] - $this->list['disk']['free'];
        $this->list['disk']['percent'] = (floatval($this->list['disk']['total'])!=0)?round($this->list['disk']['used']/$this->list['disk']['total']*100,2):0;


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

    private function get_device_model(){
        return ['name' => 'Hahadu\DeviceInfo', 'id' => 'PHP DEVICE CLASS','auth'=>"github.com/hahadu",'email'=>'582167246@qq.com'];
    }

}