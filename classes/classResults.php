<?php

class Results {
    private $results;
    private $hash;
    private $count;
    private $max; 
    private $threshold;
    private $v;

    function __construct($value, $maxResults=100) {
        $this->count = 0;
        $this->results = array();
        $this->threshold = 15000;
        $this->v = $value;
    }
    // unused
    function setThreshold($value) {
        $this->threshold = $value;
    }

    function add($sequence,$value,$rcount) {
        if (isset($this->hash[$sequence])==false) {
            //echo str_pad($value,10,' ',STR_PAD_LEFT).' '.$sequence."\n";
            $this->hash[$sequence] = true;
            $this->results[$this->count] = array(0=>$value, 1=>$sequence, 2=>$rcount);
            $this->count++; 
        }
        
        if ($this->count > $this->threshold) {
            $this->sortResults();
            $this->count=10000;
        }
        //echo count($this->results)." , ".count($this->hash)."\n";
    }

    function get($index){
        return $this->results[$index];
    }

    function count() {
        return $this->count;
    }

    function sortResults() {
        if ($this->count <2) return;
        $sorted = false;
        while ($sorted==false) {
            $sorted = true;
            for ($i=0;$i<$this->count-1;$i++) {
                // determine how much apart from ideal resistor value we are, ignore sign
                $a = $this->v - $this->results[$i][0]; $a = ($a<0) ? (0-$a) : $a;
                $b = $this->v - $this->results[$i+1][0]; $b = ($b<0) ? (0-$b) : $b;
                $flip = false;
                if ($a>$b) {
                        $temp = $this->results[$i];
                        $this->results[$i] = $this->results[$i+1];
                        $this->results[$i+1] = $temp;
                        $sorted = false;
                }
                if (($this->results[$i][0] == $this->results[$i+1][0]) && 
                    ($this->results[$i][2] > $this->results[$i+1][2])) {
                        $temp = $this->results[$i];
                        $this->results[$i] = $this->results[$i+1];
                        $this->results[$i+1] = $temp;
                        $sorted = false;                       
                }
                // if ($this->results[$i][0]==$this->results[$i+1][0]) {
                //     $a = $this->results[$i][2];
                //     $b = $this->results[$i+1][2];
                //     if ($a<$b) {
                //         $temp = $this->results[$i];
                //         $this->results[$i] = $this->results[$i+1];
                //         $this->results[$i+1] = $temp;
                //         $sorted = false;
                //     }
                // }
            }
        }

    }
}
?>