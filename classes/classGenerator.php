<?php

class ResultGenerator {
    private $hashes;
    public $list;
    private $count;

    function __construct($cachePath=''){
        $this->count = 0;
        $this->list = array();
        $this->hashes = array();
        
        $this->cachePath = $cachePath;
        if ($cachePath=='') $this->cachePath = str_replace(array('\\','//'),'/',__DIR__.'/../cache');
        //var_dump($this->cachePath);
    }

    function generate($dim,$resCount){
        $chunkSize = 256;
        $resCountSafe = ($resCount > 256) ? 256 : $resCount;
        if ($dim==4) ($resCount > 64) ? 64 : $resCount;
        $limit_up = array( 2=>64, 3=>256, 4=>256);
        $increment = array( 2=> 32, 3=>16, 4=>8);
        for ($i=0;$i<$limit_up[$dim];$i=$i+$increment[$dim]) {
            if (($resCountSafe > $i) && ($resCountSafe<=$i+$increment[$dim])) $chunkSize = $i+$increment[$dim];
        }

        // if ($dim==4) {
        //     for ($i=0;$i<$limit_up[$dim];$i=$i+8) {
        //         if (($resCountSafe > $i) && ($resCountSafe<=$i+8)) $chunkSize = $i+8;
        //     }
        // }
        // if ($dim==3) {
        //     for ($i=0;$i<256;$i=$i+16) {
        //         if (($resCountSafe > $i) && ($resCountSafe<=$i+16)) $chunkSize = $i+16;
        //     }
        // }
        // if ($dim==2) {
        //     for ($i=0;$i<256;$i=$i+32) {
        //         if (($resCountSafe > $i) && ($resCountSafe<=$i+32)) $chunkSize = $i+32;
        //     }           
        // }
        //if ($chunkSize >256) $chunkSize=256;
        echo "\nLoading combinations from cache [".$dim.','.$chunkSize.']... ';
        $this->reset();
        $result = $this->loadFromCache($dim,$chunkSize,$resCount);
        if ($result==FALSE){
            echo "File not found.\nGenerating and saving combinations to cache...\n";
            $result = $this->saveToCache($dim,$chunkSize,$resCount);
            $this->reset();
            echo "\nLoading combinations from cache [".$dim.','.$chunkSize.']... ';
            $result = $this->loadFromCache($dim,$chunkSize,$resCount);
        }
    }

