#!/bin/bash
cp -r /usr/local/customlibs/test .
cp app.spec.ts test/tests/app.spec.ts
#cp playwright.config.js test/playwright.config.js
url=$(grep -oP '(?<=value=")[^"]*(?=")' url.xml)
# Check if the URL was successfully extracted
if [ -z "$url" ]; then
    echo "Failed to extract URL from url.xml"
    exit 1
fi
#ls 
# Print the extracted URL
#export PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright
#echo $PLAYWRIGHT_BROWSERS_PATH
echo "Extracted URL: $url"
#echo "cd test/tests && PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright/ BASE_URL=$url npx playwright test app.spec.ts  > output.txt" > vpl_execution
echo "BASE_URL=$url PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright/ pytest test_script.py --browser chromium -q" > vpl_execution

chmod +x vpl_execution