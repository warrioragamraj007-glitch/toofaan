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
currentwd=$(pwd)
# Print the extracted URL
#export PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright
#echo $PLAYWRIGHT_BROWSERS_PATH
echo "Extracted URL: $url"
#echo "cd test/tests && PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright/ BASE_URL=$url npx playwright test app.spec.ts --reporter=line" > vpl_execution
echo "echo 'Please wait ....., Running  Tests'" > vpl_execution
echo "cd test/tests && PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright/ BASE_URL=$url npx playwright test app.spec.ts  > output.txt" >> vpl_execution
#echo "PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright/ pytest test_script.py --browser chromium > output.txt" > vpl_execution
echo "cat output.txt" >> vpl_execution
#echo "rm -rf test" >> vpl_execution
#echo "export BASE_URL=$url" > vpl_execution
#echo "BASE_URL=$url PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright/ pytest test_script.py --browser chromium -q --disable-warnings" >> vpl_execution
echo "cd ../../ && rm -rf test" >> vpl_execution
chmod +x vpl_execution
