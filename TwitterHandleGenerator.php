<?php
class TwitterHandleGenerator{
	private $filename;
	private $shortList;
	private $longList;
	private $shortCount = 0;
	private $longCount = 0;
	private $minLong;
	private $maxShort;

	function __construct($filename = null){
		if(is_null($filename) || !file_exists($filename)) die("Please enter a valid file to read");
		$this->filename = $filename;
	}

	private function wordStartsWithAt($word){
		return (substr($word, 0, 2) === "at");
	}
	
	private function readAllThenFilter(){
		//file function reads in the entire file to an array -- not the most efficient since we are only looking for the words with at
		$allWords = file($this->filename, FILE_IGNORE_NEW_LINES);
		$ATLines = array_filter($allWords,array($this, "wordStartsWithAt"));
		$this->sortAndShorten($ATLines);
	}
	/**
	*	We still have to read the entire file just in case someone didn't put them in the file in order, but at least we only need to keep the ones that we 
	*   know we want up front, we could also sort them as we go to avoid a sort call but we should be dealing with a small list by that point
	**/
	private function filterOnRead(){
		$handle = @fopen($this->filename, "r");
		$minLongLength;
		$ATLines = array();
		if ($handle) {
			while (!feof($handle)) {
				 $word = trim(fgets($handle,9999));			 
				 if($this->wordStartsWithAt($word)){
					$ATLines[] = $word;
				 }
			}
			fclose($handle);
			$this->sortAndShorten($ATLines);
		}
	}
	
	private function sortByLength($a,$b){
		return strlen($a)-strlen($b);
	}
	
	private function sortAndShorten($ATLines){
		usort($ATLines,array($this,"sortByLength"));
		$this->shortList = array_slice($ATLines, 0, 10);
		$this->longList = array_slice($ATLines, count($ATLines) - 10);
		$this->longList = array_reverse($this->longList);
	}
	
	
	private function printList(){
		$numReplaces = 1;
		print "Shortest\r\n";
		foreach($this->shortList as $word){
			$usingAt = str_ireplace("at","@",$word,$numReplaces);
			print "\t".$usingAt." -> ".$word."\r\n";
		}
		print "\r\nLongest\r\n";	
		foreach($this->longList as $word){
			$usingAt = str_ireplace("at","@",$word,$numReplaces);
			print "\t".$usingAt." -> ".$word."\r\n";
		}
		print "\r\n";
	}
	
	public function generateHandles($readThenFilter = false){
		if($readThenFilter)
			$this->readAllThenFilter();
		else
			$this->filterOnRead();
		$this->printList();	
	}
	
}
$filePath = $argv[1];
$th = new TwitterHandleGenerator($filePath);
$th->generateHandles($argv[2]);	
?>
