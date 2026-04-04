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
echo "cd test/tests && PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright/ BASE_URL=$url npx playwright test app.spec.ts  > output.txt" > vpl_execution
#echo "PLAYWRIGHT_BROWSERS_PATH=/usr/local/customlibs/.cache/ms-playwright/ pytest test_script.py --browser chromium > output.txt" > vpl_execution
echo "cat output.txt" >> vpl_execution
#echo "cp output.txt ../../ && cat output.txt" >> vpl_execution
cat >>vpl_execution << 'EOF'




# Extract the total number of tests run from output.txt
totaltests=$(grep -oP '(?<=Running )[0-9]+' output.txt)
#echo $totaltests
#echo "_____________"
# Extract the number of passed tests from output.txt
testcasespassed=$(grep -oP '(^\s*[0-9]+(?=\spassed)) | (?<=\s)[0-9]+(?=\spassed)' output.txt)
#echo $testcasespassed

# Calculate the grade value based on the number of tests run and passed
if [ -n "$totaltests" ]; then
    if [ -n "$testcasespassed" ]; then
        gradevalue=$((100 * testcasespassed / totaltests))
    else
        gradevalue=0
    fi
else
    echo "Error: Unable to extract integer values for totaltests."
    exit 1
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
echo "Grade :=>>$grade"
EOF
echo "cd ../../ && rm -rf test" >> vpl_execution
chmod +x vpl_execution