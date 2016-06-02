<?php

/*
    IP update for devices using an AWS hosted Asterisk instance
    author: jeff howe  date: 06/02/2016
    this script file is put on the Asterisk host server and should be run from a cron
*/

// load sip.conf for reading
$sipconf = file_get_contents('/etc/asterisk/sip.conf');

// setup the pattern to read the line with the key 'permit' in $sipconf
$pattern = "/^.*permit.*$/m";

// execute preg_match to hunt to lines matching the $pattern
$matches = array();
preg_match($pattern, $sipconf, $matches);

//$permitkeyval = $matches;
$permitkeyval =  implode($matches);
//echo $oldip;

// check if empty
if (!empty($permitkeyval)) {
    //parse permit value (existing ip address in sip.conf to use for comparison)
    preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $permitkeyval, $oldip); 
    //echo $oldip[0]."\n"; 

    // get ip address of remote office
    $newip = gethostbyname('myoffice.dyndns.net');

    // compare the $oldip with the polled ip ($newip) and see if they are the same
    if ($oldip[0] == $newip) {
        // ip address has not changed, exit script
        exit();
     }else{
        // ip address has changed, update asterisk and aws security group with new ip address

        // update ip in sip.conf
        $file = '/etc/asterisk/sip.conf';
        file_put_contents($file,str_replace('permit='.$oldip[0],'permit='.$newip,file_get_contents($file)));

      	// update AWS: add the new ip address to the AWS security group for sip, ssh access
      	exec("aws ec2 authorize-security-group-ingress --group-id sg-yoursgid --protocol tcp --port 22 --cidr $newip/32");
      	exec("aws ec2 authorize-security-group-ingress --group-id sg-yoursgid --protocol udp --port 5060-5064 --cidr $newip/32");

	      // revoke old ip's access in AWS
      	exec("aws ec2 revoke-security-group-ingress --group-id sg-yoursgid --protocol tcp --port 22 --cidr $oldip[0]/32");
      	exec("aws ec2 revoke-security-group-ingress --group-id sg-yoursgid --protocol udp --port 5060-5064 --cidr $oldip[0]/32");

        // reload asterisk
        exec('asterisk -rx "sip reload"');

        exit();

    }

}else{
    //the key 'permit' in sip.conf is missing, or sip.conf file could not be read, exit
    exit();
}
