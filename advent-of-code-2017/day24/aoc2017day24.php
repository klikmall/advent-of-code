<?php

/*
--- Day 24: Electromagnetic Moat ---
https://adventofcode.com/2017/day/24

For this test PHP 7.3 CLI was used.
Running test data takes miliseconds, but full-data iterations took 15+ minutes.
It will time-out by webserver (with common settings) if script is ran in a browser.
 */

$currAbsPathFolder = realpath(dirname(__FILE__));
$currAbsPathFolder = rtrim($currAbsPathFolder, '/') . '/';
define('ABSPATH', $currAbsPathFolder . '/');
define('ABSPATHRESULTS', $currAbsPathFolder . '/results/');

# Check if correct folder
# echo $currAbsPathFolder. PHP_EOL;
# exit;

class Aoc2017Day24
{

    public function __construct()
    {

    }

    public function part1()
    {
        # results will include the duration of the analysis
        $startTime = microtime(true);

        /*
        Switch between short (instructions, development, testing) version and
        long (full puzzle data) version
         */
        $longVersion = false;
        $longVersion = true; // uncomment to run full-puzzle data

        $longVersionOutput = false;
        # $longVersionOutput = true; // uncomment to save full resulty to output file (300-500MB!)

        /* 
        Triggered by bash script. 
        If >= 0 ... will limit anlysis to this particular key in array of starting/root building blocks. 
        If '' ... default. It will be ignored. We assume all bridges for all starting blocks must be analyzed.
        */
        $this->currentPhpRootId = ''; 

        # ---------------------------------------------------------

        $inputFilename = 'inputPart1.txt'; // test data 
        $outputFilename = 'output-aoc2017day24-short-full.txt'; // save all results for all starting blocks in a single file

        # Use long/full puzzle data
        if ($longVersion == true) {
            // operate with full-puzzle input data
            $inputFilename = 'inputPart1Long.txt';
            $outputFilename = 'output-aoc2017day24-long-full.txt';
        }

        /*
        If we use linux bash script
        day24/aoc2017day24-php-cli-parallel.sh
        in terminal with command
        ~~~
        $ bash aoc2017day24-php-cli-parallel.sh
        ~~~
        it will spawn 3 threads. Each of them will run an analysis
        for line-inputs: 0/x or x/0
        as these define rootId's that can be used to start a bridge.
        Script is written to manually set number of threads depending on
        the number of starting blocks in your input file.
         */
        // IS THIS separated per multiple threads?
        $cmdLineArgsArr = getopt(null, ["rootId:"]);
        echo '$cmdLineArgsArr: ' . PHP_EOL . print_r($cmdLineArgsArr, true) . PHP_EOL;

        if (isset($cmdLineArgsArr['rootId'])) {
            $currentPhpRootId = intval($cmdLineArgsArr['rootId']);
            echo 'current PhpThread RootId: ' . $currentPhpRootId . PHP_EOL;
        } else {
            $currentPhpRootId = '';
            echo 'PhpThread RootId NOT specified. Run all potential paths.' . PHP_EOL;
        }

        if ($currentPhpRootId >= 0) {
            $this->currentPhpRootId = $currentPhpRootId;
            # PHP cli will run in parallel
            # Set output files separately for each starting block as root
            $outputFilename = 'output-aoc2017day24-short-' . $currentPhpRootId . '.txt'; // if we are using test data
            if ($longVersion == true) { 
                $outputFilename = 'output-aoc2017day24-long-' . $currentPhpRootId . '.txt'; // if we are using full data
            }
        } else {
            // not defined -> Analyze all paths. 
        }

        # get data from file
        $fileInputData = file_get_contents(ABSPATH . $inputFilename);

        $fileName = ABSPATHRESULTS . $outputFilename;
        if (!file_exists($fileName)) {
            touch($fileName); // create a target output file if does not exist
        }

        /*
        // Check if file content is read correctly
        echo $fileInputData . PHP_EOL;
        echo $fileName . PHP_EOL;
        exit();
         */

        /*
        Define a starting port-value. Instructions state it is 0,
        but can be changed here and run for completely different starting blocks
         */
        $this->defaultStartingPort = 0;

        /*
        This array will contain all bridges as connected blockIds like
        29-48-24-17-21
        where 29, 48... are line numbers from a file with input data
         */
        $this->bridgesArr = [];

        /*
        Run parsing data and get required/prepared arrays for specific purposes and further usage
        More details about these arrays and why are created can be found in a function parseInputFileToArr()
         */
        $componentsArr = $this->parseInputFileToArr($fileInputData);

        $this->startingBlocksArr = $componentsArr['startingBlocks']; // block Ids that can serve as starting blocks
        $this->blocksArr = $componentsArr['blocks']; // all blocks as blockId => array(port values)
        $this->mapArr = $componentsArr['map']; // array where keys are port values and values are arrays of blockId with that port value
        $this->strengthArr = $componentsArr['strength']; // BlockId -> strength
        $this->bridgesStrengthControlArr = $componentsArr['strengthControlArr'];
        $this->blockLabelsArr = $componentsArr['blockLabels']; // BlockId -> initial block label ie. 6/39

        $this->usedBlocksArr = []; // array for storing already used blocks that can be excluded from n+1 iterations

        ### PART 1
        $createAllBridges = $this->createAllBridges(); // MASTER CALL to the main procedure

        ### PART 2
        $allStrengthWinnersArr = [];
        $allLengthWinnersArr = [];

        $maxBridgeStrength = 0; // the strongest bridge - strength value
        $maxBridgeLength = 0; // the lingest bridge - number of containing blocks

        ###
        ### RESULTS
        ###

        echo PHP_EOL;
        echo 'Starting Port (' . $this->defaultStartingPort . '): ' . PHP_EOL;

        # Sort starting block Ids as keys (optional)
        foreach ($this->bridgesArr as $rootId => $arr) {
            sort($arr);
            $this->bridgesArr[$rootId] = $arr;
        }

        # Display all bridge combinations as lines PER starting block Id (=$rootId)
        foreach ($this->bridgesArr as $rootId => $arr) {
            foreach ($arr as $line) {
                $path = '';
                $strength = 0;

                # $numOfDashes = substr_count($line, '-');
                $currPathArr = explode('-', $line);
                $currPathLength = count($currPathArr);

                foreach ($currPathArr as $blId) {
                    if ($blId > 0) {
                        $path .= $this->blockLabelsArr[$blId] . '-';

                        $strength = $strength + $this->strengthArr[$blId];
                    } // end if
                } // end foreach

                $path = rtrim($path, '-');

                # Display all paths on screens
                # echo $path. PHP_EOL;

                $this->bridgesPathArr[$rootId][] = $path;
                $this->bridgesStrengthArr[$rootId][$path] = $strength;
                $this->bridgesLengthsArr[$rootId][$path] = $currPathLength;

                # set if new max.strenght
                if ($strength >= $maxBridgeStrength) {
                    $maxBridgeStrength = $strength;
                    $allStrengthWinnersArr[$strength][] = $path;
                } // end if

                # set if new longest path
                if ($currPathLength >= $maxBridgeLength) {
                    $allLengthWinnersArr[$currPathLength][] = $path;
                    $maxBridgeLength = $currPathLength;

                    /*
                    Save array-combo of length, strength and path.
                    This array will give us the final result for Part 2
                     */
                    $theStrongestLongestBridgesArr[] = array(
                        'length' => $maxBridgeLength,
                        'strength' => $strength,
                        'path' => $path,
                    );
                } // end if
            } // end foreach
        } // end foreach

        ### ADD sleep/delay in execution to test timing benchmark
        /*
        echo "RootId: $this->currentPhpRootId @".date("H:i:s", time()).PHP_EOL;
        $sleepDelaySecs = rand(2,20);
        sleep($sleepDelaySecs);
        echo "RootId: $this->currentPhpRootId (slept for: $sleepDelaySecs secs) @".date("H:i:s", time()).PHP_EOL;
        # exit;
         */

        $endTime = microtime(true);

        /*
        What is the strength of the longest bridge you can make?
        If you can make multiple bridges of the longest length, pick the strongest one.
         */

        $theStrongestLongestBridgePath = '';
        // [$maxBridgeLength]
        # echo 'theStrongestLongestBridgesArr (Len='.$maxBridgeLength.'): '. PHP_EOL . print_r($theStrongestLongestBridgesArr, true). PHP_EOL;

        # clean all but the-longest bridges
        foreach ($theStrongestLongestBridgesArr as $ckey => $cArr) {
            if ($cArr['length'] < $maxBridgeLength) {
                unset($theStrongestLongestBridgesArr[$ckey]);
            }
        }

        # sort the longest bridges DESC by length + DESC by strength
        # TOP item will give us the PATH of the longest-strongest bridge we can produce
        uasort($theStrongestLongestBridgesArr, function ($a, $b) {
            return $b['length'] <=> $a['length'] //  desc
            ?: $b['strength'] <=> $a['strength'] //  desc
            ;
        });

        echo '' . PHP_EOL;
        echo '------- RESULTS -------' . PHP_EOL;

        echo '' . PHP_EOL;
        echo 'The Strongest-Longest Bridges Arr (Bridge length = ' . $maxBridgeLength . '): ' . PHP_EOL . print_r($theStrongestLongestBridgesArr, true) . PHP_EOL;
        echo '' . PHP_EOL;

        # $this->bridgesPathArr[$rootId] = $arr;
        # $this->blocksArr

        echo 'ABS maxBridgeStrength: ' . $maxBridgeStrength . PHP_EOL;
        echo 'all Strength Winners Arr: ' . PHP_EOL . print_r($allStrengthWinnersArr[$maxBridgeStrength], true) . PHP_EOL;
        # echo '$this->bridgesStrengthArr: '. PHP_EOL . print_r($this->bridgesStrengthArr, true). PHP_EOL;
        echo '' . PHP_EOL;

        echo 'ABS maxBridgeLength: ' . $maxBridgeLength . PHP_EOL;
        echo 'all Length Winners Arr: ' . PHP_EOL . print_r($allLengthWinnersArr[$maxBridgeLength], true) . PHP_EOL;
        echo '' . PHP_EOL;
        echo 'bridges Lengths Arr: ' . PHP_EOL . print_r($this->bridgesLengthsArr, true) . PHP_EOL;
        echo '' . PHP_EOL;

        # echo '$this->bridgesPathArr: '. PHP_EOL . print_r($this->bridgesPathArr, true). PHP_EOL;
        # echo '$this->bridgesArr: '. PHP_EOL . print_r($this->bridgesArr, true). PHP_EOL;
        # echo '$this->bridgesStrengthArr: '. PHP_EOL . print_r($this->bridgesStrengthArr, true). PHP_EOL;
        # echo '$this->bridgesStrengthControlArr: '. PHP_EOL . print_r($this->bridgesStrengthControlArr, true). PHP_EOL;

        # Duration / Benchmark
        $executionTime = $endTime - $startTime; // in micro seconds
        $executionTimeDisplay = $this->formatPeriod($endTime, $startTime);

        $displayDuration = '
DURATION:
' . $executionTime . ' microseconds
' . $executionTimeDisplay . ' (H:i:s)
        ';

        # Display duration in terminal
        echo $displayDuration . PHP_EOL;

        
        # SAVE RESULTS to file

        $htmlStr = '--- RESULTS
Date: ' . date("Y-m-d H:i:s", time()) . '

' . $displayDuration . '
---

current PhpThread RootId: ' . $currentPhpRootId . '

PART 1:
The strongest bridge we can create?

ABSOLUTE maxBridgeStrength: ' . $maxBridgeStrength . '
ABSOLUTE maxBridgeLength: ' . $maxBridgeLength . '

PART 2:
"What is the strength of the longest bridge you can make? If you can make multiple bridges of the longest length, pick the strongest one."

TOP Array item will give us the length, strength & path of the longest-strongest bridge we can produce with our puzzle.
If there is more than one item in this array, they are THE SAME (max) length, BUT weaker.

theStrongestLongestBridgesArr:
' . print_r($theStrongestLongestBridgesArr, true) . '

------------------------------------------------------
------------------------------------------------------
';

        if ($longVersionOutput == true) {

            $htmlStr .= '
    FULL RESULTS DATA

    bridgesStrengthArr
    ' . print_r($this->bridgesStrengthArr, true) . '
    ---------------------------


    bridgesLengthsArr
    ' . print_r($this->bridgesLengthsArr, true) . '
    ---------------------------


    bridgesPathArr
    ' . print_r($this->bridgesPathArr, true) . '
    ---------------------------

    bridgesStrengthControlArr
    ' . print_r($this->bridgesStrengthControlArr, true) . '
    ---------------------------

    bridgesArr
    ' . print_r($this->bridgesArr, true) . '
    ---------------------------
    ';

        } // end if

        $htmlStr .= '

--------------------------- END of RESULTS ---------------------------
';

        # Save content to file
        file_put_contents($fileName, $htmlStr);

    } // end function part 1

