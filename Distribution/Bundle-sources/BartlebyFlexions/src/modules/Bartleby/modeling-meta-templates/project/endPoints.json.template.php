<?php

include  FLEXIONS_SOURCE_DIR.'/Shared.php';

// URD == Upsert Read Delete
// CRUD == Create Read Update Delete

/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    $f->fileName = 'pathsFragment.json';
    $f->package = '';
}

/* TEMPLATES STARTS HERE -> */?>
{ "paths" : {
<?php
/* @var $d ProjectRepresentation */
/* @var $entity EntityRepresentation */

$counter=0;
$blocks=array();
foreach ($d->entities as $entity ) {
    $name = $entity->name;

    if (isset($prefix)) {
        $name = str_replace($prefix, '', $name);
    }


    //$pluralized=lcfirst(P

    //luralization::pluralize($name));
    $counter++;

    // EXCLUSION FROM CRUD
    // You can exclude entities containing a given string

    $shouldBeExcluded = false;
    $exclusion = array();
    if (isset($excludeActionsWith)) {
        $exclusion = $excludeActionsWith;
    }
    foreach ($exclusion as $exclusionString) {
        if (strpos($name, $exclusionString) !== false) {
            $shouldBeExcluded = true;
        }
    }

    if ($shouldBeExcluded) {
        continue;//Let's exclude this entity from the CRUD
    }


    // UPDATE EXCLUSION
    $isUnModifiable = false;
    $unModifiable = array();
    if (isset($unModifiableEntitiesWith)) {
        $unModifiable = $unModifiableEntitiesWith;
    }
    foreach ($unModifiable as $unModifiableString) {
        if (strpos($name, $unModifiableString) !== false) {
            $isUnModifiable = true;
        }
    }

    // DELETION EXCLUSION
    $isUndeletable = false;
    $undeletable = array();
    if (isset($unDeletableEntitiesWith)) {
        $undeletable = $unDeletableEntitiesWith;
    }
    foreach ($undeletable as $undeletableString) {
        if (strpos($name, $undeletableString) !== false) {
            $isUndeletable = true;
        }
    }

    $pluralizedName = lcfirst(Pluralization::pluralize($name));


    ////////////////////////////
    // SINGLE INSTANCE
    ///////////////////////////


    // The read block is the only one with the id in the path
    $readBlock = '
        "/' . lcfirst($name) . '/{' . lcfirst($name) . 'Id}" : {
        "get" : {
            "tags" : [
                "' . $pluralizedName . '"
            ],
            "summary" : "Find ' . $name . ' by ID",
            "description" : "Returns a single ' . $name . '",
            "operationId" : "read' . ucfirst($name) . 'ById",
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
              {
                "name" : "' . lcfirst($name) . 'Id",
                "in" : "path",
                "description" : "The unique identifier the the of ' . $name . '",
                "required" : true,
                "type" : "string"
              }


            ],
            "responses" : {
                    "200" : {
                        "description" : "successful operation",
                         "schema" : {
                            "$ref" : "#/definitions/' . ucfirst($name) . '"
                        }
                    },
                    "404" : {
                         "description" : "' . ucfirst($name) . ' not found"
                    }
            }
          }
        },';

    if ($entity->usesUrdMode() == true) {
        $upsertBlock = '
    "/' . lcfirst($name) . '" :
     {
        "post" : {
            "tags" : [
                "' . $pluralizedName . '"
            ],
        "summary" : "Creates or insert a new ' . $name . ' to the system",
        "description" : "",
        "operationId" : "upsert' . ucfirst($name) . '",
        "consumes" : [
                "application/json"
            ],
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
            "in" : "body",
            "name" : "' . lcfirst($name) . '",
            "description" : "The instance of' . $name . ' that needs to be inserted or updated",
            "required" : true,
            "schema" : {
              "$ref" : "#/definitions/' . ucfirst($name) . '"
            }
          }
        ],
        "responses" : {
                "405" : {
                    "description" : "Invalid input"
          }
        },
         "metadata": {
              "urdMode": true
          }
      }
    ';

    } else {

        $createBlock = '
    "/' . lcfirst($name) . '" :
     {
        "post" : {
            "tags" : [
                "' . $pluralizedName . '"
            ],
        "summary" : "Creates a new ' . $name . ' to the system",
        "description" : "",
        "operationId" : "create' . ucfirst($name) . '",
        "consumes" : [
                "application/json"
            ],
        "produces" : [
                "application/json"
            ],
        "parameters" : [


          {
            "in" : "body",
            "name" : "' . lcfirst($name) . '",
            "description" : "The instance of' . $name . ' that needs to be added",
            "required" : true,
            "schema" : {
              "$ref" : "#/definitions/' . ucfirst($name) . '"
            }
          }
        ],
        "responses" : {
                "405" : {
                    "description" : "Invalid input"
          }
        },
         "metadata": {
              "urdMode": false
          }
      }
    ';


        $updateBlock = ',
        "put" : {
            "tags" : [
                "' . $pluralizedName . '"
            ],
        "summary" : "Update an existing ' . $name . '",
        "description" : "",
        "operationId" : "update' . ucfirst($name) . '",
        "consumes" : [
                "application/json"
            ],
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
            "in" : "body",
            "name" : "' . lcfirst($name) . '",
            "description" : "The ' . ucfirst($name) . ' instance to update",
            "required" : true,
            "schema" : {
              "$ref" : "#/definitions/' . ucfirst($name) . '"
            }
          }
        ],
        "responses" : {
          "400" : {
             "description" : "Invalid ID supplied"
          },
          "404" : {
                    "description" : "' . ucfirst($name) . ' not found"
          },
          "405" : {
                    "description" : "Validation exception"
          }
        }
      }';

    }


    $deleteBlock = ',
        "delete" : {
            "tags" : [
                "' . $pluralizedName . '"
            ],
            "summary" : "Deletes a ' . $name . '",
            "description" : "",
            "operationId" : "delete' . ucfirst($name) . '",
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
              {
                "name" : "' . lcfirst($name) . 'Id",
                "in" : "path",
                "description" : "The identifier of the ' . ucfirst($name) . ' to be deleted",
                "required" : true,
                "type" : "string"
              }
            ],
            "responses" : {
                    "400" : {
                        "description" : "Invalid ' . $name . ' value"
              }
            }
        }
     ';

    $block = $readBlock;
    if ($entity->usesUrdMode() == true) {
        $block .= $upsertBlock;
    } else {
        $block .= $createBlock;
        if ($isUnModifiable == false) {
            $block .= $updateBlock;
        }
    }

    if ($isUndeletable == false) {
        $block .= $deleteBlock;
    }
    $block .= '}';
    $block .= ',';


    ////////////////////////////
    // COLLECTIONS
    ////////////////////////////

    if ($entity->usesUrdMode() == true) {

        $upsertCollectionBlock = '
    "/' . lcfirst($pluralizedName) . '" : {
        "post" : {
            "tags" : [
                "' . $pluralizedName . '"
            ],
            "summary" : "Creates or updates ' . $pluralizedName . '",
            "description" : "",
            "operationId" : "upsert' . ucfirst($pluralizedName) . '",
            "consumes" : [
                    "application/json"
                ],
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
             {
                "in" : "body",
                "name" : "' . lcfirst($pluralizedName) . '",
                "description" : "Collection of ' . $name . ' that needs to be upserted",
                "required" : true,
                "schema": {
                            "type": "array",
                            "items":
                            {
                                "$ref": "#/definitions/' . ucfirst($name) . '"
                             }
                         }
              }
            ],
            "responses" : {
                    "405" : {
                        "description" : "Invalid input"
                     }
            },
         "metadata": {
              "urdMode": true
          }
     },
    ';
    } else {

        $createCollectionBlock = '
    "/' . lcfirst($pluralizedName) . '" : {
        "post" : {
            "tags" : [
                "' . $pluralizedName . '"
            ],
            "summary" : "Create ' . $pluralizedName . ' to the system",
            "description" : "",
            "operationId" : "create' . ucfirst($pluralizedName) . '",
            "consumes" : [
                    "application/json"
                ],
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
             {
                "in" : "body",
                "name" : "' . lcfirst($pluralizedName) . '",
                "description" : "Collection of ' . $name . ' that needs to be added",
                "required" : true,
                "schema": {
                            "type": "array",
                            "items":
                            {
                                "$ref": "#/definitions/' . ucfirst($name) . '"
                             }
                         }
              }
            ],
            "responses" : {
                    "405" : {
                        "description" : "Invalid input"
                     }
            },
         "metadata": {
              "urdMode": false
          }
     },
    ';

    }
    $readCollectionBlock = '    "get" : {
            "tags" : [
                "' . $pluralizedName . '"
            ],
            "summary" : "Find ' . $pluralizedName . ' by ID",
            "description" : "Returns a collection of ' . $name . '",
            "operationId" : "read' . ucfirst($pluralizedName) . 'ByIds",
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
              {
                "name" : "ids",
                "in" : "path",
                "description" : "The IDS of the ' . $pluralizedName . ' to return",
                "required" : true,
                 "type": "array",
                 "items": {
                     "type": "string"
                  }
              },
              {
                "in" : "body",
                "name" : "result_fields",
                "description" : "the result fields (MONGO DB)",
                "required" : true,
                "schema": {
                            "type": "array",
                            "items":
                            {
                                "type": "string"
                             }
                         }
              },
              {
                "in" : "body",
                "name" : "sort",
                "description" : "the sort (MONGO DB)",
                "required" : false,
                "type":  "dictionary"
               }
            ],
            "responses" : {
               "200" : {

                       "description" : "successful operation",
                       "schema": {
                            "type": "array",
                            "items": {
                                "$ref": "#/definitions/' . ucfirst($name) . '"
                            }
                       }

              },
              "400" : {
                        "description" : "Invalid IDS supplied"
              },
              "404" : {
                        "description" : "' . ucfirst($pluralizedName) . ' not found"
              }
            }
          }
        ';

    if ($entity->usesUrdMode() == false) {
        $updateCollectionBlock = ',
        "put" : {
            "tags" : [
                "' . $pluralizedName . '"
            ],
            "summary" : "Update an existing ' . $name . '",
            "description" : "",
            "operationId" : "update' . ucfirst($pluralizedName) . '",
            "consumes" : [
                    "application/json"
                ],
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
              {
                "in" : "body",
                "name" : "' . lcfirst($pluralizedName) . '",
                "description" : "Collection of ' . ucfirst($name) . ' to update",
                "required" : true,
                "schema": {
                            "type": "array",
                            "items": {
                                "$ref": "#/definitions/' . ucfirst($name) . '"
                             }
                    }
              }
            ],
            "responses" : {
              "400" : {
                 "description" : "Invalid IDS supplied"
              },
              "404" : {
                        "description" : "' . ucfirst($pluralizedName) . ' not found"
              },
              "405" : {
                        "description" : "Validation exception"
              }
            }
      }';

    }

    $deleteCollectionBlock = ',
        "delete" : {

            "tags" : [
                "' . $pluralizedName . '"
            ],
            "summary" : "Deletes some ' . $pluralizedName . '",
            "description" : "",
            "operationId" : "delete' . ucfirst($pluralizedName) . '",
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
              {
                "name" : "ids",
                "in" : "path",
                "description" : "The ids of ' . $pluralizedName . ' to delete",
                "required" : true,
                 "type": "array",
                 "items": {
                     "type": "string"
                  }
              }
            ],
            "responses" : {
                    "400" : {
                        "description" : "Invalid ' . $pluralizedName . ' value"
                    }
            }
        }';


    if ($entity->usesUrdMode() == true) {

        $block .= $upsertCollectionBlock;
        $block .= $readCollectionBlock;

        if ($isUndeletable == false) {
            $block .= $deleteCollectionBlock;
        }

        $block .= cr() . '}';
        $block .= ',';

    } else {

        $block .= $createCollectionBlock;
        $block .= $readCollectionBlock;
        if ($isUnModifiable == false) {
            $block .= $updateCollectionBlock;
        }
        if ($isUndeletable == false) {
            $block .= $deleteCollectionBlock;
        }
        $block .= cr() . '}';
        $block .= ',';

    }


    ////////////////////////////
    // GETTER BY QUERY
    ////////////////////////////


    // We use POST to pass a query
    // Other Reading endpoint are cachable not those one ()

    $genericQueryGetPathBlock = '
        "/' . lcfirst($pluralizedName) . 'ByQuery" : {
            "post" : {
                "tags" : [
                    "' . $pluralizedName . '"
                ],
                "summary" : "Find ' . $pluralizedName . ' by query (check $q, $s, $f in Bartleby\'s MongoCallDataRawWrapper)",
                "description" : "Returns a collection of ' . $name . '",
                "operationId" : "read' . ucfirst($pluralizedName) . 'ByQuery",
                "produces" : [
                        "application/json"
                    ],
                 "parameters" : [
                     {
                        "in" : "body",
                        "name" : "result_fields",
                        "description" : "the result fields (MONGO DB)",
                        "required" : true,
                        "schema": {
                                    "type": "array",
                                    "items":
                                    {
                                        "type": "string"
                                     }
                                 }
                      },
                       {
                        "in" : "body",
                        "name" : "sort",
                        "description" : "the sort (MONGO DB)",
                        "required" : false,
                        "type":  "dictionary"
                      },
                      {
                        "in" : "body",
                        "name" : "query",
                        "description" : "the query (MONGO DB)",
                        "required" : false,
                        "type":  "dictionary"
                      }
                  ]
                ,
                "responses" : {
                   "200" : {
                             "description" : "successful operation",
                              "schema": {
                                    "type": "array",
                                    "items": {
                                        "$ref": "#/definitions/' . ucfirst($name) . '"
                                    }
                              }
                   },
                  "400" : {
                            "description" : "Invalid IDS supplied"
                  },
                  "404" : {
                            "description" : "' . ucfirst($pluralizedName) . ' not found"
                  }
                }
            }
        },';
    $block .= $genericQueryGetPathBlock;
    $blocks[] = $block;
}
// Let's echo the blocks
$nbOfBlocks=count($blocks);
for ($i=0;$i<$nbOfBlocks;$i++){
    $currentBlock=$blocks[$i];
    if($i==$nbOfBlocks-1){
        // Delete the last comma.
        $currentBlock=substr($currentBlock,0,strlen($currentBlock)-1);
    }
    echo($currentBlock);
}
?>
    }
}
<?php /*<- END OF TEMPLATE */

?>