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