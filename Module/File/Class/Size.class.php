<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\File;

class Size {
    const b = 'b';
    const B = 'B';
    
    const KB = 'KB';
    const kb = 'kb';

    const MB = 'MB';
    const mb = 'mb';

    const GB = 'GB';
    const gb = 'gb';

    const TB = 'TB';
    const tb = 'tb';

    const PB = 'PB';
    const pb = 'pb';

    const EB = 'EB';
    const eb = 'eb';

    const FACTOR = 1024;

    const BREAKPOINT_B_KB  = 300;
    const BREAKPOINT_KB_MB  = 800;
    const BREAKPOINT_MB_GB  = 600;
    const BREAKPOINT_GB_TB  = 300;
    const BREAKPOINT_TB_PB  = 300;
    const BREAKPOINT_PB_EB  = 300;

    public static function convert($size=null){
        $size_kb = null;
        $size_mb = null;
        $size_gb = null;
        $size_tb = null;
        $size_pb = null;
        $size_eb = null;      
        
        if(is_numeric($size)){
            $size += 0;

            $unit = Size::B;
            if($size > 0){
                $size_kb = $size / Size::FACTOR;
                if(
                    $size_kb !== null &&
                    $size >= Size::BREAKPOINT_B_KB
                ){                
                    $size = $size_kb;
                    $unit = Size::KB;
                }
                if(
                    $size_kb !== null &&
                    $size_kb >= Size::BREAKPOINT_KB_MB
                ){
                    $size_mb = $size_kb / Size::FACTOR;
                    $size = $size_mb;
                    $unit = Size::MB;
                }
                if(
                    $size_mb !== null &&
                    $size_mb >= Size::BREAKPOINT_MB_GB
                ){
                    $size_gb = $size_mb / Size::FACTOR;
                    $size = $size_gb;
                    $unit = Size::GB;
                }
                if(
                    $size_gb !== null &&
                    $size_gb >= Size::BREAKPOINT_GB_TB
                ){
                    $size_tb = $size_gb / Size::FACTOR;
                    $size = $size_tb;
                    $unit = Size::TB;
                }
                if(
                    $size_tb !== null &&
                    $size_tb >= Size::BREAKPOINT_TB_PB
                ){
                    $size_pb = $size_tb / Size::FACTOR;
                    $size = $size_pb;
                    $unit = Size::PB;
                }       
                if(
                    $size_pb !== null &&
                    $size_pb >= Size::BREAKPOINT_PB_EB
                ){
                    $size_eb = $size_pb / Size::FACTOR;         
                    $size = $size_eb;       
                    $unit = Size::EB;
                }                         
            }
            return round($size, 2) . ' ' . $unit;
        } else {
            $size = str_replace(' ', '', $size);
            $size = str_replace('EB', '', $size, $count_eb);
            $size = str_replace('PB', '', $size, $count_pb);
            $size = str_replace('TB', '', $size, $count_tb);
            $size = str_replace('GB', '', $size, $count_gb);
            $size = str_replace('MB', '', $size, $count_mb);
            $size = str_replace('KB', '', $size, $count_kb);
            $size = str_replace('B', '', $size);
            $size = trim($size);            
            if(is_numeric($size)){
                $size = $size += 0;  
                if($count_kb > 0){
                    $size = $size * Size::FACTOR;
                }
                elseif($count_mb > 0){
                    $size = $size * Size::FACTOR * Size::FACTOR;
                }
                elseif($count_gb > 0){
                    $size = $size * Size::FACTOR * Size::FACTOR * Size::FACTOR;
                }
                elseif($count_tb > 0){
                    $size = $size * Size::FACTOR * Size::FACTOR * Size::FACTOR * Size::FACTOR;
                }
                elseif($count_pb > 0){
                    $size = $size * Size::FACTOR * Size::FACTOR * Size::FACTOR * Size::FACTOR * Size::FACTOR;
                }
                elseif($count_eb > 0){
                    $size = $size * Size::FACTOR * Size::FACTOR * Size::FACTOR * Size::FACTOR * Size::FACTOR * Size::FACTOR;
                }          
                return $size;            
            }           
        }           
    }
}