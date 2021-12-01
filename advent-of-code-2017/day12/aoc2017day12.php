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
        example: nodeId (6):
        Array
        (
        [0] => 4
        [1] => 5
        [2] => 6
        )
         */

        # echo 'nodePrimaryNeighboursArr: '. PHP_EOL . print_r($nodePrimaryNeighboursArr[2], true); // show PrimaryNeighbours of node ID
        # echo 'nodePrimaryNeighboursArr: '. PHP_EOL . print_r($nodePrimaryNeighboursArr, true);
        # exit();
        # return $nodePrimaryNeighboursArr;

        $rootKey = 0; # Define initial programID to start with for part 1

        ### PART 1
        $connectedGroupArr = [];
        $connectedProgramsArr = $this->getConnectedPrograms($rootKey, $connectedGroupArr, $nodePrimaryNeighboursArr);

        ### PART 2
        $clusterArr = []; // save to global var all clustered-program-groups
        $getClustersArr = $this->getProgramClusters($nodePrimaryNeighboursArr, $clusterArr);

        ### DISPLAY RESULTS

        echo 'Program rootKey: ' . $rootKey . ' ' . PHP_EOL;
        echo '' . PHP_EOL;
        # echo 'connectedProgramsArr: '. PHP_EOL . print_r($connectedProgramsArr, true). PHP_EOL;
        echo 'Number of connectedPrograms with program (' . $rootKey . '): ' . count($connectedProgramsArr) . PHP_EOL;

        echo '' . PHP_EOL;
        echo 'Number of program clusters: ' . count($clusterArr) . PHP_EOL;
        echo '' . PHP_EOL;
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
                matches:
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
        # echo 'nodeNeighboursArr: '. PHP_EOL . print_r($nodeNeighboursArr, true);
        # exit;
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
            }
        }

        return $connectedGroupArr;
    } // end function

    private function getProgramClusters(&$allProgramsDataArr, &$clusterArr)
    {
        // get item[0]
        $key1 = array_key_first($allProgramsDataArr);

        // collect all programs in a group of this key

        $connectedGroupArr = []; # send empty
        $clusterArr[$key1] = $this->getConnectedPrograms($key1, $connectedGroupArr, $allProgramsDataArr);

        # remove
        if (count($clusterArr[$key1]) > 0) {
            foreach ($clusterArr[$key1] as $val) {
                if (array_key_exists($val, $allProgramsDataArr)) {
                    unset($allProgramsDataArr[$val]);
                }

            }
        }

        if (count($allProgramsDataArr) == 0) {
            return $clusterArr; // this means we are finished, no more to iterate
        } else {
            $run = $this->getProgramClusters($allProgramsDataArr, $clusterArr); // run again
        }

        return $clusterArr;
        # echo 'connectedGroup: '. PHP_EOL . print_r( $connectedGroup, true). PHP_EOL;
    } // end function

} // end Class

$Aoc2017Day12 = new Aoc2017Day12;

# Results will be displayed on terminal console
$Aoc2017Day12->runDay12();