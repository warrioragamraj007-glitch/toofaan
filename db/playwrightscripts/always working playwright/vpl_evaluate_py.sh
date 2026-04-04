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
echo "BASE_URL=$url PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright/ pytest test_script.py --browser chromium > output.txt" > vpl_execution
echo "cat output.txt" >> vpl_execution
#echo "cp output.txt ../../ && cat output.txt" >> vpl_execution
cat >>vpl_execution << 'EOF'

#!/bin/bash

# Extract the total number of collected tests from output.txt
totaltests=$(grep -oP '(?<=collected )[0-9]+' output.txt)

# Extract the number of passed and failed tests from the summary section
failedtests=$(grep -oP '(?<=)[0-9]+(?=\sfailed)' output.txt)
passedtests=$(grep -oP '(?<=)[0-9]+(?=\spassed)' output.txt)

# Calculate the grade value based on the number of tests passed and the total number of collected tests
if [ -n "$totaltests" ] && [ -n "$passedtests" ]; then
    gradevalue=$((100 * passedtests / totaltests))
else
    gradevalue=0
fi

# Output all lines except the last 3 lines of output.txt
tail -n -3 output.txt

# Check the grade value and set the appropriate grade
if [ "$gradevalue" -eq 100 ]; then
    echo "Congratulations"
    grade=$gradevalue
else
    echo "----"
    grade=$gradevalue
fi

# Output the grade
echo $passedtests
echo $totaltests
echo $failedtests
echo "Grade :=>>$grade"



EOF
chmod +x vpl_execution