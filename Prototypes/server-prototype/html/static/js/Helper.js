//////////////////
// SHARED HELPER
//////////////////



/**
 * Invokes a protected Script
 * @param scriptFileName
 */
function invokeScript(scriptFileName){
    $("#outputResult").empty()
    GET('run','ProtectedRun',"?fileToRun="+scriptFileName+".php&useText=true&prettify=true");
}

// DISPLAY

/**
 * Displays the result in the console and on the screen
 * @param data
 */
function displayResult(data){
    console.log(data);
    if (typeof data == "object" ) {
        $("#outputResult").append ((JSON && JSON.stringify ? JSON.stringify(data,null, 1) : data) + "<br />");
    } else {
        $("#outputResult").append( data + "<br/>");
    }
}

///////////////////////////////
// ON THE CURRENT DOM
///////////////////////////////


// REGISTER Runnable script

/***
 * Registers the runnable scripts
 *
 * Any element with the "runnable" class will be considered as a runnable script reference.
 * e.g :    `<button type="button" class="btn btn-default runnable" id="echo">Echo</button>`
 *          Will run the script `www/Protected/echo.php
 */
$(".runnable").each(function() {
    $(this).on("click", function(e){
        e.preventDefault();
        invokeScript(this.id);
    });
});