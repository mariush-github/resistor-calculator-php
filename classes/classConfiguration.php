<?php

class Configuration {
    private $settings;
    function __construct() {
        $this->loadDefaults();
    }
    function loadDefaults() {
        $this->settings = array('min'=>0, 'max'=>0,'mix'=>0,'tol'=>1,'e'=>24,'results'=>25,'count'=>3,'value'=>0,'group'=>1);
    }
    function get($value) {
        return $this->settings[$value];
    }
    private function helper_validNumber($value){
        $text = ''.$value;
        if ($text=='') return FALSE;
        $count = 0;
        for ($i=0;$i<strlen($value);$i++) {
            $c = substr($value,$i,1);
            if ($c=='.') $count++;
            if ($c!='.') {
                if (ctype_digit($c)==FALSE) return FALSE;
            }
        }
        if ($count>1) return FALSE;
        return TRUE;
    }
    function loadParameters() {
        $longOpts = array('min:','max:','e:','count:','mix:','results:','tol:','value:','group:');

        $cmdParameters = getopt('',$longOpts);
        
        foreach ($cmdParameters as $key=>$value) {
            //echo $key."\n";
            if ($this->helper_validNumber($value)==TRUE) {
                $this->settings[strtolower($key)] = $value;
                //echo $value."\n";
            }
        }
    }

    function validateParameters() {
        if ($this->settings['tol']< 0.01) $this->settings['tol'] = 0.01;
        if ($this->settings['tol']>10.00) $this->settings['tol'] = 10;
        if ($this->settings['results']< 10) $this->settings['results'] = 10;
        if ($this->settings['results']>100) $this->settings['results'] = 100;
        if ($this->settings['value']<   0.01) $this->settings['value'] = 0;
        if ($this->settings['value']>1000000) $this->settings['value'] = 0;
        if (($this->settings['mix']!=0) && ($this->settings['mix']!=1)) $this->settings['mix'] = 0;
        if (($this->settings['group']!=0) && ($this->settings['group']!=1)) $this->settings['group'] = 1;
        if ($this->settings['count']<2) $this->settings['count'] = 2;
        if ($this->settings['count']>4) $this->settings['count'] = 4;
        $e = $this->settings['e'];
        if (($e!=3) && ($e!=6) && ($e!=12) && ($e!=24) && ($e!=48) && ($e!=96) && ($e!=192)) $this->settings['e']=24;
        if ($this->settings['min']<   0.01) $this->settings['min'] = 0;
        if ($this->settings['min']>1000000) $this->settings['min'] = 0;
        if ($this->settings['max']<   0.01) $this->settings['max'] = 0;
        if ($this->settings['max']>1000000) $this->settings['max'] = 0;
        $e = $this->settings['e'];
        $value_min = round($this->settings['value']/100,2); if ($value_min <0.01) $value_min = 0.01;
        $value_max = round($this->settings['value']*100,2); if ($value_max > 1000000) $value_max = 1000000;
        if ($this->settings['min']==0) {
            if ($e==192)  $this->settings['min'] =  $value_min;
            if ($e==96)  $this->settings['min'] =  ($value_min<100) ? $value_min : 100;
            if ($e==48)  $this->settings['min'] =  ($value_min<100) ? $value_min : 100;
            if ($e==24)  $this->settings['min'] =  ($value_min<10 ) ? $value_min : 10;
            if ($e==12)  $this->settings['min'] =  ($value_min<1  ) ? $value_min : 1;
            if ($e <12)  $this->settings['min'] =  ($value_min<0.1  ) ? $value_min : 0.1;
        }
        if ($this->settings['max']==0) {
            if (($e==192) || ($e==96)) $this->settings['max'] =  $value_max;
            if ($e==48)  $this->settings['max'] =  ($value_max>100000) ? $value_max : 100000;
            if ($e==24)  $this->settings['max'] =  ($value_max>100000 ) ? $value_max : 100000;
            if ($e==12)  $this->settings['max'] =  ($value_max>470000 ) ? $value_max : 470000;
            if ($e <12)  $this->settings['max'] =  ($value_max>820000 ) ? $value_max : 820000;
        }
    }
    function displayParameters() {
        echo "Configuration:\n    range=e".$this->settings['e'].', min='.$this->settings['min'].', max='.$this->settings['max'].', count='.$this->settings['count'].', mix='.$this->settings['mix'].', value='.$this->settings['value']."\n";
    }
    function displayHelp() {
        
            die("
Description: Generates valid combinations of resistors in series or parallel based on entered parameters.\n

Required: 

--value [value]\t : desired resistor value

Optional: 

--min [value]\t\t : minimum resistor value allowed (d=auto,value/100 min=0.01)
--max [value]\t\t : maximum resistor value allowed (d=auto,value*100 m=1000000)
--e [value]\t\t : use resistors defined in this range, AND previous ranges.
--count [value]\t\t : maximum resistors to use (d=3, m=4)
--mix [0,1]\t\t : enable groups of resistors in series or parallel (slow)
--tol [value]\t : tolerance, max. drift from value allowed +/- n% (d=1, m=10)
--results [value]\t : maximum results to show (d=25, m=100)
--group [0,1] : group results by number of resistors, from least to most (d=1)

Limitations: 
* maximum 256 unique resistors (64 when count=4). Use --min and --max to reduce larger ranges. 
* E96, E48 : maximum 3 resistors
* If the range entered and min-max parameters produce 256+ values, 256 resistor values closest to desired value are used.
");
        
    }
}

?>