<?php
class Base62x {

	# variables

	var $isdebug = false;
	var $i = 0;
	var $codetype = 0; # 0:encode, 1:decode
	const XTAG = 'x'; 
	const ENCD = "-enc";
	const DECD = "-dec";
	const DEBG = "-v";
	const CVTN = "-n";
	const b62x = array('0','1','2','3','4','5','6','7','8','9',
		'A','B','C','D','E','F','G','H','I','J','K','L','M','N',
		'O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b',
		'c','d','e','f','g','h','i','j','k','l','m','n','o','p',
		'q','r','s','t','u','v','w','y','z','1','2','3','x');
	const bpos = 60; # 0-60 chars
	const xpos = 64; # b62x[64] = 'x'
	static $rb62x = array();
	const ascmax = 127;
	const asclist = array('4','5','6','7','8','9', '0',
		'A','B','C','D','E','F','G','H','I','J','K','L','M','N',
		'O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b',
		'c','d','e','f','g','h','i','j','k','l','m','n','o','p',
		'q','r','s','t','u','v','w','y','z'); # 58 
	var $ascidx = array();
	var $ascrlist = array();
	const max_safe_base = 36;
    const base59 = 59; # static $rb62xyz = array(); static $b62xyz = array();
	static $ver = 0.9;
	

	# methods

	# encode, ibase=2,8,10,16,32...
	public static function encode($input, $ibase=null){
		
		$output = null;
		if($input == null || $input == ''){
			return $input;
		}

		$codetype = 0;
		$xtag = self::XTAG;
		$b62x = self::b62x;
		$asclist = self::asclist;
		$bpos = self::bpos;
		$xpos = self::xpos;
		$ascmax = self::ascmax;
		#$max_safe_base = self::max_safe_base;

		$rb62x = self::fillRb62x($b62x, $bpos, $xpos);
		$isNum = false;
		if($ibase > 0){ $isNum = true; }
		if($isNum){
			$output = 0;
			$num_input = self::xx2dec($input, $ibase, $rb62x); 	
			$obase = $xpos;
			$output = self::dec2xx($num_input, $obase, $b62x);
			# why a mediate number format is needed?
		}
		else{
			# string
			$ascidx = array(); $ascrlist = array();
			$inputArr = str_split($input); $inputlen = count($inputArr);
			#if(!isset($ascidx)){ $ascidx = array(); }
			#if(!isset($ascrlist)){ $ascrlist = array(); }
			$setResult = self::setAscii($codetype, $inputArr, $ascidx, $ascmax, $asclist, $ascrlist);
			$asctype = $setResult['asctype'];
			$ascidx = $setResult['ascidx'];
			$ascrlist = $setResult['ascrlist'];

			$op = array();
			$i = 0; $m = 0;
			if($asctype == 1){
				$ixtag = ord($xtag);
				do{
					$inputArr[$i] = ord($inputArr[$i]);
					if($ascidx[$inputArr[$i]] > -1){
						$op[$m] = $xtag;
						$op[++$m] = $ascidx[$inputArr[$i]];	
					}
					else if($inputArr[$i] == $ixtag){
						$op[$m] = $xtag; 
						$op[++$m] = $xtag;
					}
					else{
						$op[$m] = chr($inputArr[$i]);	
					}	
					$m++;
				}
				while(++$i < $inputlen);
				$op[$m] = $xtag; # asctype=1 has a tag 'x' appended
			}
			else{
				$c0 = 0; $c1 = 0; $c2 = 0; $c3 = 0;
				do{
					$remaini = $inputlen - $i;
					$inputArr[$i] = ord($inputArr[$i]);
                    if($remaini > 2){
                        $inputArr[$i+1] = ord($inputArr[$i+1]);
                        $inputArr[$i+2] = ord($inputArr[$i+2]);
                        $c0 = $inputArr[$i] >> 2;
                        $c1 = ((($inputArr[$i] << 6) & 0xff) >> 2) | ($inputArr[$i+1] >> 4);
                        $c2 = ((($inputArr[$i+1] << 4) & 0xff) >> 2) | ($inputArr[$i+2] >> 6);
                        $c3 = (($inputArr[$i+2] << 2) & 0xff) >> 2;
                        if($c0 > $bpos){ $op[$m] = $xtag; $op[++$m] = $b62x[$c0]; }
                        else{ $op[$m] = $b62x[$c0]; } 
                        if($c1 > $bpos){ $op[++$m] = $xtag; $op[++$m] = $b62x[$c1]; }
                        else{ $op[++$m] = $b62x[$c1]; } 
                        if($c2 > $bpos){ $op[++$m] = $xtag; $op[++$m] = $b62x[$c2]; }
                        else{ $op[++$m] = $b62x[$c2]; } 
                        if($c3 > $bpos){ $op[++$m] = $xtag; $op[++$m] = $b62x[$c3]; }
                        else{ $op[++$m] = $b62x[$c3]; } 
                        $i += 2;	 
                    }
                    else if($remaini == 2){
                        $inputArr[$i+1] = ord($inputArr[$i+1]);
                        $c0 = $inputArr[$i] >> 2;
                        $c1 = ((($inputArr[$i] << 6) & 0xff) >> 2) | ($inputArr[$i+1] >> 4);
                        $c2 = (($inputArr[$i+1] << 4) & 0xff) >> 4;
                        if($c0 > $bpos){ $op[$m] = $xtag; $op[++$m] = $b62x[$c0]; }
                        else{ $op[$m] = $b62x[$c0]; } 
                        if($c1 > $bpos){ $op[++$m] = $xtag; $op[++$m] = $b62x[$c1]; }
                        else{ $op[++$m] = $b62x[$c1]; } 
                        if($c2 > $bpos){ $op[++$m] = $xtag; $op[++$m] = $b62x[$c2]; }
                        else{ $op[++$m] = $b62x[$c2]; } 
                        $i += 1; 
                    }
                    else{ // == 1
                        $c0 = $inputArr[$i] >> 2;
                        $c1 = (($inputArr[$i] << 6) & 0xff) >> 6;
                        if($c0 > $bpos){ $op[$m] = $xtag; $op[++$m] = $b62x[$c0]; }
                        else{ $op[$m] = $b62x[$c0]; } 
                        if($c1 > $bpos){ $op[++$m] = $xtag; $op[++$m] = $b62x[$c1]; }
                        else{ $op[++$m] = $b62x[$c1]; }  
                    }
   					$m++;
				}
				while(++$i < $inputlen);
			}
			$output = implode($op);
		}

		return $output;

	}

