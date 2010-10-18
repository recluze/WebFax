#!/usr/bin/php
<?php

// set some global variables here 
$SUCCESS_STATUS = "FAX_SUCCESS"; 
$FROM_EMAIL = "recluze@gmail.com"; 
$FROM_NAME = "VoipOWOWStick.net"; 
$CONTACT_EMAIL = "toqeer83@gmail.com"; 

if ($argc != 7) {
 echo "Usage faxnotify.php messagetype email destination timestamp status_string pages \n ";
 echo "message type can be one of INIT or NOTIFY "; 
}
echo "Starting faxnotify.php \n"; 


// setting up  
$messtype   = $argv[1];
$email      = $argv[2];
$dest       = $argv[3]; 
$timestamp  = $argv[4]; 
$status     = $argv[5];
$numpages   = $argv[6];

$headers = "From:  $FROM_NAME <$FROM_EMAIL>";

// end setting up 

if ($messtype == "INIT") { // SendFAX called successfully in the dialplan... 
	$emailSubject = "Your fax to $dest has been initiated"; 
	$notice = "Your fax to $dest sent on $timestamp has been initiated. You will get a notification " . 
			  "email when the transmission is complete. "; 
    $emailBody = "Hi!  \n\n" .  $notice . " \n\n " . 
				 "If you have any queries, please contact us at:  $CONTACT_EMAIL"; 
	mail($email, $emailSubject, $emailBody, $headers); 
}
else {  // meaning $messtype = "NOTIFY" ... sending of fax is complete. Need to check if SUCCEEDED
	$tech_details = "------------------------------ \n". 
	                "DESTINATION = $dest            \n". 
					"TIMESTAMP = $timestamp         \n". 
					"FAXOPTS_STATUSSTRING = $status \n". 
					"NUM_PAGES = $numpages          \n". 
					"------------------------------ \n"; 
					
					
	echo "Sending fax notification email to: $email from $FROM_EMAIL \n";
	
	if($status == $SUCCESS_STATUS) { 
	  $emailSubject = "Your fax to $dest was delivered successfully"; 
	  $notice = "This is an automated response to let you know that your fax to " .
				"$dest sent on $timestamp was delivered successfully. \n";
	} else {
	  $emailSubject = "Your fax to $dest could not be sent"; 
	  $notice = "This is an automated response to let you know that your fax to " .
				"$dest sent on $timestamp could not be delivered. \n";
	}
	
	$emailBody = "Hi!  \n\n" .  $notice . "\n\n" . $tech_details . " \n\n " . 
				 "If you have any queries, please contact us at:  $CONTACT_EMAIL"; 
				 
	// echo $emailSubject . "\n"; 
	// echo $emailBody . "\n";
	
	// mail 
	mail($email, $emailSubject, $emailBody, $headers ); 
	// exec("echo $email $timestamp $emailSubject >> /var/log/asterisk/webfax.log"); 
	// exec("echo $emailBody >> /var/log/asterisk/webfax.log"); 
	// exec("echo -------------------------------- >> /var/log/asterisk/webfax.log"); 
} 
?>
