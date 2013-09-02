<?php
function FormatPhone($phone)
{
	$phone = preg_replace("/[^0-9]/", "", $phone);

	if(strlen($phone) == 7)
		return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
		
	elseif(strlen($phone) == 10)
		return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);

	elseif(strlen($phone) == 11 )
		return preg_replace("/([0-9])([0-9]{3})([0-9]{3})([0-9]{4})/", "($2) $3-$4", $phone);
	
	else
		return $phone;
}
?>