# Entities Modeling 

Bartleby's Json Modeling was intially inspired by Swagger.
It will converge as much as possible with [JSON Schema](http://json-schema.org/example2.html) 

Entities definitions are essential building block of flexions.
They are injected into templates and meta-template by the Flexers.

Understanding Entity Modeling is essential.

# An Organism

```json 
{
    "name": "Organism",
    "definition": {
        "description": "An Organism",
        "type": "object",
        "properties": {
            "domain":{
                "description": "The biological domain ",
                "type": "enum",
                "instanceOf": "string",
                "emumPreciseType": "Organism.Domain",
                "enum": [
                    "bacteria",
                    "archaea",
                    "eukaryota"
                ],
                "mutability":false,
                "serializable":false,
                "dynamic":false
            },
            "kingdom": {
                "description": "The kingdom",
                "type": "String",
                "default":"‎animalia"
            },
            "phylum‎": {
                "description": "Its phylum",
                "type": "string",
                "default":"‎chordata"
            },
            "class": {
                "description": "Its class",
                "type": "‎mammalia"
            },
            "order‎": {
              "description": "Its Order‎",
              "type": "String"
            },
            "family": {
              "description": "Damily",
              "type": "String"
            },
            "genus": {
              "description": "Genus",
              "type": "String"
            },
            "species": {
              "description": "species ",
              "type": "String"
            },
            "extincted": {
               "description": "extincted ",
               "type": "boolean"
           }
        },
        "metadata": {
            "urdMode": false,
            "persistsLocallyOnlyInMemory": false,
            "persistsDistantly": true,
            "undoable":false,
            "groupable":true
        }
    }
}

```


## Entities Metadata 

The metadata model is extensible.

```json
	 "metadata": {
        "urdMode": false,
        "persistsLocallyOnlyInMemory": false,
        "persistsDistantly": false,
        "undoable":false,
        "groupable":true
    }
```
            

### Currently used keys:

+ urdMode (to specify to the generator if it should generate  a URD or CRUD stack. It can be used in templates by calling ```$entityRepresentation->usesUrdMode()```
+ persistsLocallyOnlyInMemory ( is saved locally?)
+ persistsDistantly (create a CRUD stack)
+ undoable  ( undo manager support )
+ groupable (groupable on auto commit)


## Entities explicitType 

You can add an explicitType for an entity 

```json
 	"A": {
        "explicitType": "NSObject",
        "description": "A is ...",
        "allOf": []
        }
```


# Properties 

## dynamic 
mark as dynamic 

## serializable

In this case we don't want the property proxy to serialized

```json
  "proxy": {
      "explicitType": "JObject",
       "description": "",
        "dynamic": false,
         "serializable":false
  },
```


## supervisable

If a property is marked as supervisable any change will mark its parent as changed.

```json
"fruit": {
    ...
    "supervisable":false
},
```

## cryptable

If a property is marked as cryptable on serialization it should be crypted.

```json
	"password": {
	    ...
	    "cryptable":true
	},
```

## Properties explicitType 

You can specify an explicit type (that is not necessarly generated) by specifying the type "object".

```json
 "dmgCard": {
	"description": "The associated dmg Card",
    "type": "object",
    "explicitType":"BsyncDMGCard"
},
```

## Properties dictionaries

You can use the **dictionary** type, for parameters & properties

```json
	"parameters":[
		{
			"in": "body",
			"name": "sort",
			"description": "the sort (MONGO DB)",
			"required": true,
			"type": "dictionary"
		}
	]
```

# Tip and Tricks 

You can add **default native functions** if your generated targets can afford it!


### native functions

```json
      "startDate": {
         "type": "date",
       	"definition": "the starting date",
          "default": "NSDate()"
	}             
```



# @todo analysis 

```php
/*
   
   // Operation is a very special object.
   // Used By bartleby interact with a collaborative api
   // (!) Do not serialize globally the operation
   // as the operation will serialize this instance in its data dictionary.
   
   $_opUID_operation_rep=new PropertyRepresentation();
   $_opUID_operation_rep->name="_operation.registryUID";
   $_opUID_operation_rep->type=FlexionsTypes::STRING;
   $_opUID_operation_rep->required=true;
   $_opUID_operation_rep->default="\\(Default.NO_UID)";
   $_opUID_operation_rep->isGeneratedType=true;
   
   $_creatorUID_operation_rep=new PropertyRepresentation();
   $_creatorUID_operation_rep->name="_operation.creatorUID";
   $_creatorUID_operation_rep->type=FlexionsTypes::STRING;
   $_creatorUID_operation_rep->required=true;
   $_creatorUID_operation_rep->default="\\(Default.NO_UID)";
   $_creatorUID_operation_rep->isGeneratedType=true;
   
   $_status_operation_rep=new PropertyRepresentation();
   $_status_operation_rep->name="_operation.status";
   $_status_operation_rep->type=FlexionsTypes::ENUM;
   $_status_operation_rep->instanceOf="string";
   $_status_operation_rep->emumPreciseType="PushOperation.Status";
   $_status_operation_rep->required=true;
   $_status_operation_rep->default='.None';
   $_status_operation_rep->isGeneratedType=true;
   
   $_counter_operation_rep=new PropertyRepresentation();
   $_counter_operation_rep->name="_operation.counter";
   $_counter_operation_rep->type=FlexionsTypes::INTEGER;
   $_counter_operation_rep->required=true;
   $_counter_operation_rep->default=0;
   $_counter_operation_rep->isGeneratedType=true;
   
   $_creationDate_operation_rep=new PropertyRepresentation();
   $_creationDate_operation_rep->name="_operation.creationDate";
   $_creationDate_operation_rep->type=FlexionsTypes::DATETIME;
   $_creationDate_operation_rep->required=false;
   $_creationDate_operation_rep->isGeneratedType=true;
   
   
   $_operation_rep=new PropertyRepresentation();
   $_operation_rep->name="_operation";
   $_operation_rep->type="Operation";
   $_operation_rep->required=true;
   $_operation_rep->isDynamic=false;
   $_operation_rep->default="Operation()";
   $_operation_rep->isGeneratedType=true;
   
   
   
   // So we use a customSerializationMapping
   $_operation_rep->customSerializationMapping=array(
                                                       $_opUID_operation_rep,
                                                       $_creatorUID_operation_rep,
                                                       $_status_operation_rep,
                                                       $_counter_operation_rep,
                                                       $_creationDate_operation_rep
                                                   );
   $virtualEntity->properties[]=$_operation_rep;
   */
```