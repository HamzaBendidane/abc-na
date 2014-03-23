<VirtualHost *:80>
        ServerName ws.neighbapp.com
        ServerAlias media.neighbapp.com ws1.neighbapp.com ws2.neighbapp.com ws3.neighbapp.com
        DocumentRoot /home/neighbapp.com/www/public

        SetEnv APPLICATION_ENV "prod"
        SetEnv PLATFORM "prod"
		setEnv LOG_PATH "/home/logs/"
		
        <Directory /home/neighbapp.com/www/public>
            DirectoryIndex index.php
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>
        <Location />
                RewriteEngine On
                RewriteCond %{REQUEST_FILENAME} -s [OR]
                RewriteCond %{REQUEST_FILENAME} -l [OR]
                RewriteCond %{REQUEST_FILENAME} -d
                RewriteRule ^.*$ - [NC,L]
                RewriteRule ^.*$ /index.php [NC,L]
        </Location>
		
		ScriptAlias /cgi-bin/ /usr/lib/cgi-bin/
        <Directory "/usr/lib/cgi-bin">
                AllowOverride None
                Options +ExecCGI -MultiViews +SymLinksIfOwnerMatch
                Order allow,deny
                Allow from all
        </Directory>
		ErrorLog /var/log/apache2/newapps-error.log

        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel warn

        LogFormat "%h %l %u %t \"%r\" %>s %b" combined

        CustomLog /var/log/apache2/newapps-access.log combined

        ServerSignature Off
        
     </VirtualHost>