	# decode, obase=2,8,10,16,32...
	public static function decode($input, $obase=null){
		
		$output = "";
		if($input == null || $input == ''){
			return $input;
		}

		$codetype = 1;
		$xtag = self::XTAG;
		$b62x = self::b62x;
		$asclist = self::asclist;
		$bpos = self::bpos;
		$xpos = self::xpos;
		$ascmax = self::ascmax;
		$rb62x = self::fillRb62x($b62x, $bpos, $xpos);
		#$max_safe_base = self::max_safe_base;
		
		$isNum = false;
		if($obase > 0){ $isNum = true; }
		if($isNum){
			$output = 0;
			$ibase = $xpos;
			$num_input = self::xx2dec($input, $ibase, $rb62x); 	
			$output = self::dec2xx($num_input, $obase, $b62x);
			# why a mediate number format is needed?
		}
		else{
			# string
			$ascidx = array(); $ascrlist = array();
			$inputArr = str_split($input); $inputlen = count($inputArr);
			$setResult = self::setAscii($codetype, $inputArr, $ascidx, $ascmax, $asclist, $ascrlist);
			$asctype = $setResult['asctype'];
			$ascidx = $setResult['ascidx'];
			$ascrlist = $setResult['ascrlist'];
			
			$op = array();
			$i = 0; $m = 0;
			if($asctype == 1){
				$inputlen--;
				do{
					if($inputArr[$i] == $xtag){
						if($inputArr[$i+1] == $xtag){
							$op[$m] = $xtag;
							$i++;
						}
						else{
							$op[$m] = chr($ascrlist[$inputArr[++$i]]);	
						}
					}
					else{
						$op[$m] = $inputArr[$i];	
					}
					$m++;
				}
				while(++$i < $inputlen);
			}
			else{
				$tmpArr = array();
				$bint = array('1'=>1, '2'=>2, '3'=>3);
				do{
					$tmpArr = array(null, null, null, null);
					$remaini = $inputlen - $i; $j = 0;
                    if($remaini > 1){
                        $j = 0;
                        do{
                            if($inputArr[$i] == $xtag){
							    $i++;
                                $tmpArr[$j] = $bpos + $bint[$inputArr[$i]];
                            }
							else{
                                $tmpArr[$j] = $rb62x[$inputArr[$i]];
                            }
                            $i++; $j++;
                        }
                        while($j < 4 && $i < $inputlen);

                        $arr = self::_decodeByLength($tmpArr, $op, $m);
                        $op = $arr[0];
                        $m = $arr[1]; //- deprecated
                    }
                    else{
						error_log(__FILE__.": found illegal base62x input:[".$inputArr[$i]."]. 1608091042.");
                        continue;
                    }
                    $m++;
				}
				while($i < $inputlen);
			}
			$output = implode($op);
		}

		return $output;

	}

