<?php


/* @var $h Hypotypose */

// /////////////////////////////////////////
// #1 Save the hypotypose to files
// /////////////////////////////////////////

hypotyposeToFiles();

// /////////////////////////////////////////
// #2 generate some post generation files
// /////////////////////////////////////////


if(file_exists(realpath($destination))==false){
	throw new Exception("Unexisting destination ".realpath($destination));
}

$generated='';
$h=Hypotypose::Instance();

// Let's write the list of the files we have created
// We could iterate of each loop ( $h->flexedList)
$list = $h->getFlatFlexedList();
$counter = 0;

foreach ( $list as $flexed ) {
    /* @var $flexed Flexed */
	if ($flexed->exclude === false) {
		// Let's add a human readable log.
		$counter ++;
        $line='';
		if (VERBOSE_FLEXIONS)
			fLog ( $counter . " " . $flexed->fileName. cr() , false );
		// Let's list the file name
		$line .= $counter.'-> We have created "'.$flexed->package.$flexed->fileName . '"' . "".cr();
		$generated .= $line;
	}
}

// We save the file
$filePath= $destination .'ReadMe.txt';
$c='Those files that are recreated by YouDubApi-flexions-App should not be modified directly.'.cr().cr();
$c.=$generated;
file_put_contents ( $filePath, $c );


// /////////////////////////////////////////
// #3 Deploy
// /////////////////////////////////////////


// We can deploy the files per version and stage
// And keep a copy in the out.YouDubApi-flexions-App folder.

require_once FLEXIONS_MODULES_DIR . '/Deploy/FTPDeploy.php';
require_once FLEXIONS_MODULES_DIR . '/Deploy/LocalDeploy.php';



// DEVELOPMENT
if ($h->stage==DefaultStages::STAGE_DEVELOPMENT){
    $deploy=new LocalDeploy($h);

    $generatedFolder=dirname(dirname(__DIR__)).'/Commons/_generated/';
    // WE DELETE THE GENERATED FOLDER BEFORE TO REGENERATE
    $deploy->rmPath($generatedFolder);
    $deploy->flatCopyFiles('/php/api/v1/_generated/Endpoints/',$generatedFolder.'EndPoints/',true);
    $deploy->flatCopyFiles('/php/api/v1/_generated/Models/',$generatedFolder.'Models/',true);
	$deploy->flatCopyFiles('/php/_generated/',$generatedFolder,true);

    // We want to copy the package 'ios/' files to the iOS sources
    // WE DELETE THE GENERATED FOLDER BEFORE TO REGENERATE
    $deploy->rmPath(xOS_BARTLEBYS_COMMONS_EXPORT_PATH);
    $deploy->copyFiles('/xOS/',xOS_BARTLEBYS_COMMONS_EXPORT_PATH,true);
}

// PRODUCTION
// Replace Host + <USER> & <PASSWORD>
if ($h->stage==DefaultStages::STAGE_BETA){
	// We want to copy the package 'php/' files to a valid FTP.
	$ftpDeploy=new FTPDeploy($h);
    $ftpDeploy->setUp("dev.api.lylo.tv");
    if($ftpDeploy->login("www.dev","uburoi1972danse")==true){
        $ftpDeploy->copyFiles('php/','/home/dev/public_html/');
    }else{
        // There is may be an issue
    }
    // Local copies
    $deploy=new LocalDeploy($h);
	// We want to copy the package 'ios/' files to the iOS sources
    $deploy->rmPath(xOS_BARTLEBYS_COMMONS_EXPORT_PATH);
	$deploy->copyFiles('/xOS/',xOS_BARTLEBYS_COMMONS_EXPORT_PATH,true);
}