#!/bin/bash

# MySQL Connector path
MYSQL_CONN=/usr/share/java/mysql-connector-java-8.0.33.jar

# Find all Java files in the current directory
SOURCE_FILES=$(find . -name "*.java")

# Check if there are Java source files
if [ -z "$SOURCE_FILES" ]; then
    echo "No Java source files found."
    exit 0
fi

# Compile the Java files explicitly setting the classpath
javac -cp $MYSQL_CONN:. $SOURCE_FILES 2> compile_errors.log
cat compile_errors.log

# Check if compilation was successful
if [ "$?" -ne "0" ]; then
    echo "Compilation failed"
    exit 0
fi

# Search for the main class by finding the class with a main method
MAINCLASS=
for FILENAME in $SOURCE_FILES; do
    if grep -q "public static void main" "$FILENAME"; then
        MAINCLASS=$(basename "$FILENAME" .java)
        break
    fi
done

# If no main class found, exit with an error message
if [ -z "$MAINCLASS" ]; then
    echo "Class with 'public static void main(String[] arg)' method not found"
    exit 0
fi

# Create the execution script with explicit classpath for runtime
echo "#!/bin/bash" > vpl_execution
echo "java -cp $MYSQL_CONN:. $MAINCLASS" >> vpl_execution
chmod +x vpl_execution
