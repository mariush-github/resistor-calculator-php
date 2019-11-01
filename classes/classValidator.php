<?php

class Validator {
    private $res;
    private $resP;
    private $resC;
    private $results;
    private $v_min;
    private $v_max;
    private $v;
    private $tol;
    private $mix;
    private $count_max;

    function __construct(&$cfg,&$res,&$results) {
        $this->results = $results;
        $this->res = array();
        $this->resP = array();
        $this->resC = $res->count();
        if ($this->resC>256) $this->resC = 256;
        if ($this->resC>0) {
            for ($i=0;$i<$this->resC;$i++) {
                $this->res[$i] = $res->get($i);
                $this->resP[$i] = 1/$this->res[$i];
            }
        }
        //echo $cfg->get('value')."\n".$res->count()."\n";
        $this->count_max = $cfg->get('count');
        $this->mix = $cfg->get('mix');
        $this->tol = $cfg->get('tol');
        $this->v = $cfg->get('value');
        $this->v_min  = $this->v - ($this->v/100)*$this->tol;
        $this->v_max  = $this->v + ($this->v/100)*$this->tol;    
    }

    function checkCombination($combination) {
        //var_dump($combination);
        $modi = array();
        $groups = count($combination);
        // don't add combinations where one resistor is exact desired value
        for ($i=0;$i<$groups;$i++) {
            if ($this->res[$combination[$i]]==$this->v) return;
        }
        // sum or parallels of the chain
        $hashs = '';
        $hashp = '';
        $rsum = 0;
        $rpar = 0;
        for ($i=0;$i<$groups;$i++) {
            $rsum = $rsum + $this->res[$combination[$i]];
            $hashs .= ''.$this->res[$combination[$i]].' + ';
            $rpar = $rpar + $this->resP[$combination[$i]];
            $hashp .= ''.$this->res[$combination[$i]].' | '; 
 
        }
        $rpar = round(1/$rpar,3);
        if (($rsum>$this->v_min) && ($rsum<$this->v_max)) $this->results->add(trim($hashs,'+ '),$rsum,$groups);
        if (($rpar>$this->v_min) && ($rpar<$this->v_max)) $this->results->add(trim($hashp,'| '),$rpar,$groups); 

        // same but combining parallel and series
        if ($this->mix==1) {
            $modi = array();
            if ($groups == $this->count_max) return ; // ex. 3 resistors, max 3 res. chosen, can't mix
            if ($groups==2) {
                if ($this->count_max==3) $modi = array( array(1,2), array(2,1));
                if ($this->count_max==4) $modi = array( array(1,2), array(1,3), array(2,1), array(3,1));
            }
            if ($groups==3) {
                if ($this->count_max==4) $modi = array( array(1,1,2), array(1,2,1), array(2,1,1));
            }
            if (count($modi)>0) {
                // we have modifiers 
                foreach ($modi as $idx =>$modif) {
                    // series
                    $rcnt = 0;
                    $rsum = 0;
                    $rpar = 0;
                    for ($i=0;$i<$groups;$i++) {
                        $rcnt = $rcnt + $modif[$i];
                        $rsum = $rsum + $this->res[$combination[$i]] / $modif[$i];
                        $rpar = $rpar + 1 / ($this->res[$combination[$i]] * $modif[$i]);
                        //$hash .= (($modif[$i]>1)? $modif[$i].'p' : '').''.$this->res[$combination[$i]].' + '; 
                    }
                    $rsum = round($rsum,3);
                    $rpar = round(1/$rsum,3);
                    //echo $hash.':'.$rsum."\n";
                    $hashs = $this->helper_makeHash($combination,$modif,'s');
                    $hashp = $this->helper_makeHash($combination,$modif,'p');
                    if (($rsum>$this->v_min) && ($rsum<$this->v_max)) $this->results->add(trim($hashs,'+ '),$rsum,$rcnt);
                    if (($rpar>$this->v_min) && ($rpar<$this->v_max)) $this->results->add(trim($hashp,'| '),$rpar,$rcnt);
                }
            }
        }
    }
    // normalize hash /
    private function helper_makeHash($combo,$modi,$mode) {
        $a = $combo;
        $b = $modi;
        $sorted = false;
        while ($sorted==false) {
            $sorted=true;
            for ($i=0;$i<count($combo)-1;$i++) {
                if ($modi[$i]>$modi[$i+1]) {
                    $t = $modi[$i];
                    $modi[$i] = $modi[$i+1];
                    $modi[$i+1] = $t;
                    $t = $combo[$i];
                    $combo[$i] = $combo[$i+1];
                    $combo[$i+1] = $t;
                    $sorted = false;
                }
            }
        }
        $hash = '';
        for ($i=0;$i<count($combo);$i++) {
            if ($modi[$i]>1) {
                $hash .= $modi[$i];
                if ($mode=='s') $hash .= 'p';
                if ($mode=='p') $hash .= 's';
            }
            $hash .= $this->res[$combo[$i]];
            if ($mode=='s') $hash .= ' + ';
            if ($mode=='p') $hash .= ' | ';
        }
        return trim($hash,'+| ');

    }
}
?>