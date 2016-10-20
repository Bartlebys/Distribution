<?php

namespace Bartleby\Core;

interface IResponse {
    function usePrettyPrint($enabled);
    function send();
}

interface  IHTTPResponse extends  IResponse{
    function getStatusCode();
}