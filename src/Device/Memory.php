<?php


namespace Hahadu\DeviceInfo\Device;


class Memory
{
    private $buf = false;
    private $buffers = false;

    public function __construct(){
        $this->meminfo();
    }
    static public function init(){
        return new self();
    }

    public function list(){
        $mem['total'] = $this->total();
        $mem['free'] = $this->free();
        $mem['buffers'] = $this->buffers();
        $mem['cached'] = $this->cached();
        $mem['cached_percent'] = (floatval($mem['cached'])!=0)?round($mem['cached']/$mem['total']*100,2):0;
        $mem['used'] = $mem['total']-$mem['free'];
        $mem['percent'] = (floatval($mem['total'])!=0)?round($mem['used']/$mem['total']*100,2):0;
        $mem['real']['used'] = $mem['total'] - $mem['free'] - $mem['cached'] - $mem['buffers'];
        $mem['real']['free'] = round($mem['total'] - $mem['real']['used'],2);
        $mem['real']['percent'] = (floatval($mem['total'])!=0)?round($mem['real']['used']/$mem['total']*100,2):0;
        $mem['swap'] = $this->swap();
        return $mem;
    }

    private function total(){
        if($this->buf !== false){
            $total = round($this->buf[1][0]/1024, 2);
        }else{
            $total = 0;
        }
        return $total;
    }
    private function free(){
        if($this->buf !== false){
            $free = round($this->buf[2][0]/1024, 2);
        }else{
            $free = 0;
        }
        return $free;
    }
    private function cached(){
        if($this->buf !== false){
            $cached = round($this->buf[3][0]/1024, 2);
        }else{
            $cached = 0;
        }
        return $cached;
    }
    private function buffers(){
        if($this->buffers !== false){
            $buffers = round($this->buffers[1][0]/1024, 2);
        }else{
            $buffers = 0;
        }
        return $buffers;
    }

    private function swap(){
        if($this->buf !==false){
            $swap['total'] = round($this->buf[4][0]/1024, 2);
            $swap['free'] = round($this->buf[5][0]/1024, 2);
            $swap['used'] = round($swap['total']-$swap['free'], 2);
            $swap['percent'] = (floatval($swap['total'])!=0)?round($swap['used']/$swap['total']*100,2):0;
        }else{
            $swap['total'] = 0;
            $swap['free'] = 0;
            $swap['used'] = 0;
            $swap['percent'] = 0;

        }
        return $swap;
    }

    private function real(){

    }

    private function meminfo(){
        if (($str = @file("/proc/meminfo")) !== false){
            $str = implode("", $str);

            preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
            preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);
            $this->buf = $buf;
            $this->buffers = $buffers;
        }

    }

}