// Infos::GET endPoint
$(function(){
    $("#getInfos").on("click", function(e){
        e.preventDefault();
        $("#outputResult").empty()
        GET('infos','Infos','');
    });
});

// Infos::GET endPoint
$(function(){
    $("#getExport").on("click", function(e){
        e.preventDefault();
        $("#outputResult").empty()
        GET('export','Export','?excludeTriggers='+EXCLUDE_TRIGGER_FROM_EXPORT_TOOL_PAGE);
    });
});