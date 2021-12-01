## Advent of Code 2017, day 24


Task details: [Advent of Code 2017, day 24](https://adventofcode.com/2017/day/24)


### PHP script

Run PHP script in terminal
```
$ php aoc2017day24.php 
```

**Analysis with full input data will run 15+ minutes**

Main results will be displayed in terminal. Longer version of results will be saved in output files in folder /results


### Options for PHP script

Uncomment this variable to run input file with full data. Otherwise it will use short (test) data.
```
$longVersion = true; 
```

Uncomment to save full results to output file  (**.txt files with full results can reach 500MB**). 
```
$longVersionOutput = true; 
```
Output files will contain arrays like: 

- Strength of a bridge:
```
bridgesStrengthArr 
Array
(
    [17] => Array
        (
            [0/7] => 7
            [0/7-28/7] => 42
            [0/7-28/7-15/28] => 85
            [0/7-28/7-15/28-15/8] => 108
            ...
```

- Length of a potential bridge:
```
bridgesLengthsArr 
Array
(
    [17] => Array
        (
            [0/7] => 1
            [0/7-28/7] => 2
            [0/7-28/7-15/28] => 3
            ...
```

- Building blocks of a bridge (path made out of input-file line values):
```
bridgesPathArr        
Array
(
    [17] => Array
        (
            [0] => 0/7
            [1] => 0/7-28/7
            [2] => 0/7-28/7-15/28
            [3] => 0/7-28/7-15/28-15/8
            ...
```
- Building blocks of a bridge (path made out of input-file line numbers):
```
bridgesArr
Array
(
    [17] => Array
        (
            [0] => 17
            [1] => 17-21
            [2] => 17-21-16
            [3] => 17-21-16-33
            ...
```


<br>
<br>

### Bash script for parallel search

PHP by default runs synchronously. 
So for each starting block it finds in input data (ie. 0/2) it will run an analysis for that block first and then go to the next one.

To speed things up I created a bash script 
```
$ bash aoc2017day24-php-cli-parallel.sh 
```
The script will 

1. Read data input file (defined with INPUTFILE)
2. Count all starting blocks (lines that have "0/x" or "x/0")
3. Run the main PHP script with a passed argument which starting block (as rootId) to analyze. 
```
for ((i=0; i<=$COUNT_STARTING_BLOCKS; i++));
do
    php aoc2017day24.php "--rootId=$i" & 
done
```

This way we can utilize CPU multithreading to find all resulting bridges in parallel processes. 
Time savings in this case was app. 20% (it took 13m26s to finish all in parallel vs. 16m28s when ran PHP script directly.) 
In terminal htop looks like this:

![](https://www.klik-mall.com/docs/documents/xr/mu/1638322252-5bmDA-2021-11-28-screenshot-htop-pve1-running-php-in-parallel.jpg)

Results will be saved in folder /results, but for each starting block (rootId) separately, so you need to check each file and compare results.


## Results

PART 1:
The strongest bridge we can create?
```
max Bridge Strength: 1940
```

PART 2:
What is the strength of the longest bridge you can make? If you can make multiple bridges of the longest length, pick the strongest one.


```
max Bridge Length: 35 
```
This "bridge" is the longest strongest bridge we can make out of given blocks. 
```
Array
(
    [length] => 35
    [strength] => 1928
    [path] => 0/22-22/35-0/35-0/7-28/7-28/28-19/28-19/27-27/39-39/14-3/14-3/31-20/31-15/20-15/33-1/33-1/10-10/40-40/20-20/47-47/15-46/15-46/44-30/44-30/30-30/43-43/43-43/45-45/45-45/8-8/42-50/42-34/50-34/34-34/32
)
```
Result can be read as:
<br>
Bridge is 35 blocks long, 1928 strong and starting with 0/22 (+ the whole path of blocks)

<br>
<br>
<br>