#!/bin/bash

echo 'Combining individual markdown files into one_page.md'
cat index.md installation.md modules_* core_* extending_* > one_page.md

echo 'Converting header links to local'
sed -i '' -E s'/(\]\()(.*)(#)/\1\3/g' one_page.md
# if -i '' does not work for you, try the line below
# sed -i -E s'/(\]\()(.*)(#)/\1\3/g' one_page.md

echo 'Done'