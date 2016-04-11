# Bartleby's Bundler

Bartleby Bundler is a tool to distribute Bundles to be used by the bartleby Command Line Tool.It can be used as a commandline tool or via a web server.

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