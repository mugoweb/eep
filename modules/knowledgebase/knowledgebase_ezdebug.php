<?php

$knowledgeBaseString = <<<EOT
[DebugSettings]
## DebugByIP=enabled
## DebugIPList[]
## DebugIPList[]=127.0.0.1
DebugOutput=enabled
DebugRedirection=disabled

[TemplateSettings]
# Use either enabled to see which template files are loaded or disabled to supress debug
Debug=enabled
ShowXHTMLCode=disabled

# If enabled will add a table with templates used to render a page.
# DebugOutput should be enabled too.
ShowUsedTemplates=enabled
# Determines whether the templates should be compiled to PHP code, by enabling this the loading
# and parsing of templates is omitted and template processing is significantly reduced.
# Note: The first time the templates are compiled it will take a long time, use the
#       bin/php/eztc.php script to prepare all your templates.
TemplateCompile=disabled
# Controls all template base caching mechanisms, if disabled they will never be
# used.
# The elements currently controlled by this is:
# - cache-block
TemplateCache=disabled
# Controls if development is enabled or not.
# When enabled the system will perform more checks like modification time on
# compiled vs source file and will reduce need for clearing template compiled
# files.
# Note: Live sites should not have this enabled since it increases file access
#       and can be slower.
# Note: When switching this setting the template compiled files must be cleared.
DevelopmentMode=enabled

[ContentSettings]
# Whether to use view caching or not
ViewCaching=disabled

EOT;

?>