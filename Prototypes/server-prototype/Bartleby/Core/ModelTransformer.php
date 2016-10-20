<?php

namespace Bartleby\Core;

interface IModelTransformer {
    function modelFromDictionary(array $dictionary);
}

abstract class Transformer implements  IModelTransformer{

    function modelFromDictionary(array $dictionary){
        return (new Model())->patchFromDictionary($dictionary);
    }
}
