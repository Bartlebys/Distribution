<?php
namespace Bartleby;
require_once dirname(__DIR__).'/Configuration.php';
use Bartleby\Configuration;

echo CR.shell_exec("git rev-parse HEAD 2>&1").CR;