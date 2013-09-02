<?php

global $mail_admin;
global $mail_enabled;
global $mail_servers;

function MyMail( $message ) {
	$trace = $GLOBALS['gTrace'];
	if( $trace ) {
		$GLOBALS['gFunction'][] = "MyMail()";
		Logger();
	}
	$debug = $GLOBALS[ 'gDebug' ];
	static $ms = array();
	static $mailer;
	static $logger;
  	
	if( empty( $GLOBL['mail_enabled'] ) ) {
		echo "** Mail service for this application is not enabled, update your local settings<br>";
		exit;
	}
	if( empty( $ms ) ) {
		if( count( $GLOBALS['mail_servers'] ) == 0 ) {
			echo "** no Mail Servers defined, update you local settings<br>";
			exit;
		}
		$ms = array_shift( $GLOBALS['mail_servers'] );
		$ms['connected'] = 0;
	}
	
	if( ! $ms['connected'] ) {
		if( $debug ) {
			printf( "MyMail:  Connecting to server: [%s], port: [%d]<br>",
					 $ms['server'], $ms['port'] );
		}
		$transport = Swift_SmtpTransport::newInstance();
		$transport->setHost($ms['server']);
		$transport->setPort($ms['port']);
		if( isset( $ms['encr'] ) ) $transport->setEncryption($ms['encr']);
		if( isset( $ms['user'] ) ) { # a login is required
			$transport->setUsername( $ms['user'] );
			$transport->setPassword( $ms['pass'] );
		}
		$mailer = Swift_Mailer::newInstance( $transport );
		if( $debug ) $logger = new Swift_Plugins_Loggers_EchoLogger();
		$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100,30));
		if( $debug ) $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
		$ms['connected'] = 1;
	} else {
		if( $debug) { printf( "MyMail:  Already connected to %s<br>", $ms['server']); }
	}

	if( $debug ) {
		echo "MyMail> before send<br>";
		echo "logger #1:";
#		echo $logger->dump();
	}
		
	$result = $mailer->send($message,$failures);

	if( $debug ) {
		echo "MyMail> after send<br>";
		echo "logger #2:";
#		echo $logger->dump();	
	}
	
	echo "result: $result<br>";	
	if( ! $result ) 
	{
		if($debug) echo "error<br>";
		if( ! empty( $failures ) ) {
			$text = array();
			foreach( $failures as $key => $val ) {
				$text[] = sprintf( "%s -> %s", $key, $val );	
			}
			$body = join( "<br>", $text );
			$msg = Swift_Message::newInstance();
			$msg->setTo( $GLOBALS['mail_admin'] );
			$msg->setSubject('Email failures' );
			$msg->setBody($body,'text/html');
			$msg->setFrom( $GLOBALS['mail_admin'] );
			$mailer->send($msg);
		}
	} else {
		if( $debug ) echo "sent ok<br>";
	}
	
	if( $trace ) array_pop( $GLOBALS['gFunction'] );
}
?>
