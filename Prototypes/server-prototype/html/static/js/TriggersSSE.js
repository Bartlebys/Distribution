/**
 * The url of the Event Source
 * @param url
 */
function triggersSSE(url) {

    var counter=0;
    var output = document.getElementById("output");
    var source = new EventSource(url);
    source.addEventListener("relay",function(evt){
        
        var currentData=JSON.parse(evt.data);
        var tr=document.createElement("tr");

        // The JS SSE uses showDetails=true
        
        var index=currentData.i;
        var observationUID=currentData.o
        var sender=currentData.s;
        var runUID=currentData.r;
        var origin=currentData.n;
        var action=currentData.a;
        var uids=currentData.u;
        var payload=currentData.p;


        // Inject the row id
        var rowId="row"+index;
        tr.id=rowId;

        var senderID="sender"+index;

        var tdIndex=document.createElement("td");
        tdIndex.innerHTML=index;

        tr.appendChild(tdIndex);
        var pos=url.indexOf('api/v1/');
        var baseURL=url.substr(0,pos);

        var tdObservationUID=document.createElement("td");
        tdObservationUID.innerHTML='<a href="'+baseURL+'triggers?showDetails=true&observationUID='+observationUID+'">'+observationUID.substr(0,5)+'...'+'</a>';
        tr.appendChild(tdObservationUID);
        
        var tdSender=document.createElement("td");
        tdSender.innerHTML='<a class="top" title="'+sender+'" id="'+senderID+'" data-placement="top" data-toggle="tooltip" href="#" data-original-title="">'+sender.substr(0,5)+'...'+'</a>';
        tr.appendChild(tdSender);

        var tdRunUID=document.createElement("td");
        tdRunUID.innerHTML='<a class="top" title="'+runUID+'" data-placement="top" data-toggle="tooltip" href="#" data-original-title="">'+runUID.substr(0,5)+'...'+'</a>';
        tr.appendChild(tdRunUID);

        var tdOrigin=document.createElement("td");
        tdOrigin.innerHTML=origin;
        tr.appendChild(tdOrigin);

        var tdUIDS=document.createElement("td");
        tdUIDS.innerHTML=uids.substr(0,64);
        tr.appendChild(tdUIDS);

        var idOfAction="action"+index;
        var tdAction=document.createElement("td");
        var linkAction=document.createElement("a");
        linkAction.href="#";
        linkAction.id=idOfAction;
        linkAction.innerHTML=action;
        tdAction.appendChild(linkAction);
        tr.appendChild(tdAction);

        if (output.hasChildNodes() ){
            output.insertBefore(tr,output.firstChild)
        }else{
            output.appendChild(tr);
        }
        counter++;

        var isDestructive = ( action.indexOf("Delete") >= 0 );
        if (isDestructive ){
            $("#"+idOfAction).on("click", function(e){
                $("#outputResult").empty()
                displayResult('{"message":"'+uids+' has been deleted"}');
            });
        }else{
            $("#"+idOfAction).on("click", function(e){
                $("#outputResult").empty();
                displayResult(payload);
            });
        }


        $("#"+senderID).on("click", function(e){
            e.preventDefault();
            $("#outputResult").empty();
            GET("user","ReadUserById",'/'+sender);
        });
        return;
        
    });

}
