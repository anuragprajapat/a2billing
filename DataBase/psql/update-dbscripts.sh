#!/bin/bash

# This script re-runs all SQL scripts not involving dataset changes

A2B_USER=a2billing
A2B_DB=a2billing
A2B_GROUP=a2b_group

for DBSCRIPT in schema-200/* ; do
	if grep 'CREATE TABLE' $DBSCRIPT > /dev/null ; then
		echo 'Skiping'  "$DBSCRIPT"
		continue
	fi
	echo  'Invoking' "$DBSCRIPT"

	psql -U $A2B_USER --set ON_ERROR_STOP= --set A2B_GROUP=$A2B_GROUP \
		-d $A2B_DB -f "$DBSCRIPT" || exit $?
done

echo "Database updated successfully!"
#eof
