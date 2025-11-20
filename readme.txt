The .htaccess file needs be placed in the root (next to index.php) and has to look like this:

SetEnv TENOR_API_KEY "credentials-here"
SetEnv DB_HOST "credentials-here"
SetEnv DB_NAME "credentials-here"
SetEnv DB_USER "credentials-here"
SetEnv DB_PASS "credentials-here"
SetEnv DB_CHARSET "utf8"
        
                     or whatever charset you want...
            
            
For local development, make sure the localhost credentials in includes/models/Database.php are valid. Make sure to comment the SetEnv credentials in .htaccess file when you work locally, or else you will work on your LIVE database. 

You'be been warned!

                     Stay safe, don't be evil.

                     Daniel Pincu 
                     mail: echo @ danielpincu.com