    ##
    ## HELPER, ITERATOR FUNCTIONS
    ## 

    private function createAllBridges()
    {
        /*
        1. For each startingBlocksArr get its ID
        2. get opposite-side port value
        3. Run function iterateNewBlocks with starting port value for step 2 and rootID
         */

        foreach ($this->startingBlocksArr as $blocksKey => $rootId) {
            echo 'createAllBridges startId (' . $rootId . '): ' . PHP_EOL; // tmp. control output

            // this is our master array with ALL BRIDGE PATHS as items (per each root block)
            $this->bridgesArr[$rootId][] = $rootId;

            // Set initial used blocks arr PER each root path
            $this->usedBlocksArr[$rootId][] = $rootId;

            // get opposite port to define port value that block at STEP2 must have.
            $portNext = $this->getOtherPortSide($this->defaultStartingPort, $this->blocksArr[$rootId]);

            $path = $rootId; // current path is only rootId

            if ($blocksKey == $this->currentPhpRootId or $this->currentPhpRootId == '') {
                # if( $rootId == 29 ){ // if we want to run only for the winner-tree of part1 and save time
                $run = $this->iterateNewBlocks($rootId, $portNext, $path);
            }

            echo '---' . PHP_EOL;
        } // end foreach startId

        # return $connectedArr;
        # echo 'connectedGroup: '. PHP_EOL . print_r( $connectedGroup, true). PHP_EOL;
    } // end function createAllBridges

