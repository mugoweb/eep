<?php
/*
EEP is a command line tool to support developers using ezpublish
Copyright © 2012  Mugo Web
GNU GENERAL PUBLIC LICENSE
Version 3, 29 June 2007
*/

$knowledgeBaseString = <<<EOT
<VirtualHost *:80>
    DirectoryIndex index.php
    DocumentRoot <<<ezroot>>>
    ServerName <<<servername>>>

    LogFormat "%h %V %u %t \"%m %V%U%q %H\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" \"%{Cookie}i\"" special
    CustomLog <<<ezroot>>>/var/log/apache_access.log special
    ErrorLog  <<<ezroot>>>/var/log/apache_error.log.txt

    <Directory "<<<ezroot>>>">
        Allow from all
        Options FollowSymLinks
        AllowOverride None
    </Directory>
    <IfModule mod_php5.c>
        php_admin_flag safe_mode Off
        php_admin_value register_globals 0
        php_value magic_quotes_gpc 0
        php_value magic_quotes_runtime 0
        php_value allow_call_time_pass_reference 0
    </IfModule>
    <IfModule mod_rewrite.c>
        RewriteEngine On
        # strip index.php all the time
        RewriteRule ^/index\.php(.*)$ https://<<<servername>>>$1 [R=301,L]
        # rule for ezoe
        RewriteRule ^/var/[^/]+/cache/public/.* - [L]
        # standard rules
        RewriteRule content/treemenu/?$ /index_treemenu.php [L]
        Rewriterule ^/var/storage/.* - [L]
        Rewriterule ^/var/[^/]+/storage/.* - [L]
        RewriteRule ^/var/cache/texttoimage/.* - [L]
        RewriteRule ^/var/[^/]+/cache/texttoimage/.* - [L]
        Rewriterule ^/design/[^/]+/(stylesheets|images|javascript|fonts)/.* - [L]
        Rewriterule ^/share/icons/.* - [L]
        # updated for ezflow
        Rewriterule ^/extension/[^/]+/design/[^/]+/(stylesheets|images|lib|flash|javascripts?)/.* - [L]
        Rewriterule ^/packages/styles/.+/(stylesheets|images|javascript)/[^/]+/.* - [L]
        RewriteRule ^/packages/styles/.+/thumbnail/.* - [L]
        RewriteRule ^/favicon.ico - [L]
        RewriteRule ^/robots.txt - [L]
        RewriteRule .* /index.php
    </IfModule>
</VirtualHost>

EOT;
