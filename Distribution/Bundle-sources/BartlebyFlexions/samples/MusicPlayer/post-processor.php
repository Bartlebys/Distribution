<?php 

// /////////////////////////////////////////
// #1 Save the hypotypose to files
// /////////////////////////////////////////


hypotyposeToFiles();


// /////////////////////////////////////////
// #2 generate a consolidated header file 
// /////////////////////////////////////////

/* @var $outPutFolderRelativePath string */

if(file_exists(realpath($destination))==false){
	throw new Exception("Unexisting destination ".realpath($destination));
}

$generated='';
$classList = Hypotypose::Instance()->flexedList [DefaultLoops::ENTITIES];
$counter = 0;

foreach ( $classList as $flexed ) {
	if ($flexed->exclude === false) {
		// Let's add a human readable log.
		$counter ++;
		if (VERBOSE_FLEXIONS)
			fLog ( $counter . " " . $flexed->fileName. cr() , false );
			
		// Populate the Header file
		if ((strpos (  $flexed->fileName, ".h" ) === strlen (  $flexed->fileName ) - 2) && strpos ( $generated,  $flexed->fileName ) == null) {
			$line = '';
			if ($flexed->description!=null && strpos ( $generated,  $flexed->description ) == null) {
				// This documentation line has not been found
				$line = "\n" . '//' .  $flexed->description . "\n";
				$line .= '#import "' .  $flexed->fileName . '"' . "\n";
				$generated .= $line;
			} else {
				// There is already such a documentation line
				$line .= '#import "' .  $flexed->fileName . '"' . "\n";
				$generated .= $line;
			};
		}
	}
}

// We use the models variables
$f=new Flexed();
// This include sets $f properties
include FLEXIONS_SOURCE_DIR . "SharedMusicPlayer.php";
$f->package="";
$f->fileName="SLYModelsImports.h";

// We save the generated headers.
$filePath= $destination .$f->package. $f->fileName;
file_put_contents ( $filePath, $generated );