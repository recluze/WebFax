<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- 

      * Web Fax for Asterisk php script written by recluze (http://csrdu.org/nauman) 
      * ----------------------------------------------------------------------------
      * Redistribution allowed provided this notice remains visible at the top. 
      * Released under GPLv3. 
      
      * Form stats with HTML comment FORM_START. 

--> 

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Web FAX for Asterisk</title>

<link href="links/styles.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div class="container">
  <div class="content">
    <h1>Web FAX for Asterisk</h1>
    <p> 
<?php 


// SET CONFIGURATIONS HERE ------------------------------ 
$outbound_route = "@outbound-allroutes"; // include the @ before the route name 
$outboundfax_context = "outboundfax";
$asterisk_spool_folder = "/var/spool/asterisk/outgoing"; 



// END CONFIGURATIONS 


// HELPERS -------------------
   function unique_name($path, $suffix) 
   { 
      $file = $path."/".mt_rand().$suffix; 
      return $file; 
   } 
   // error list 
   $ERROR_CONVERTING_DOCUMENT = 1; 
   $ERROR_CREATING_CALL_FILE = 2; 
   $ERROR_NO_ERROR = 0;
// END HELPERS --------------



// generate a new name for the PDF. 
$input_file_noext = unique_name("/tmp", ""); 
$input_file = $input_file_noext . ".pdf";
$input_file_tif = $input_file_noext . ".tif";
$input_file_doc = $input_file_noext . ".doc";


?>
<!-- initiating document conversion. HTML comment hack to supress all error messages from appearing on the screen 


<?
$error = $ERROR_NO_ERROR;  // no error at beginning

$script_local_path = $_REAL_BASE_DIR = realpath(dirname(__FILE__));


$input_file_orig_name = basename($_FILES['faxFile']['name']); 
$ext = substr($input_file_orig_name, strrpos($input_file_orig_name, '.') + 1);


// check if the file is a ".doc"  file 
// $ext = substr($filename, strrpos($filename, '.') + 1);  
// echo "File extension : " . $ext . " <br /> \n";
if ($ext == "doc")  {
	// copy the file to /tmp/ 
	if(move_uploaded_file($_FILES['faxFile']['tmp_name'], $input_file_doc)) {
		// echo "DOC file uploaded";
		// convert the doc file to PDF file using wvPDF 
		$input_file = $input_file_noext . ".pdf"; 
		
		// $wv_command_output = passthru("touch /root/temp.tmp | sudo /usr/bin/php -f /util/wvPDF.php");
		// need this in /etc/sudoers
		// --> asterisk ALL=(ALL) NOPASSWD: /usr/bin/wvPDF (for whatever the apache user is) 
 
		$wv_command = "sudo /usr/bin/wvPDF $input_file_doc $input_file" ;
		// echo "<br /> executing : ". $wv_command . "<br />\n"; 
		$wv_command_output = system($wv_command, $retval);
		
		// echo $wv_command_output; 
		
		if ($retval != 0) {
			echo "There was an error converting your DOC file to PDF. Try uploading the file again or with an older version of PDF"; 
			$error = $ERROR_CONVERTING_DOCUMENT; 
			$doc_convert_output = $wv_command_output;
			// die();
		}else{ 
			// set the input file type to .pdf now as it's converted
			$input_file_type = "pdf"; 
		}
	} else{
		echo "There was an error uploading the file, please try again!";
	}
} // END DOC file detected 
  
  
// IF it was originally a PDF 
if ($ext == "pdf")  {
	if(move_uploaded_file($_FILES['faxFile']['tmp_name'], $input_file)) {
		$input_file_type = "pdf";
	}else{
		echo "There was an error uploading the file, please try again!";
	}
}
// we should now have a PDF file which we will convert to tif 

if($error == $ERROR_NO_ERROR && $input_file_type == "pdf") {

	// convert the attached PDF to .tif using ghostsccript ... 
	$gs_command = "gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=tiffg3 -sOutputFile=${input_file_tif} -f $input_file " ;
	$gs_command_output = system($gs_command, $retval);
	$doc_convert_output = $gs_command_output;
	
	if ($retval != 0) {
		echo "There was an error converting your PDF file to TIF. Try uploading the file again or with an older version of PDF"; 
		$error = $ERROR_CONVERTING_DOCUMENT; 
		// die();
	}
	else  {
	
		// call the faxout.pl script to create a call file and copy the required files to appropriate directories
		// ------------------------------------------------------------------------------------------------------
		// $script_local_path = $_REAL_BASE_DIR = realpath(dirname(__FILE__));
		
		
		
		$faxHeader = $_POST["faxHeader"];
		$localID = $_POST["localID"];
		$email = $_POST["email"];
		$dest = $_POST["dest"];
		
		//echo "Sending FAX. Debug information:  <br /> \n"; 
		//echo "  -- faxHeader: $faxHeader  <br /> \n"; 
		//echo "  -- localID: $localID <br /> \n"; 
		//echo "  -- email: $email <br /> \n"; 
		//echo "  -- dest: $dest <br /> \n"; 
		
		// ----------------------- PERL SCRIPT ------- NOT USING FORM NOW ------------------------------------------\\
		// setting up the options required by faxout.pl 
		// $faxout_command = $script_local_path . "/faxout.pl"; 
		
		
		// calling faxout.pl now 
		// exec($faxout_command, $faxout_output, $retval); 
		
		// echo $retval . " <br />\n";   // should be 0 for correct output by faxout.pl  
		// echo $faxout_output;
		
		// END call faxout.pl 
		// ------------------------------------------------------------------------------------------------------
		
		// ----------------------- END PERL SCRIPT ------- NOT USING FORM NOW ------------------------------------------\\
		
		// ------------------------------ CREATING CALL FILE AND SENDING THROUGH PHP -------------------------------
		
		$callfile = "Channel: Local/$dest$outbound_route\n" . 
					"MaxRetries: 1\n" . 
					"RetryTime: 60\n" . 
					"WaitTime: 60\n"  . 
					"Archive: yes\n"  . 
					"Context: $outboundfax_context \n"  . 
					"Extension: s\n" . 
					"Priority: 1\n" . 
					"Set: FAXFILE=$input_file_tif\n" . 
					"Set: FAXHEADER=$faxHeader\n" . 
					"Set: TIMESTAMP=" . date("d/m/y : H:i:s",time()) . "\n" .
					"Set: DESTINATION=$dest\n". 
					"Set: LOCALID=$localID\n" . 
					"Set: EMAIL=$email\n";
		
		// echo $callfile; 
		// create the call file in /tmp 
		$callfilename = unique_name("/tmp", ".call"); 
		$f = fopen($callfilename, "w"); 
		fwrite($f, $callfile); 
		fclose($f); 
		
		// move the file to asterisk outgoing spool directory 
		// stopping the call to asterisk for now .. TODO: uncomment before deploying .. 
		rename($callfilename, $asterisk_spool_folder .  "/" . substr($callfilename,4));
	
	//------------------------- END CREATE CALL FILE -----------------------------------------------------------
	} 
}
// if no error, display that notification will be sent. 

?>
END HTML HACK to supress errors appearing on screen. 
-->


<? 

if ($error == $ERROR_NO_ERROR) {
	echo "Your fax sending has been initiated. You should receive an email shortly ". 
	     " when the fax reaches the queue. If you don't receive an email in 10 minutes, ". 
		 " there has been a problem with sending your fax and you should try again.";  	
}else if ($error == $ERROR_CONVERTING_DOCUMENT) {
	echo "<span class='error'>Your fax document could not be converted. Please try again or upload the document ". 
	     " in another format. The error details follow. </span> <br /><br />". 
		 " $doc_convert_output ";  	
}

?>


</p>
  <!-- end .content --></div>
  <!-- end .container --></div>
       <p class="footer-copyrights">Web Fax for Asterisk script provided by CSRDU (<a href="http://www.csrdu.org">http://www.csrdu.org</a>). Released under GPLv3. </p>

</body>
</html>
