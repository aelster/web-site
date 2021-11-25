<?php

function Logger() {
    $gDebug = $GLOBALS['gDebug'];
    if( $gDebug == 0 ) return;
    $gDebugInLine = $GLOBALS['gDebugInLine'];
    $gDebugErrorLog = $GLOBALS['gDebugErrorLog'];
    $gDebugWindow = $GLOBALS['gDebugWindow'];
    $gDebugHTML = $GLOBALS['gDebugHTML'];
    
//    $prefix = join('>', $GLOBALS['gFunction'] );
    
    $show_traceback = 0;
    
    $e = new Exception();
    $trace = $e->getTrace();
    $depth = count($trace);
    
    $stack = [];
    
    for( $i = $depth-1; $i>=1; $i--) {
        $stack[] = sprintf("%s(%d)", $trace[$i]['function'], $trace[$i-1]['line']);
    }
    $prefix = implode("->", $stack );
//    $prefix .= sprintf( ">%s(%d)", $x[$depth-1]['function'], $x[$depth-2]['line'] );
//    if( $depth >= 2 ) {
//        $prefix .= sprintf( "(%d)", $trace[$depth-2]['line'] );
//    }
    $strace = $vfn = $vln = array();
 
    if( $show_traceback ) {
        for( $i = 0; $i < $depth; $i++ ) {
            $vfn[] = $trace[$i]['function'];
            $vln[] = $trace[$i]['line'];
        }
        $vfn[] = "Index";
        $vln[] = -1;
        $rvfn = array_reverse($vfn);
        $rvln = array_reverse($vln);
        for( $i = 0; $i < count( $vln); $i++ ) {
            $strace[] = sprintf("%s(%d)", $rvfn[$i], $rvln[$i]);
        }
        $rstrace = array_reverse( $strace );
        
        $tb = join("->", $strace);

        
#        type(x) = " . gettype($x) . "<br>";
#        $v = array_keys($x);
#        echo "array_keys(x) = " . print_r($v,true) . "<br>";
#        foreach( $x as $v ) {
#            printf( "%s(%d)", $v[0]['function'], $v[0]['line'] );
#        }
/*        $obj = array_keys($x[0]);
        for( $i = 0; $i < count($x); $i++ ) {
            $v = array_keys($x[$i]);
            echo "array_keys(x[$i]) = " . print_r($v,true) . "<br>";
            echo "ix:$i<br>" . var_dump($x[$i]);
#            printf( "%s(%d):", $v[3][0], $v[1][0]);
        }
*/    
        
    }

    
    $num_args = func_num_args();
        
    if ($gDebug & $gDebugInLine) {
        $indent = "&nbsp;&nbsp;";
        $indent = "";
        $eol = "<br>";
        echo "<div align=left>";
        echo $prefix . $eol;
        if( $num_args ) { // Implies one or more args
            $arg = func_get_arg(0);
            if( is_string($arg) ) {
                echo $indent . $arg . $eol;
            } else {
                foreach( $arg as $msg ) {
                    echo $indent . $msg . $eol;
                }
            }
        }
        echo "</div>";
    }

    if ($gDebug & $gDebugErrorLog) {
        $indent = "  ";
        $eol = "";
        error_log($prefix . $eol);
        if( $num_args ) { // Implies one or more args
            $arg = func_get_arg(0);
            if( is_string($arg) ) {
                error_log( $indent . $arg . $eol );
            } else {
                foreach( $arg as $msg ) {
                    error_log($indent . $msg . $eol );
                }
            }
        }
    }

    if ($gDebug & $gDebugWindow) {
        $indent = "&nbsp;&nbsp;";
        $eol = "\n";
        $maxlen = 240;

        echo "<script type='text/javascript'>" . $eol;
        echo "if( typeof debug === 'function' ) {" . $eol;
        echo "  debug('$indent$prefix');" . $eol;
        if ($num_args) { // Implies one or more args
            $arg = func_get_arg(0);
            if (is_string($arg)) {
                $str = preg_replace('/\'/', '-', $arg);
                
                while (strlen($str) > $maxlen) {
                    $s = substr($str, 0, $maxlen);
                    echo "  debug('{$indent}{$s}<br>');" . $eol;
                    $str = substr($str, $maxlen);   
                }
                echo "  debug('{$indent}{$str}');" . $eol;
            } else {
                $pre = "";
                foreach ($arg as $msg) {
                    $str = preg_replace('/\'/', '-', $msg);
                    while (strlen($str) > $maxlen) {
                        $j = strrpos($str," ",$maxlen);
                        $s = substr($str, 0, $j);
                        echo "  debug('{$pre}{$s}<br>');" . $eol;
                        $str = substr($str, $j+1);
                        $pre = $indent;
                    }
                    echo "  debug('{$pre}{$str}');" . $eol;
                    $pre = $indent;
                }
            }
        }
        if ($show_traceback) {
            echo "  debug('Tracebk: $tb');" . $eol;
        }
        echo "}" . $eol;
        echo "</script>" . $eol;
    }

    if ($gDebug & $gDebugHTML) {
        $indent = "  ";
        $eol = "\n";
        echo "<!--" . $eol;
        echo $prefix . $eol;
        if( $num_args ) { // Implies one or more args
            $arg = func_get_arg(0);
            if( is_string($arg) ) {
                $str = $arg;
                echo $indent . $str . $eol;
            } else {
                foreach( $arg as $msg ) {
                    echo $indent . $msg . $eol;
                }
            }
        }
        echo "-->" . $eol;
    }
}