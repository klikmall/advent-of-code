#!/bin/bash

curr_dir=`cd "$(dirname "$0")" && pwd`
echo "Current dir: "$curr_dir

# MATCH input file with the one in PHP script aoc2017day24.php
# SHORT version/test data
# INPUTFILE="$curr_dir/inputPart1.txt" 
# or use FULL/LONG puzzle data
INPUTFILE="$curr_dir/inputPart1Long.txt" 

echo "INPUTFILE: "$INPUTFILE
echo "---"
# exit

# Count number of lines in input file 
ALL_LINES=0;
# Count number of starting blocks for out php script in input file 
COUNT_STARTING_BLOCKS=0;

while read line || [ -n "$line" ]
do
   # COUNT_STARTING_BLOCKS=`(($COUNT_STARTING_BLOCKS+1))`
   let "ALL_LINES+=1" 
    # echo "$line" ;
    IFS='/';
    # echo "line: "$line
    arrIN=($line)
    # echo "Array: "${arrIN[0]}" / "${arrIN[1]}

    # IF arr[0] or arr[1] is exactly "0" -> this can be a starting block
    if [ "${arrIN[0]}" = "0" ] || [ "${arrIN[1]}" = "0" ]; then
        let "COUNT_STARTING_BLOCKS+=1"
        echo "NEW STARTING BLOCK in: "${arrIN[0]}"/"${arrIN[1]}
    fi

done < $INPUTFILE

echo "---"
echo "ALL_LINES: $ALL_LINES"
echo "COUNT_STARTING_BLOCKS: $COUNT_STARTING_BLOCKS"
echo "We will spawn ($COUNT_STARTING_BLOCKS) PHP processes"
echo "---"
# exit

for ((i=0; i<=$COUNT_STARTING_BLOCKS; i++));
do
    echo "php aoc2017day24.php --rootId="$i & 
    php aoc2017day24.php "--rootId=$i" & 
    sleep 2
done
 
## Put all cust_func in the background and bash 
## would wait until those are completed 
## before displaying all done message
wait 
echo "FINISHED ALL PHP SCRIPTS"