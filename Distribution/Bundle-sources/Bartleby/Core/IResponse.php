<?php

namespace Bartleby\Core;

interface IResponse {
    function send();
}

interface  IHTTPResponse extends  IResponse{
    function getStatusCode();
}