	# xx2dec
	public static function xx2dec($inum, $ibase, $ridx){

		$onum = 0;
		$obase = 10; $xtag = self::XTAG; $bpos = self::bpos;
		$safebase = self::max_safe_base;
        $base59 = self::base59; $xpos = self::xpos;
		if($ibase <= $safebase){
			$onum = base_convert($inum, $ibase, $obase);
		}
		else{
            if($ibase > $base59 && $ibase < $xpos){
                # base 60, 61, 62 or sth, reset ridx table
                #$ridx = $self->rb62xyz;
                $ridx_in = array();
                foreach($ridx as $rk=>$rv){
                    $ridx_in[$rk] = $rv;
                }
                $ridx_in['x'] = 59; $ridx_in['y'] = 60; $ridx_in['z'] = 61;
                $ridx= $ridx_in;
            }
			$iArr = str_split($inum);
			$iArr = array_reverse($iArr);
			$arrLen = count($iArr);
			$xnum = 0; $isBase62x = ($ibase==$xpos);
			for($i=0; $i<$arrLen; $i++){
				if($isBase62x && $iArr[$i+1] == $xtag){
					$tmpi = $bpos + $ridx[$iArr[$i]];
					$xnum++;
					$i++;
				}
				else{
					$tmpi = $ridx[$iArr[$i]];	
				}
				if($tmpi >= $ibase){
					error_log(__FILE__.": xxdec found out of radix:$tmpi for base:$ibase.\n");
					$tmpi = $ibase - 1;
				}
				$onum = $onum + $tmpi * pow($ibase, ($i - $xnum));	
				#error_log("\t".__FILE__.": xx2dec ibase:$ibase i:$i c:".$iArr[$i]." tmpi:$tmpi onum:$onum xnum:$xnum");
			}
			if(strpos($onum, 'E') !== false){
				error_log(__FILE__.": Base62x::xx2dec: lost precision due to too large number:[$onum]. consider using bc math. 1610072145.");
				$onum = number_format($onum);	
			}
		}
		#error_log(__FILE__.": xx2dec: in:$inum ibase:$ibase outindec:".$onum);
		return $onum;

	}

	# dec2xx
	public static function dec2xx($inum, $obase, $idx){
	
		$onum = 0;
		$ibase = 10; $xtag = self::XTAG; $bpos = self::bpos;
		$safebase = self::max_safe_base;
        $base59 = self::base59; $xpos = self::xpos;
		if($obase <= $safebase){
			$onum = base_convert($inum, $ibase, $obase);
		}
		else{
            $isBase62x = false;
            if($obase > $base59 && $obase < $xpos){
                #$idx = self::b62xyz;
                $idx_in = array();
                foreach($idx as $ik=>$iv){
                    $idx_in[$ik] = $iv;
                }
                $idx_in[59] = 'x'; $idx_in[60] = 'y'; $idx_in[61] = 'z';
                $idx = $idx_in;
            }
            else if($obase == $xpos){
                $isBase62x = true;
            }
            $maxPos = $bpos;
            if(!$isBase62x){ $maxPos = $bpos + 1; } # cover all 0-61 chars
			$i = 0; $b = 0;
			$oArr = array(); 
			while($inum >= $obase){
				$b = $inum % $obase;
				$inum = floor($inum / $obase);
				if($b <= $maxPos){
					$oArr[$i++] = $idx[$b];
				}
				else{
					$oArr[$i++] = $idx[$b - $bpos];
					$oArr[$i++] = $xtag;
				}
			}
			$b = $inum;
			if($b > 0){
				if($b <= $maxPos){
					$oArr[$i++] = $idx[$b];
				}
				else{
					$oArr[$i++] = $idx[$b - $bpos];
					$oArr[$i++] = $xtag;
				}
			}
			$oArr = array_reverse($oArr);
            #print_r($oArr);
			$onum = implode($oArr);
		}
		#error_log(__FILE__.": dec2xx: inindec:$inum obase:$obase out:".($onum));
		return $onum;

	}

