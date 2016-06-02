
     asterisk-remote-ip-update README                                    
     author:  jeff howe                              
     date:    06/02/2016                             

PROBLEM:  When an ISP serving an office changes the IP address, the office device registrations to their AWS based asterisk host breaks.
In other words, the phones don't ring!  When this happens, the 'permit' fields in sip.conf for the devices need to be updated and
the server's AWS security group also needs an update to authorize SIP packets from the new IP.

SOLUTION:  This script, with two simple modifications to include the remote office dyndns host name (or other dynamic host), and the AWS security group id of the Asterisk host instance.

PREREQUISITES:  A vanilla Asterisk (not FreePBX or other gui based) installation on AWS.  Installation of the tool 'aws-cli' on the asterisk host, a dynamic dns account to poll for ip changes, and the AWS sercurity group ip.

What this script does:  Running from a cron, this script will periodically poll the IP address of the remote office and compare it with the device information in the sip.conf account.  If it hasn't changed, the script exits.  If the IP has changed, the script will update the sip.conf file with the new
IP address information in the 'permit' fields.  Update the security group to grant the new IP access to the SIP ports, then reload asterisk for the changes to take effect.