    private function iterateNewBlocks($rootId, $portVal, $prevPath)
    {
        /*
        If previous path == rootId (or. root Block Id),
        it means we are at the beggining of a tree for this block id (contains port value 0)
         */
        if ($prevPath == $rootId) {
            $this->usedBlocksArr[$rootId] = [$rootId]; // reset & put only root block ID as USED block
            echo 'RESET usedBlocksArr for ' . $this->blockLabelsArr[$rootId] . ' (rootId: ' . $rootId . ')' . PHP_EOL . PHP_EOL;
            # echo 'usedBlocksArr: '. PHP_EOL . print_r($this->usedBlocksArr[$rootId], true). PHP_EOL;
        }

        # CREATE AN ARRAY of USED BLOCK IDs based on pathUsedBlocksArr
        # prevPath looks like "29-48-5-30-57-28-40-49-53-31-27-10"
        $pathUsedBlocksArr = explode('-', $prevPath);
        # Merge used blocks from pervPath with usedBlocksArr[$rootId]
        $this->usedBlocksArr[$rootId] = array_merge($this->usedBlocksArr[$rootId], $pathUsedBlocksArr);
        $this->usedBlocksArr[$rootId] = array_unique($this->usedBlocksArr[$rootId]);
        # echo 'prevPath ('.$prevPath.') has ('.count($this->usedBlocksArr[$rootId]).' usedArr: '. PHP_EOL;

        # A list on a terminal that will show iterations
        echo $prevPath . ' (usedArr:' . count($this->usedBlocksArr[$rootId]) . ')' . PHP_EOL;
        # echo print_r($this->usedBlocksArr[$rootId], true). PHP_EOL; // tmp. control output
        # exit;

        # echo 'usedBlocksArr: '. PHP_EOL . print_r($this->usedBlocksArr[$rootId], true). PHP_EOL;

        $suitableBlocksArr = $this->getNextBlocksArr($portVal, $rootId);
        # echo '(rootId: '.$rootId.') blocks for port '.$portVal.': '.count($suitableBlocksArr) . PHP_EOL;
        # echo print_r($suitableBlocksArr, true). PHP_EOL;

        if (count($suitableBlocksArr) > 0) {
            # $this->usedBlocksArr[$rootId] = array_merge($this->usedBlocksArr[$rootId], $suitableBlocksArr);

            $newBridgesArr = $this->addNewBridgesToRoot($rootId, $prevPath, $suitableBlocksArr);
            # echo 'newBridgesArr: '. PHP_EOL . print_r($newBridgesArr, true). PHP_EOL;

            $this->bridgesArr[$rootId] = array_merge($this->bridgesArr[$rootId], $newBridgesArr);

            foreach ($suitableBlocksArr as $ch) {
                $this->usedBlocksArr[$rootId][] = $ch; // Remove current block from the pool

                $pathNext = $prevPath . '-' . $ch;

                $portNext = $this->getOtherPortSide($portVal, $this->blocksArr[$ch]); // to get
/*
echo 'ITER: root('.$rootId.') > ID('.$ch.') '.
$this->blockLabelsArr[$ch].' + portVal('.$portVal.') > portNext('.$portNext.') AvailBlocks['.$portVal.']: '.count($suitableBlocksArr).' prevPath('.$prevPath.') '.
PHP_EOL;
 */
                $this->iterateNewBlocks($rootId, $portNext, $pathNext);
                # $this->iterateNewBlocks($rootId, $ch, $portVal, $prevPath);

                // give it back for other suitable blocks at this point to use
                $chkey = array_search($ch, $this->usedBlocksArr[$rootId]);
                if ($chkey !== false) {
                    unset($this->usedBlocksArr[$rootId][$chkey]);
                }
                # echo 'After put back: usedBlocksArr: '. PHP_EOL . print_r($this->usedBlocksArr[$rootId], true). PHP_EOL;
            } // end foreach
        } // end if

        return true; // result is already being written to global vars $this->bridgesArr, $this->usedBlocksArr
    } // end function iterateNewBlocks

