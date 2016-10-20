<?php

namespace Bartleby;

require_once dirname(__DIR__).'/Configuration.php';
use Bartleby\Configuration;

if (function_exists('sem_get')) {
    echo '"Semaphores" are enabled. '.CR;
} else {
    echo '"Semaphores" are not available! '.CR;
}
if (function_exists('shmop_read')) {
    echo '"shmop_read" is available. '.CR;
} else {
    echo '"shmop_read" is not available! '.CR;
}

if (function_exists('sem_get') ) {
    echo "Testing semaphore support!".CR;
    // Semaphore support
    $semaphoreIdentifier = crc32("TEST");
    $semResource = sem_get($semaphoreIdentifier, 1, 0666, 1);
    if (sem_acquire($semResource)) {
        echo "Semaphore $semaphoreIdentifier was acquired".CR;
        sleep(1);// Or do something blocking
        sem_release($semResource);
        echo "Semaphore $semaphoreIdentifier was released".CR;
    }else{
        echo "Semaphore $semaphoreIdentifier was not acquired ".CR;
    }
}