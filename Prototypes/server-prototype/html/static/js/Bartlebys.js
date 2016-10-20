/**
 * Calls method GET on an endpoint designated by its name
 * @param endPoint
 * @param headerKey
 * @param queryString
 */
function GET(endPoint, headerKey, queryString) {
    callEndPoint("GET",endPoint,headerKey,queryString,null);
}

/**
 * Calls method POST on an endpoint designated by its name
 * @param endPoint
 * @param headerKey
 * @param queryString
 * @param data
 */
function POST(endPoint, headerKey, queryString,data) {
    callEndPoint("POST",endPoint,headerKey,queryString,data);
}

/**
 * Calls method PUT on an endpoint designated by its name
 * @param endPoint
 * @param headerKey
 * @param queryString
 * @param data
 */
function PUT(endPoint, headerKey, queryString,data) {
    callEndPoint("PUT",endPoint,headerKey,queryString,data);
}

/**
 * Calls method DELETE on an endpoint designated by its name
 * @param endPoint
 * @param headerKey
 * @param queryString
 * @param data
 */
function DELETE(endPoint, headerKey, queryString,data) {
    callEndPoint("DELETE",endPoint,headerKey,queryString,data);
}


/**
 * Calls method POST on an endpoint designated by its name
 * @param endPoint
 * @param headerKey
 * @param queryString
 * @param data
 */
function callEndPoint(httpMethod,endPoint, headerKey, queryString,data) {
    url=baseApiURL+endPoint+queryString;
    var currentHttpHeaders=httpHeaders[headerKey];
    $.ajax({
        url:url,
        type: httpMethod,
        data: data,
        headers: currentHttpHeaders,
        success: function(data){
            displayResult(data);
        }
    });
}


/**
 * Filters a endPoint e.g: "ReadUsersByIds" becomes "users"
 * @param endPoint string
 * @returns string
 */
function filterEndPoint(endPoint) {
    var filteredEndPoint=endPoint.toLowerCase().replace('read','');
    filteredEndPoint=filteredEndPoint.replace('byids','');
    filteredEndPoint=filteredEndPoint.replace('byid','');
    return filteredEndPoint;
}


// Const

const EXCLUDE_TRIGGER_FROM_EXPORT_TOOL_PAGE=true;