    function saveToCache($dim,$chunkSize,$actualChunkSize) {
        $ret = @mkdir($this->cachePath);
        $hOut = @fopen($this->cachePath.'/cache_'.$dim.'_'.$chunkSize.'.bin','wb');
        if ($dim==2) {
            for ($j=0;$j<$chunkSize;$j++) {
                for ($i=0;$i<$chunkSize;$i++) {
                    $this->add($j,$i,255,255,2);
                }
            }
        }
        if ($dim==3) {
            for ($k=0;$k<$chunkSize;$k++) {
                echo '.';
                for ($j=0;$j<$chunkSize;$j++) {
                    for ($i=0;$i<$chunkSize;$i++) {
                        $this->add($k,$j,$i,255,3);
                    }
                }
            }
        }
        if ($dim==4) {
            for ($l=0;$l<$chunkSize;$l++) {
                echo '.';
                for ($k=0;$k<$chunkSize;$k++) {
                    for ($j=0;$j<$chunkSize;$j++) {
                        for ($i=0;$i<$chunkSize;$i++) {
                            $this->add($l,$k,$j,$i,4);
                        }
                    }
                }
            }
        }
        if ($dim>2) echo "\n";
        echo "Done. Generated ".($this->count)." combinations.\n";
        for ($counter=0;$counter<$this->count;$counter++) {
            $value = $this->list[$counter];
            $buffer = chr($value[0]).chr($value[1]);
            if ($dim>2) $buffer.=chr($value[2]);
            if ($dim>3) $buffer.=chr($value[3]);
            $ret = fwrite($hOut,$buffer);
            //if ($dim==2) $ret = fwrite($hOut,substr($this->list[$counter],0,2),2);
            //if ($dim==3) $ret = fwrite($hOut,substr($this->list[$counter],0,3),3);
            //if ($dim==4) $ret = fwrite($hOut,substr($this->list[$counter],0,4),4);
        }
        fclose($hOut);
    }
    function loadFromCache($dim,$chunkSize,$actualChunkSize) {
        $hIn = @fopen($this->cachePath.'/cache_'.$dim.'_'.$chunkSize.'.bin','rb');
        if ($hIn===FALSE) return FALSE;
        $buffer = fread($hIn,$dim);
        
        while ($buffer!='') {
            $bytes = array(255,255,255,255);
            if ($dim==4) $bytes[3] = ord(substr($buffer,3,1));
            
            if ($dim>2) $bytes[2] = ord(substr($buffer,2,1));
            // if ($dim==3) $bytes[1] = ord(substr($buffer,0,1));
            
            $bytes[1] = ord(substr($buffer,1,1));
            // if ($dim==3) $bytes[2] = ord(substr($buffer,1,1));
            // if ($dim==2) $bytes[2] = ord(substr($buffer,0,1));

            $bytes[0] = ord(substr($buffer,0,1));
            // if ($dim==3) $bytes[3] = ord(substr($buffer,2,1));
            // if ($dim==2) $bytes[3] = ord(substr($buffer,1,1));
            if ($dim==2) {
                if (($bytes[0]<$actualChunkSize) && ($bytes[1]<$actualChunkSize) ) {
                        $this->add($bytes[0],$bytes[1],$bytes[2],$bytes[3],2);
                }
            }
            if ($dim==3) {
                if (($bytes[0]<$actualChunkSize) && ($bytes[1]<$actualChunkSize) && ($bytes[2]<$actualChunkSize)) {
                    $this->add($bytes[0],$bytes[1],$bytes[2],$bytes[3],3);
                }
            }
            if ($dim==4) {
                if (($bytes[0]<$actualChunkSize) && ($bytes[1]<$actualChunkSize) && 
                ($bytes[2]<$actualChunkSize) && ($bytes[3]<$actualChunkSize)) {
                    $this->add($bytes[0],$bytes[1],$bytes[2],$bytes[3],4);
                }
            }

            
            $buffer = fread($hIn,$dim);
        }
        echo "Done. Loaded ".($this->count)." combinations from cache.\n";

        $ret = fclose($hIn);
        return TRUE;
    }

    function reset() {
        $this->count = 0;
        $this->list = array();
        $this->hashes = array();

    }

    function add($a,$b,$c,$d,$count=4){
        $buffer = array(0=>$a,1=>$b,2=>$c,3=>$d);
        
        //var_dump($buffer);
        $sorted = false;
        while($sorted==false) {
            $sorted = true;
            for ($i=0;$i<$count-1;$i++) {
                if ($buffer[$i]>$buffer[$i+1]){
                    $temp = $buffer[$i];
                    $buffer[$i] = $buffer[$i+1];
                    $buffer[$i+1] = $temp;
                    $sorted = false;
                }
            }
        }
        //var_dump($buffer);
        
        if ($count>1) $hash = $buffer[0]*256 + $buffer[1];
        if ($count>2) $hash = $hash*256 +$buffer[2];
        if ($count==4) $hash = $hash*256 + $buffer[3];

        if ($count==2) $value = array(0=>$buffer[0],1=>$buffer[1]);
        if ($count==3) $value = array(0=>$buffer[0],1=>$buffer[1],2=>$buffer[2]);
        if ($count==4) $value = array(0=>$buffer[0],1=>$buffer[1],2=>$buffer[2],3=>$buffer[3]);
        
        if (isset($this->hashes[$hash])==false) {
            $this->hashes[$hash] = true;
            $this->list[$this->count] = $value;$this->count++;
            
        }
    }

    function dumpToFile($filename) {
        echo "\n".$this->count."\n";
        $hOut= fopen($filename,'wb');
        if ($this->count>=0) {
            for ($i=0;$i<$this->count;$i++){
                //$buffer = str_pad(dechex($this->list[$i]),16,'0',STR_PAD_LEFT);
                fwrite($hOut,$this->list[$i],4);
            }
        }
        fclose($hOut);
    }
    function count(){
        return $this->count;
    }
    function get($index) {
        return $this->list[$index];
    }

}
?>