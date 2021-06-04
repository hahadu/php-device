<?php


namespace Hahadu\DeviceInfo\Device;

/******
 * Class CPU
 * @package Hahadu\DeviceInfo\Device
 */
class CPU
{
    private $cpu_info = [
        'model_name' => false,
        'bogomips' => false,
        'model' => false
    ];

    public function __construct(){
        $this->cpu_info();
    }

    /******
     * @return array
     */
    public function list(){
        return [
            'freq' => $this->freq(),
            'stat' => $this->stat(),
            'temp' => $this->temp(),
            'count' => $this->count(),
            'model' => $this->model(),
            'model_id'=> $this->model_id(),
        ];
    }

    static public function init(){
        return new self();
    }

    /******
     * cpu freq
     * @return int
     */
    private function freq(){
        if (($str = @file("/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq")) !== false){
            return (int)$str[0];
        }
        else{
            return 0;
        }

    }

    /*******
     * cpu stat
     * @return array|int[]
     */
    private function stat(){
        if (($str = @file("/proc/stat")) !== false){
            $str = str_replace("  ", " ", $str);
            $info = explode(" ", implode("", $str));
            $stat =  [
                'user'=>$info[1],
                'nice'=>$info[2],
                'sys' => $info[3],
                'idle'=>$info[4],
                'iowait'=>$info[5],
                'irq' => $info[6],
                'softirq' => $info[7]
            ];
        }else{
            $stat = [
                'user'=>0,
                'nice'=>0,
                'sys' => 0,
                'idle'=> 0,
                'iowait'=> 0,
                'irq' => 0,
                'softirq' => 0
            ];
        }

        return $stat;

    }

    /******
     * cpu temp
     * @return array|false|int
     */
    private function temp(){
        if (($str = @file("/sys/class/thermal/thermal_zone0/temp")) !== false){
            $str[0] = (int)$str[0];
            return $str;
        }
        else{
            return 0;
        }

    }

    private function count(){
        if(false !== is_array($this->cpu_info['model_name'][1])){
            $count = sizeof($this->cpu_info['model_name'][1]);
        }else{
            $count = 1;
        }
        return $count;
    }

    private function model(){
        $bogomips = ' | Bogomips:'.$this->cpu_info['bogomips'][1][0];
        if($this->count() == 1){
            $model = $this->cpu_info['model_name'][1][0].$bogomips;
        }
        else{
            $model = $this->cpu_info['model_name'][1][0].$bogomips.' ×'.$this->count();
        }
        return $model;
    }

    private function model_id(){
        if (false !== is_array($this->cpu_info['model'][1])){
            $model = $this->cpu_info['model'][1][0];
        }else{
            $model = '';
        }
        return $model;

    }


    private function cpu_info(){
        if (($str = @file_get_contents("/proc/cpuinfo")) !== false){
            dump($str);
            @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model_name);
            @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);
            @preg_match_all("/model\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
            dump($model);
            dump($model_name);
            $this->cpu_info['model_name'] = $model_name;
            $this->cpu_info['bogomips'] = $bogomips;
            $this->cpu_info['model'] = $model;
        }
    }

}