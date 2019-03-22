#!/usr/bin/env bash

echo "Check multiple deps"

#Check vue
if [[ `yarn why vue | grep "Found" -c` == 1 ]]
then
    echo "OK vue"
else
    echo "There is multiple vue here"
    echo `yarn why vue`
    exit 1
fi

#kendo-ui
if [[ `yarn why "@progress/kendo-ui" | grep "Found" -c` == 1 ]]
then
    echo "OK kendo"
else
    echo "There is multiple kendo-ui here"
    echo `yarn why "@progress/kendo-ui"`
    exit 2
fi

#jquery
if [[ `yarn why "jquery" | grep "Found" -c` == 1 ]]
then
    echo "OK jquery"
else
    echo "There is multiple jquery here"
    echo `yarn why "jquery"`
    exit 3
fi