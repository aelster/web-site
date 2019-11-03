<?php

function Logger() {
    $gDebug = $GLOBALS['gDebug'];
    $gDebugInLine = $GLOBALS['gDebugInLine'];
    $gDebugErrorLog = $GLOBALS['gDebugErrorLog'];
    $gDebugWindow = $GLOBALS['gDebugWindow'];
    $gDebugHTML = $GLOBALS['gDebugHTML'];
    
    $prefix = join('>', $GLOBALS['gFunction'] );
    
    $show_traceback = 0;
    
    $e = new Exception();
    $x = $e->getTrace();
    $depth = count($x);

//    $prefix .= sprintf( ">%s(%d)", $x[$depth-1]['function'], $x[$depth-2]['line'] );
    if( $depth >= 2 ) {
        $prefix .= sprintf( "(%d)", $x[$depth-2]['line'] );
    }
    if( $show_traceback ) {
        echo "type(x) = " . gettype($x) . "<br>";
        $v = array_keys($x);
        echo "array_keys(x) = " . print_r($v,true) . "<br>";
        for( $i = 0; $i < count($x); $i++ ) {
            $v = array_keys($x[$i]);
            echo "array_keys(x[$i]) = " . print_r($v,true) . "<br>";
            var_dump($x[$i]);
        }
    }
    
    $num_args = func_num_args();
        
    if ($gDebug & $gDebugInLine) {
        $indent = "&nbsp;&nbsp;";
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
        $indent = "  ";
        $eol = "\n";

        echo "<script type='text/javascript'>" . $eol;
        echo "if( typeof debug === 'function' ) {" . $eol;
        echo "  debug('$indent$prefix');" . $eol;
        if( $num_args ) { // Implies one or more args
            $arg = func_get_arg(0);
            if( is_string($arg) ) {
                echo "  debug('$indent$arg');" . $eol;
            } else {
                foreach( $arg as $msg ) {
                    echo "  debug('$indent$msg');" . $eol;
                }
            }
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
                echo $indent . $arg . $eol;
            } else {
                foreach( $arg as $msg ) {
                    echo $indent . $msg . $eol;
                }
            }
        }
        echo "-->" . $eol;
    }
}