# Swagger to Flexions 
 
**TODO CHECK the IMPLEMENTATION AND VALIDATE THIS DOCUMENT**
 
Our modeling approach was inspired by SWAGGER 2.0. **But it is not strictly compliant!**
We have added optional extensions : Bartleby Metadata. 
That's enough for modeling APIS and Entities with Flexions.


NOT SUPPORTED ANYMORE? 
IMPORTANT to support login and logout generation you must include the signature in the path
And add a security key that maps to the security definitions.

SWAGGER complete specs are available accessible [on github] (https://github.com/swagger-api/swagger-spec/blob/master/versions/2.0.md)

## Not supported ##

- "object" + "additionalProperties" Swagger usage of additionalProperties is not compliant with [JSON Schema](http://json-schema.org/example2.html)
- "consumes" and "produces" (as we generate both client and servers the generation template can decide to use JSON or XML or anything else)
- Resolution of $ref we extract the entity from the reference
- We prefer to use a fully typed approach so you should define (in definitions) and $ref an entity as much as possible.

# Actions extensions

## Entities Metadata 

The metadata model is extensible.

```Javascript
     		
			 "metadata": {
                "urdMode": false
            }
```
### Currently used keys :

+ urdMode (to specify to the generator if it should generate  a URD or CRUD stack. It can be used in templates by calling "$entityRepresentation->usesUrdMode()"

# Entities extensions


## Entities Metadata 

The metadata model is extensible.

```Javascript
     		
			 "metadata": {
                "urdMode": false,
                "persistsLocallyOnlyInMemory": false,
                "allowDistantPersistency": false,
                "undoable":false
            }
```
            

### Currently used keys :

+ urdMode (to specify to the generator if it should generate  a URD or CRUD stack. It can be used in templates by calling ```$entityRepresentation->usesUrdMode()```
+ persistsLocallyOnlyInMemory
+ allowDistantPersistency
+ undoable 


## Entities explicitType 

You can add an explicitType for an entity (that as not been generated)

```javascript
     "A": {
            "explicitType": "NSObject",
            "description": "A is ...",
            "allOf": []
            }
```



# Properties extensions

## Properties explicitType 

You can specify an explicit type (that is not necessarly generated) by specifying the type "object".

```javascript
 "dmgCard": {
	"description": "The associated dmg Card",
    "type": "object",
    "explicitType":"BsyncDMGCard"
},
```

You can use the **dictionary** type, for parameters & properties

```javascript
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


### A static function like Bartleby.arrayOfAliases()

```javascript
"episodes": {
	"schema": {	
		"type": "array",
 		"items": {
 			"$ref": "#/definitions/Alias",
  			"default": "Bartleby.arrayOfAliases()"
		}
	}
}
```

### or a native call like NSDate()

```javascript
      "startDate": {
         "type": "date",
       	"definition": "the starting date",
          "default": "NSDate()"
	}             
```