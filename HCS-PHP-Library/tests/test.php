<?php

#incluse haylix storage php sdk file
require_once '../haylix.php';

#new storage class with account_name, user_name, password
$CS = new CloudStorage("testa27", "testu27", "testp");

#add new user, with new user name and new user password
#return 0 when create success
#return 1 when new user already exist
#return < 0 when fault
echo $CS->add_user("testu27_100", "testp");

#add new container with container name
#return 0 when create success
#return 1 when new container already exist
#return < 0 when fault
echo $CS->add_container("testc27_1");

#list all container in your account
#return a string list of container name, seperated by \r\n
#return empty string when no container
#return < 0 when fault
echo $CS->list_container();

#upload file to container, with container name and file path
#return 0 when upload file success
#return 1 when upload file already exist (use the new one you upload to replace the old one auto)
#return < 0 when fault
echo $CS->upload_file("testc27_1", "../haylix.php");

#download remote file from container to a local file, with container name, remote file name, local file name
#return 0 when download success
#return 1 when remote file not found
#return < 0 when fault
echo $CS->download_file("testc27_1", "haylix.php", "tttt");

#list all files in container , with container name
#return a string list of file name, seperated by \r\n
#return empty string when no file
#return < 0 when fault
echo $CS->list_file("testc27_1");

#look the file information, with container name and file name
#return file info string, seperated by \r\n
#return empty string when file not found
#return < 0 when fault
echo $CS->info_file("testc27_1", "haylix.php");

#delete a file, with container name and file name
#return 0 when delete success
#return 1 when file not found
#return < 0 when fault
echo $CS->del_file("testc27_1", "haylix.php");

#make a container public, with container name
#return 0 when success
#return 1 when container not found
#return < 0 when fault
echo $CS->public_container("testc27_1");

#make a container not public, with container name
#return 0 when success
#return 1 when container not found
#return < 0 when fault
echo $CS->unpublic_container("testc27_1");

#set public container access refer, with container name and refer string
#return 0 when success
#return 1 when container not found
#return < 0 when fault
echo $CS->refer_container("testc27_1", "*.example.com");

#look a container information, with container name
#return container info string , seperated by \r\n
#return empty string when container not found
#return < 0 when fault
echo $CS->info_container("testc27_1");

#make public container can only accessed by http request, with container name
#return 0 when success
#return 1 when container not found
#return < 0 when fault
echo $CS->http_container("testc27_1");

#make public container can only accessed by https request, with container name
#return 0 when success
#return 1 when container not found
#return < 0 when fault
echo $CS->https_container("testc27_1");

#set container read/write user, with container name, read user name array, write user name array
#if no read user, just let it be an empty string ""
#if no write user, just let it be an empty string ""
#return 0 when success
#return 1 when container not found
#return < 0 when fault
echo $CS->rw_container("testc27_1", array("testu27_100", "testu27_101"), array("testu27_102", "testu27_103"));

#list all user in your account
#return a json format string when success
#return < 0 when fault
echo $CS->list_user();

#delete a container with container name
#return 0 when success
#return 1 when container not found
#return < 0 when fault
echo $CS->del_container("testc27_1");

#delete a user in your account with user name
#return 0 when success
#return 1 when user not found
#return < 0 when fault
echo $CS->del_user("testu27_100");

?>
