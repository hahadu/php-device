<?php


namespace Hahadu\DeviceInfo\Device;


class Disk
{
    public function __construct(){

    }

    static public function init(){
        return new self();
    }

    /*****
     * @return array
     */
    public function list(){
        $disk['total'] = round(@disk_total_space(".")/(1024*1024*1024),3);
        $disk['free'] = round(@disk_free_space(".")/(1024*1024*1024),3);
        $disk['used'] = $disk['total'] - $disk['free'];
        $disk['percent'] = (floatval($disk['total'])!=0)?round($disk['used']/$disk['total']*100,2):0;
        return $disk;

    }

}