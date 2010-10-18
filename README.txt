============================================================================
Web Fax for Asterisk php script 
----------------------------------------------------------------------------
- written by recluze (http://csrdu.org/nauman) 
- queries, comments to: nauman@csrdu.org or recluze@gmail.com 
============================================================================

Redistribution allowed provided this notice remains visible at the top. 
Released under GPLv3. 


Installation instructions: 
    0. Make sure you have Fax for Asterisk (or Free Fax for Asterisk) installed. 
	   See [http://downloads.digium.com/pub/telephony/fax/README] 
	   for details on that. Also make sure you have the FAX module installed 
	   in FreePBX and configured to send out faxes. You will also need a service 
	   provided (ITSP) that supports T.38 faxing. 

    1. Extract all Web Fax files to somewhere on your web server's htdocs. If you're 
	   running apache as user asterisk, you need to chown the Web Fax files. 
	
	>   chown asterisk:asterisk <wwwroot>/webfax -R 
	
	2. If you're running asterisk as the user 'asterisk', chown the faxnotify.php script: 
	    
	>   chown asterisk:asterisk faxnotify.php 
	   
	3. Make faxnotify.php executable. It will be called from the dialplan from 
	   within asterisk: 
	
	>   chmod +x faxnotify.php 
	   
	4. Move faxnotify.php to $ASTERISDIR/bin/faxnotify.php 
	
	>   mv /var/www/html/webfax/faxnotify.php /var/lib/asterisk/bin/faxnotify.php 

    5. Create a dialplan with [outboundfax] in extensions_additional.conf with the
	   following content: 
	
		[outboundfax]
		; exten => s,1,NoOp(send a fax)
		exten => s,1,Set(FAXOPT(filename)=${FAXFILE})
		exten => s,n,Set(FAXOPT(ecm)=yes)
		exten => s,n,Set(FAXOPT(headerinfo)=${FAXHEADER})
		exten => s,n,Set(FAXOPT(localstationid)=${LOCALID})
		exten => s,n,Set(FAXOPT(maxrate)=14400)
		exten => s,n,Set(FAXOPT(minrate)=2400)
		exten => s,n,SendFAX(${FAXFILE},d)
		exten => s,n,System(${ASTVARLIBDIR}/bin/faxnotify.php INIT "${EMAIL}" "${DESTINATION}" "${TIMESTAMP}" "NO_STATUS" "NO_PAGES")
		exten => h,1,NoOp(FAXOPT(ecm) : ${FAXOPT(ecm)})
		exten => h,n,NoOp(FaxStatus : ${FAXSTATUS})
		exten => h,n,NoOp(FaxStatusString : ${FAXSTATUSSTRING})
		exten => h,n,NoOp(FaxError : ${FAXERROR})
		exten => h,n,NoOp(RemoteStationID : ${REMOTESTATIONID})
		exten => h,n,NoOp(FaxPages : ${FAXPAGES})
		exten => h,n,NoOp(FaxBitRate : ${FAXBITRATE})
		exten => h,n,NoOp(FaxResolution : ${FAXRESOLUTION})
		exten => h,n,System(${ASTVARLIBDIR}/bin/faxnotify.php NOTIFY "${EMAIL}" "${DESTINATION}" "${TIMESTAMP}" "${FAXSTATUSSTRING}" "${FAXPAGES}")
		; end of outboundfax context

	(You can also use the following command to add the dialplan directly to extensions_additional.conf 
	[not yet implemented] 
	
	>  chmod +x insert_dialplan.sh 
	>  ./insert_dialplan.sh 
	
Usage: 

    1. Point your browser to sendfaxform.html and see the magic happen. 