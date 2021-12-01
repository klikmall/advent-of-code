<?php

/*
--- Day 12: Digital Plumber ---
https://adventofcode.com/2017/day/12
 */

class Aoc2017Day12
{
    public function __construct()
    {
        # get current folder abs path for reading and storing results into files
        $currAbsPathFolder = realpath(dirname(__FILE__));
        $currAbsPathFolder = rtrim($currAbsPathFolder, '/') . '/';
        define('ABSPATH', $currAbsPathFolder . '/');
    }

    public function runDay12()
    {
        # Select inpu-data to use (short/test or long/full)
        # $fileInputData = file_get_contents(ABSPATH.'inputData.txt');
        $fileInputData = file_get_contents(ABSPATH . 'inputDataFull.txt');
        /*
        // Check if file content is read correctly
        echo $fileInputData . PHP_EOL;
        exit();
         */

        // Parse input content data to array
        $nodePrimaryNeighboursArr = $this->parseInputFileToArr($fileInputData);
        /*
        example: program 6 has these direct neighbours (+ itself by definition)
        Array
        (
            [0] => 4
            [1] => 5
            [2] => 6
        )
         */
        # echo 'nodePrimaryNeighboursArr: '. PHP_EOL . print_r($nodePrimaryNeighboursArr[2], true); // show PrimaryNeighbours of program ID = 2
        # echo 'nodePrimaryNeighboursArr: '. PHP_EOL . print_r($nodePrimaryNeighboursArr, true);

        $rootKey = 0; # Define initial program ID to start with for part 1

        ### PART 1
        $connectedGroupArr = []; # Get programs that are connected with $rootKey
        $connectedProgramsArr = $this->getConnectedPrograms($rootKey, $connectedGroupArr, $nodePrimaryNeighboursArr);

        ### PART 2
        $clusterArr = []; // All clustered-program-groups
        $getClustersArr = $this->getProgramClusters($nodePrimaryNeighboursArr, $clusterArr);

        ### DISPLAY RESULTS
        echo 'PART 1:' . PHP_EOL;
        echo 'Program rootKey: ' . $rootKey . ' ' . PHP_EOL;
        echo 'Number of connectedPrograms with program (' . $rootKey . '): ' . count($connectedProgramsArr) . PHP_EOL;
        # echo 'connectedProgramsArr: '. PHP_EOL . print_r($connectedProgramsArr, true). PHP_EOL;

        echo 'PART 2:' . PHP_EOL;
        echo 'Number of program clusters: ' . count($clusterArr) . PHP_EOL;
        # echo 'clustersArr: ' . PHP_EOL . print_r($clusterArr, true) . PHP_EOL;

        # or return them as
        /*
    return count($connectedProgramsArr); // number of connected programs with programID as $rootKey
    return count($clusterArr); // number of clusters
     */

    } // end function part 1

    ##
    ## HELPER FUNCTIONS
    ##

    private function parseInputFileToArr($fileInputData)
    {
        $lines = explode(PHP_EOL, $fileInputData);
        $nodeNeighboursArr = [];
        foreach ($lines as $line) {
            if (preg_match('/(\d+) <-> (.*)/', $line, $matches)) {

                # echo 'matches: '. PHP_EOL . print_r($matches, true). PHP_EOL;
                /*
                Array
                (
                    [0] => 4 <-> 2, 3, 6
                    [1] => 4
                    [2] => 2, 3, 6
                )
                 */

                list($_, $a, $b) = $matches;

                $valsStr = preg_replace('/[^,0-9]/', '', $b);
                # echo 'valsStr: '. $valsStr. PHP_EOL;

                $nodeNeighboursArr[$a] = explode(',', $valsStr);
                # $nodeNeighboursArr[$a] = array_map('trim', explode(',', $b));
                # echo 'nodeNeighboursArr: '. PHP_EOL . print_r($nodeNeighboursArr[$a], true). PHP_EOL;
                # echo PHP_EOL. PHP_EOL;
            }
        }
        # echo 'Full nodeNeighboursArr: '. PHP_EOL . print_r($nodeNeighboursArr, true);
        return $nodeNeighboursArr;
    } // end function

    private function getConnectedPrograms($root, &$connectedGroupArr, &$allProgramsDataArr)
    {
        # echo 'IN getConnectedPrograms ('.$root.'): '. PHP_EOL;
        if (!in_array($root, $connectedGroupArr)) {
            # echo 'root ('.$root.'): '. PHP_EOL;
            $connectedGroupArr[] = $root;

            foreach ($allProgramsDataArr[$root] as $ch) {
                $this->getConnectedPrograms($ch, $connectedGroupArr, $allProgramsDataArr);
            } // end foreach
        } // end if

        return $connectedGroupArr;
    } // end function

    private function getProgramClusters(&$allProgramsDataArr, &$clusterArr)
    {
        // get item[0] to find connected programs with this program ID
        $key1 = array_key_first($allProgramsDataArr);

        // find all programs connected with program ID = $key1
        $connectedGroupArr = []; # send empty to getConnectedPrograms
        $clusterArr[$key1] = $this->getConnectedPrograms($key1, $connectedGroupArr, $allProgramsDataArr);

        # remove programs from list of all programs that are already present in one of the clusters. 
        # This way we are left with programs that are not in any of already defined clusters, yet.
        if (count($clusterArr[$key1]) > 0) {
            foreach ($clusterArr[$key1] as $val) {
                if (array_key_exists($val, $allProgramsDataArr)) {
                    unset($allProgramsDataArr[$val]);
                } // end if
            } // end foreach
        } // end if

        # When the list of all programs becomes empty it means we have sorted all programs into one of the clusters
        if (count($allProgramsDataArr) == 0) {
            return $clusterArr; // this means we are finished, no more to iterate. restun this array as the result.
        } else {
            $run = $this->getProgramClusters($allProgramsDataArr, $clusterArr); // run again
        } // end if

        return $clusterArr;
        # echo 'connectedGroupArr: '. PHP_EOL . print_r( $connectedGroupArr, true). PHP_EOL;
    } // end function

} // end Class

# Load class
$Aoc2017Day12 = new Aoc2017Day12;

# Run main function. Results will be displayed on terminal console
$Aoc2017Day12->runDay12();
