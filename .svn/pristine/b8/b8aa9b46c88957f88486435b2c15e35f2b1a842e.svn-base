<?php

$knowledgeBaseString = <<<EOT
update ezcontentclass_name
set
  language_id=3
  , language_locale="eng-US"
where
  language_id=5;

update ezcontentclass
set
  initial_language_id=2
  , language_mask=3
  , serialized_name_list=REPLACE(serialized_name_list,"eng-GB","eng-US");
  
delete from ezcontent_language
where
  id=4;

EOT;
?>