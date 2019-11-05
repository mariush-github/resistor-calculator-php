<?php

class Resistor {
    private $list;
    private $count;

    function __construct() {
        $this->list = array();
        $this->count = 0;
    }

    function count(){ 
        return $this->count;
    }

    function get($index) {
        return $this->list[$index];
    }

    private function getBaseResistors($e) {
        $eS = array();
        $values = '';
        if ($e>=3) $values .= '100,220,470,';
        if ($e>=6) $values .= '100,150,220,330,470,680,';
        if ($e>=12) $values .= '100,120,150,180,220,270,330,390,470,560,680,820,';
        if ($e>=24) $values .=  '100,110,120,130,150,160,180,200,220,240,270,300,330,'.
                                '360,390,430,470,510,560,620,680,750,820,910,';
        if ($e>=48) $values .=  '100,105,110,115,121,127,133,140,147,154,162,169,178,187,196,205,215,'.
                                '226,237,249,261,274,287,301,316,332,348,365,383,402,422,442,464,487,'.
                                '511,536,562,590,619,649,681,715,750,787,825,866,909,953,';
        if ($e>=96) $values .=  '100,102,105,107,110,113,115,118,121,124,127,130,133,137,140,143,147,'.
                                '150,154,158,162,165,169,174,178,182,187,191,196,200,205,210,215,221,'.
                                '226,232,237,243,249,255,261,267,274,280,287,294,301,309,316,324,332,'.
                                '340,348,357,365,374,383,392,402,412,422,432,442,453,464,475,487,499,'.
                                '511,523,536,549,562,576,590,604,619,634,649,665,681,698,715,732,750,'.
                                '768,787,806,825,845,866,887,909,931,953,976,';
        
        if ($e>=192) $values .= '100,101,102,104,105,106,107,109,110,111,113,114,115,117,118,120,121,'.
                                '123,124,126,127,129,130,132,133,135,137,138,140,142,143,145,147,149,'.
                                '150,152,154,156,158,160,162,164,165,167,169,172,174,176,178,180,182,'.
                                '184,187,189,191,193,196,198,200,203,205,208,210,213,215,218,221,223,'.
                                '226,229,232,234,237,240,243,246,249,252,255,258,261,264,267,271,274,'.
                                '277,280,284,287,291,294,298,301,305,309,312,316,320,324,328,332,336,'.
                                '340,344,348,352,357,361,365,370,374,379,383,388,392,397,402,407,412,'.
                                '417,422,427,432,437,442,448,453,459,464,470,475,481,487,493,499,505,'.
                                '511,517,523,530,536,542,549,556,562,569,576,583,590,597,604,612,619,'.
                                '626,634,642,649,657,665,673,681,690,698,706,715,723,732,741,750,759,'.
                                '768,777,787,796,806,816,825,835,845,856,866,876,887,898,909,920,931,'.
                                '942,953,965,976,988,';
        $v = array();
        $items = explode(',',$values);
        foreach ($items as $key=>$item) {  $temp = trim($item); if ($temp!='') $v[intval($temp)]=1; }
        $result = array();
        foreach ($v as $value => $nothing) {
            $result[] = $value;
        }
        return $result;
    }

    function loadResistorsFromFile($filename) {
        echo "Loading resistors from ".$filename."\n";
        $text = file_get_contents($filename);
        if (strlen($text)<1) return false;
        $text = strtolower($text);
        // allow dot (ex 2.2) , smd convention (ex 2R2), multiplier (ex 100k, 1m)
        $text = str_replace('r','.',$text);
        $text = str_replace(array("\t",' ',chr(0x0D),chr(0x0A)),',',$text);
        //var_dump($text);
        $values = explode(',',$text);
        foreach ($values as $idx => $val) {
            $value = trim($val);
            if ($value!=''){
                // $value = str_replace('r','.',$value);
                $mul = 1;
                if (substr($value,0,1)=='.') $value = '0'.$value;
                $c = substr($value,strlen($value)-1,1);
                if (ctype_digit($c)==false) {
                    if ($c=='k') $mul=1000;
                    if ($c=='m') $mul=1000000;
                    $value = substr($value,0,strlen($value)-1);
                }
                if (is_numeric($value)==true) {
                    $value = floatval($value) * $mul;
                    if ($value!=0) {
                        $this->list[$this->count] = $value;
                        $this->count++;
                    }
                }
            }
        }
        //var_dump($this->list);
    }
    function loadResistors($range,$min,$max) {
        $br = $this->getBaseResistors($range);
        $bCount = count($br);
        for ($i=-3;$i<5;$i++) {
            $multiplier = pow(10,$i); // 0.01, 1, 2, 10000000
            for ($j=0;$j<$bCount;$j++) {
                $nr = $br[$j] * $multiplier;
                if (($nr>=$min) && ($nr<=$max)) {
                    $this->list[$this->count] = $nr;
                    $this->count++;
                }
            }
        }
    }
    function arrangeResistors($value) {
        if ($this->count < 2 ) return;
        $dev = array();
        for ($i=0;$i<$this->count;$i++) {
            $dev[$i] = $this->list[$i]-$value; if ($dev[$i]<0) $dev[$i] = 0-$dev[$i];
        }
        $sorted = false;
        while ($sorted==false) {
            $sorted = true;
            for ($i=0;$i<$this->count-1;$i++) {
                if ($dev[$i] > $dev[$i+1]) {
                    $temp = $this->list[$i+1];
                    $this->list[$i+1] = $this->list[$i];
                    $this->list[$i] = $temp;
                    $temp = $dev[$i+1];
                    $dev[$i+1] = $dev[$i];
                    $dev[$i] = $temp;
                    $sorted = false;
                }
            }
        }
        //var_dump($this->list);
    }
}
?>