	# inner faciliates

	# fill reverse b62x
	private static function fillRb62x($b62x, $bpos, $xpos){
		$rb62x = array();
		for($i=0; $i<=$xpos; $i++){
			if($i > $bpos && $i< $xpos){
				# omit x1, x2, x3	
			}
			else{
				$rb62x[$b62x[$i]] = $i;	
			}
		} 
		return $rb62x;
	}

	# set ascii type
	private static function setAscii($codetype, $inputArr, $ascidx, $ascmax, $asclist, $ascrlist){
		
		$ret = array();

		$asctype = 0;
		$xtag = self::XTAG;
		$inputlen = count($inputArr);
		if($codetype == 0 && ord($inputArr[0]) <= $ascmax){
			$asctype = 1;
			for($i=1; $i<$inputlen; $i++){
				$tmpi = ord($inputArr[$i]);
				if($tmpi > $ascmax
					|| ($tmpi > 16 && $tmpi < 21) # DC1-4
					|| ($tmpi > 27 && $tmpi < 32)){ # FC, GS, RS, US
					$asctype = 0;
					break;
				}
			}
		}
		else if($codetype == 1 && $inputArr[$inputlen-1] == $xtag){
			$asctype = 1;	
		}
		$ret['asctype'] = $asctype;

		if($asctype == 1){
			for($i=0; $i<=$ascmax; $i++){ $ascidx[$i] = -1; }		
			$idxi = 0;
			$bgnArr = array(0, 21, 32, 58, 91, 123);
			$endArr = array(17, 28, 48, 65, 97, $ascmax+1);
			foreach($bgnArr as $k=>$v){
				for($i=$v; $i<$endArr[$k]; $i++){
					$ascidx[$i] = $asclist[$idxi]; 
					$ascrlist[$asclist[$idxi]] = $i;
					$idxi++;
				}
			}
		}

		$ret['ascidx'] = $ascidx;
		$ret['ascrlist'] = $ascrlist;

		return $ret;

	}

	//- decode with x1, x2, x3
	//- Mon Nov 28 17:47:45 CST 2016
	private static function _decodeByLength($tmpArr, $op, $m){
	    $rtn = $op;
		$c0 = 0; $c1 = 0; $c2 = 0;
	    if($tmpArr[3] !== null){
	        $c0 = $tmpArr[0] << 2 | $tmpArr[1] >> 4;
	        $c1 = (($tmpArr[1] << 4) & 0xf0) | ($tmpArr[2] >> 2);
	        $c2 = (($tmpArr[2] << 6) & 0xff) | $tmpArr[3];
	        $op[$m] = chr($c0);
	        $op[++$m] = chr($c1);
	        $op[++$m] = chr($c2);
	    }
	    else if($tmpArr[2] !== null){
	        $c0 = $tmpArr[0] << 2 | $tmpArr[1] >> 4;
	        $c1 = (($tmpArr[1] << 4) & 0xf0) | $tmpArr[2];
	        $op[$m] = chr($c0);
	        $op[++$m] = chr($c1);
	    }
	    else if($tmpArr[1] !== null){
	        $c0 = $tmpArr[0] << 2 | $tmpArr[1];
	        $op[$m] = chr($c0);
	    }
	    else{
	        $c0 = $tmpArr[0];
	        $op[$m] = chr($c0);
	    }
	    return array($rtn=$op, $m);
	}
	
}

?>
