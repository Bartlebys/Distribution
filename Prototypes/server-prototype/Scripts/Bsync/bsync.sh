#!/usr/bin/env bash
#!/usr/bin/env bash



# Make the script stop on error
set -e

PAUSE_ON_STEP=false

if [ "$1" = "-i" ] ; then
    PAUSE_ON_STEP=true
fi

function step(){
    set +v
    if $PAUSE_ON_STEP
    then
        read -p "? "
    fi
    set -v
}

#BASE_URL=http://yd.local/api/v1/
BASE_URL=https://dev.api.lylo.tv/api/v1

# Make the script verbose
set -v

http GET ${BASE_URL}BartlebySync/isSupported
http GET ${BASE_URL}BartlebySync/reachable

step "Create local assets"

mkdir -p ~/Desktop/Samples/
touch ~/Desktop/Samples/text1.txt
echo "Eureka1" > ~/Desktop/Samples/text1.txt
touch ~/Desktop/Samples/text2.txt
echo "Eureka2" > ~/Desktop/Samples/text2.txt
touch ~/Desktop/Samples/hashmap.data
echo  "{ \"pthToH\": { \"text1.txt\" : 1812593931, \"text2.txt\": 1851787394 } }" > ~/Desktop/Samples/hashmap.data

step "Install the repository"

http -v -f POST ${BASE_URL}BartlebySync/install/

step "Creates trees"

http -v -f POST  ${BASE_URL}BartlebySync/create/tree/1 
http -v -f POST  ${BASE_URL}BartlebySync/create/tree/2
http -v -f POST  ${BASE_URL}BartlebySync/create/tree/3

step "Delete the tree 3"

http -v -f DELETE ${BASE_URL}BartlebySync/delete/tree/3

step "Touch the tree "1" to reset its public id, then try an unexisting ID"

http -v -f POST ${BASE_URL}BartlebySync/touch/tree/1

step "Try an unexisting ID"
http -v -f POST ${BASE_URL}BartlebySync/touch/tree/unexisting-tree

step "Try to Grab the hashmap that should not exists"

http -v GET  ${BASE_URL}BartlebySync/hashMap/tree/1/ redirect==true returnValue==false

step "Upload the files"

SYNC_ID="my_sync_id_"
http -v -f POST  ${BASE_URL}BartlebySync/uploadFileTo/tree/1/ destination='file1.txt' syncIdentifier=${SYNC_ID} source@~/Desktop/Samples/text1.txt
http -v -f POST  ${BASE_URL}BartlebySync/uploadFileTo/tree/1/ destination='file2.txt' syncIdentifier=${SYNC_ID} source@~/Desktop/Samples/text2.txt

step "Finalize the upload session"

# To remain simple we donnot inject the real hash map data but a placeholder.

http -v -f POST ${BASE_URL}BartlebySync/finalizeTransactionIn/tree/1/ commands='[[0 ,"file1.txt"],[0 ,"file2.txt"]]' syncIdentifier=${SYNC_ID} hashMap@~/Desktop/Samples/hashmap.data 

step "Down Stream samples"

step "Download a hashmap"

http -v GET  ${BASE_URL}BartlebySync/hashMap/tree/1/ redirect==false returnValue==true

step "Download a file"

http -v GET ${BASE_URL}BartlebySync/file/tree/1/ path=='file1.txt' redirect==false returnValue==true
http -v GET ${BASE_URL}BartlebySync/file/tree/1/ path=='file1.txt' redirect==true

step "Remove Ghosts"

http -v -f POST ${BASE_URL}BartlebySync/removeGhosts

