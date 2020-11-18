|--+----|----+----|----+----|----+----|----+----|----+----|----+----|----+---|
                  Southwest Florida Water Management District
                               2379 Broad Street
                          Brooksville, Florida 34604 (USA)
                              Phone: 352.796.7211
                   8:00am-5:00pm (US-Eastern), Monday-Friday



Updated: 03-10-2010

Here is an "At-A-Glance" summary of the limitations and restrictions for using
our anonymous FTP server. Detailed explanations of the limitations and 
restrictions for using our anonymous FTP server are listed further below.

Limitations
-----------
  - Currently, there is no limit to the amount of data transferred per 
    connection session
  - 265 gigabytes total disk space available in /pub/incoming 
  - 265 gigabytes total disk space available in /pub/outgoing 
  - 7 day life span for files placed on the FTP server 
  - Files on this FTP server are *NOT* backed up

Restrictions
------------
  - ALL connections and commands to our FTP server are logged *AND* monitored 
  - Reverse TCP/IP address look-ups are performed. Access is not allowed 
    from improperly configured networks 
  - FTP access via a web browser is not allowed. No ftp:// in the URL unless
    your browser is properly configured to respond with your correct and 
    valid e-mail address for the anonymous password
  - Canned or default password responses from FTP clients are not allowed.
    Your FTP client must be properly configured to respond with your 
    correct and valid e-mail address for the anonymous password
  - Cannot create sub-directories under /pub/incoming or /pub/outgoing 
  - Cannot delete, rename, or overwrite, nor change the ownership or 
    permissions on files placed in public directories
  - File names can only contain the characters A-Z, a-z, 0-9, a 
    period ( . ), a dash ( - ), or an underscore ( _ ) 
  - File names cannot begin with a dash, a period, or an underscore, 
    nor contain any spaces or special characters in the file name 

When sending multiple files to or from our anonymous FTP server, consider
using a Windows utility such as PKZip, WinZip, or WinRAR, or the UNIX tar, 
compress, or gzip commands to "bundle" everything up in a single file. 

Using utilities and commands such as these make for much easier file 
transfers, both for the sender and the recipient. You're moving only one 
file rather than having to keep track of multiple files. This also saves 
space and reduces transfer times as all these utilities perform some form 
of data compression.

Problems with or specific questions about our anonymous FTP server can be 
directed to our Help Desk at:
  - 352.796.7211, ext. 4008
  - help.desk@swfwmd.state.fl.us

A more detailed "How To FTP" guide explaining everything in this README 
file, including examples of how to upload and download files from our
anonymous FTP server is available online at: 
http://ftp.swfwmd.state.fl.us/HowToFTP/how_to_ftp.html

Are you only downloading something from our anonymous FTP server? You can
use an internet browser to navigate the folder structure and retrieve the
desired files. Browse to: http://ftp.swfwmd.state.fl.us/

Address technical questions regarding our anonymous FTP server configuration
or network configuration to: sysadmin@swfwmd.state.fl.us






---------------------
Limitations Explained
---------------------
The total disk space available in each  /pub/incoming  and  /pub/outgoing 
directories is 265 gigabytes, ample space to handle most types of Powerpoint, 
Excel, PDF, ZIP, or image files. Of course, files that are already stored 
in these directories subtract from the total capacity in each directory.

Files placed in either  /pub/incoming  or  /pub/outgoing  have a life span 
on the server of seven days before being automatically deleted. The Systems 
Administrator reserves the right to remove files prior to the seven-day 
life span if it's found that the offending files are causing problems for 
other users. For example, an extremely large file would be removed from the 
server if it were limiting the disk space available for other file transfers.

As files on our anonymous FTP server are transient in nature and can be
recreated or reloaded from the original source files, it's important to note:
NO BACKUPS OF THE DATA DIRECTORIES ON THIS SERVER ARE PERFORMED.


----------------------
Restrictions Explained
----------------------
Since our anonymous FTP server is connected to the internet, it is 
accessible by anyone in the world with only minimal authentication. Because 
of this, several restrictions have been put in place to ensure the server 
is used for legitimate District business, and to safeguard the data stored 
on it. Also, these restrictions minimize the occurrence of a malicious 
hacker attack on our server or to the data stored on the server.

Operational restrictions in place on the District's anonymous FTP 
server are: 

Reverse TCP/IP address lookups are performed. A connection cannot be made 
to our FTP server if the (DNS) domain name server for the incoming computer 
system is not properly configured to perform reverse address mapping ... that 
is: IP_network_address-to-host_name lookups -- also know as DNS "PTR" records,
or reverse DNS lookup.  This prevents someone from connecting a computer to 
the internet, making up a network address, and accessing our FTP server. A 
legitimate user would generally be accessing our server from a properly 
configured and managed network.

Browser access (entering an URL beginning with ftp:// ) to the anonymous 
FTP server directories is not allowed unless it is properly configured to 
provide your valid e-mail address to the FTP server's password challenge. By 
default, web browsers and FTP client utilities typically provide a "canned 
response" to the password authentication challenge of an anonymous FTP server. 
There is no way to tell from this "canned response" who is using our server.
Your valid e-mail address is required should it be necessary to contact you 
regarding problems or issues with your FTP transfer.

All connections and FTP commands are logged and monitored to ensure proper 
use of the server.

Anonymous users cannot create sub-directories under the public FTP 
directories /pub/incoming or /pub/outgoing. This prevents someone from 
hiding files by burying them deep within multi-level subdirectories.

Once a file is placed on our FTP server, it cannot be deleted, renamed, or 
overwritten, nor can the ownership or permissions be changed on the file. 
This prevents someone from overwriting a legitimate file with a file 
containing a virus, changed or incorrect data. Imagine being a District 
customer, downloading a file you think is the one you're expecting, only 
to have it be an infected trojan file placed on our server by a hacker. If
you need to upload a file with the same name as a file already in the 
directory, alter the name of the second file so it's unique and different
from the first file name.

Files uploaded to the FTP server must have names made of only the 
characters A-Z, a-z, 0-9, a period ( . ), dash ( - ), or underscore ( _ ), 
and may not begin with a dash or period, nor contain any spaces in the file 
name. By restricting the characters in the file name, we prevent someone 
from placing files on our server that could hide themselves or cause other 
problems for the unsuspecting user of our FTP server. 