    // HELPER FUNCTIONS

    private function addNewBridgesToRoot($rootId, $prevPath, $arr)
    {    
        /*
        add new items 
        $arr('25/17', '25/17')
        to
        prePath of '0/22-22/35-0/35-0/7-28/7-15/28-15/8-8/42-38/42-38/36-25/36'
        for a specific root BlockId as its new potential bridge-paths.
        result:
        [14] => 0/22-22/35-0/35-0/7-28/7-15/28-15/8-8/42-38/42-38/36-25/36-25/17
        [15] => 0/22-22/35-0/35-0/7-28/7-15/28-15/8-8/42-38/42-38/36-25/36-25/17
        */
        $newBridgesArr = [];
        foreach ($arr as $nextStep) {
            $newBridgesArr[] = $prevPath . '-' . $nextStep;
        }
        return $newBridgesArr; // only remaining option (if input arr is valid)
    }

    # get all block Ids based on input:portValue
    # result is a part of an array we prepared in $componentsArr['map']
    private function getNextBlocksArr($portValue, $rootId)
    {
        $nextBlockArr = [];
        $usedArr = $this->usedBlocksArr[$rootId];
        if ($portValue >= 0 && count($this->mapArr[$portValue]) > 0) {
            foreach ($this->mapArr[$portValue] as $id) {
                if (!in_array($id, $usedArr)) {
                    $nextBlockArr[] = $id;
                }
            }
        }

        $nextBlockArr = array_unique($nextBlockArr);
        # echo 'Returning remaining options for nextBlockArr for port value '.$portValue. PHP_EOL;
        # echo 'usedArr: '. PHP_EOL . print_r( $usedArr, true). PHP_EOL;
        # echo 'nextBlockArr: '. PHP_EOL . print_r( $nextBlockArr, true). PHP_EOL;
        # echo '---'. PHP_EOL;
        # echo ''. PHP_EOL;

        return $nextBlockArr;
        # echo 'connectedGroup: '. PHP_EOL . print_r( $connectedGroup, true). PHP_EOL;
    }

