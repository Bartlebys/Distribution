# Bartleby's Flocker

## How to create the "Distribution folder"?

```
. php -f build.php args --source map.json
```

## How to create the Bundle.package(.zip) file 

```
. php -f pack.php args --source $BUNDLED_FOLDER_PATH  --destination $BUNDLE_FILE_DESTINATION_PATH
```

## How to unpack a bundle ?

```
. php -f unpack.php args --source $BUNDLE_FILE_SOURCE_PATH --destination $EXPANSION_PATH
```


## License 

Copyright 2O16 Benoit Pereira da Silva [Pereira-da-Silva.com](https://pereira-da-silva.com)

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

[ http://www.apache.org/licenses/LICENSE-2.0 ] ( http://www.apache.org/licenses/LICENSE-2.0 )

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
