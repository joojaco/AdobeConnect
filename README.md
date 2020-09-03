# AdobeConnect
The files in this folder are PHP scripts that allow you to make server-side API calls to Adobe Connect.

Adobe Connect API calls do not work with proxy or JavaScript fetch() methods, because you need to extract the header cookie from the INITIAL Adobe Connect API response. You'll need a web server to run these. I use Apache, I would assume it works on Nginix but I haven't tested these on that web server.

Do not remove the SSL from the URLs in these scripts. Also recommend additional security layers, including a VPN when running these.