    # get OPPOSITE port value based on a passed block and one port-side value.
    private function getOtherPortSide($input, &$arr)
    {
        # could do a control that input arr has to have only 2 sides,
        if ($arr[0] == $input) {
            return $arr[1]; // return other side-port-value
        }
        return $arr[0]; // only remaining option (if input arr is valid)
    }

    # PARSE input file data & prepare result array for further usage
    private function parseInputFileToArr($fileInputData)
    {
        $lines = explode(PHP_EOL, $fileInputData);
        # $lines = array_slice($lines, 0, 35); // slide initial N-items for testing

        $componentsArr = [];
        $count = 0;

        foreach ($lines as $line) {
            # echo 'LINE'.$count.': ' . $line. PHP_EOL;
            $linePortsArr = explode('/', $line);

            $val0 = intval($linePortsArr[0]);
            $val1 = intval($linePortsArr[1]);

            if (is_numeric($val0) && is_numeric($val1)) {
                $count++; // ! THIS is the new blockId!

                # ID => port1/port2
                # helps modifying the outputs by replacing used blockId with its original fileInput value ie. "22/35"
                $componentsArr['blockLabels'][$count] = $line;

                $lineStrength = $val0 + $val1;

                # create an associative array of componentID => Total Strength
                $componentsArr['strength'][$count] = $lineStrength;

                # Just for displaying a prettier form of calculated strength for a given block
                # ie. [3] => 29/25 (54) ... 54 being a sum of port values.
                $componentsArr['strengthControlArr'][$count] = $line . ' (' . $lineStrength . ')';

                // these are our potential starting blocks
                if ($val0 == $this->defaultStartingPort or $val1 == $this->defaultStartingPort) {
                    $componentsArr['startingBlocks'][] = $count;
                }

                # Create a map of all componentIDs that can be called for a given port-number value
                # i.e GET US ALL block IDs that have at least one port value of NUMBER
                $componentsArr['map'][$val0][] = $count;
                if ($val0 != $val1) {
                    $componentsArr['map'][$val1][] = $count;
                }

                /*
                here are all blocks data in form of array(
                blockId => array(
                port1 value,
                port2 value
                )
                )
                 */
                $componentsArr['blocks'][$count] = $linePortsArr; # save all components to array for further usage
            } // end if is numeric
        }

        # echo 'componentsArr: '. PHP_EOL . print_r($componentsArr, true);
        # echo 'componentsArr[map]: '. PHP_EOL . print_r($componentsArr['map'], true);
        # exit;

        /*
        # premature exit with putting prepared data to external file for manual check
        $fileName = ABSPATH.'aoc2017day24Output.txt';
        $fileName = ABSPATH.'aoc2017day24OutputLong.txt';
        $htmlStr = print_r($componentsArr,true);
        file_put_contents($fileName, $htmlStr);
        exit;
         */

        return $componentsArr;
    } // end function

    public function formatPeriod($endtime, $starttime)
    {
        $duration = $endtime - $starttime;
        $hours = (int) ($duration / 60 / 60);
        $minutes = (int) ($duration / 60) - $hours * 60;
        $seconds = (int) $duration - $hours * 60 * 60 - $minutes * 60;
        return ($hours == 0 ? "00" : $hours) . ":" . ($minutes == 0 ? "00" : ($minutes < 10 ? "0" . $minutes : $minutes)) . ":" . ($seconds == 0 ? "00" : ($seconds < 10 ? "0" . $seconds : $seconds));
    }

} // end Class

$Aoc2017Day24 = new Aoc2017Day24;

$part1 = $Aoc2017Day24->part1();

# Results will be displayed in a terminal and in results/ files
