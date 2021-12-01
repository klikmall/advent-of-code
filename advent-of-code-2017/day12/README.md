## Advent of Code 2017, day 12


Task details: [Advent of Code 2017, day 12](https://adventofcode.com/2017/day/12)


Run PHP script from console
```
$ php aoc2017day12.php 
```

Results will be displayed in terminal
```
Program rootKey: 0 

Number of connectedPrograms with program (0): 115

Number of program clusters: 221
```

### Options

Change input file to use test or full data
```
# $fileInputData = file_get_contents(ABSPATH.'inputData.txt');
$fileInputData = file_get_contents(ABSPATH . 'inputDataFull.txt');
```

Although task states that we need to find programs that contain "program ID 0", it is possible to modify the script to use another primary program ID 
```
$rootKey = 0;
```

Uncomment lines if you want to see resulting arrays of connected programs and clusters of programs

```
# echo 'connectedProgramsArr: '. PHP_EOL . print_r($connectedProgramsArr, true). PHP_EOL;
# echo 'clustersArr: ' . PHP_EOL . print_r($clusterArr, true) . PHP_EOL;
```