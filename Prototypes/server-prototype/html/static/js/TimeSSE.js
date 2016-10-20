/**
 * Created by bpds on 01/06/2016.
 */

/**
 * The url of the Event Source
 * @param url
 */
function timeSSE(url) {
    var counter=0;
    var output = document.getElementById("output");
    var source = new EventSource(url);
    var lastLine ;
    source.addEventListener("tic",function(evt){
        var currentData=JSON.parse(evt.data);
        output.innerHTML=currentData.serverTime;
        counter++;
        return;
    });
}

