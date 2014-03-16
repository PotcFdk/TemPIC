#!/bin/sh

# Set this depending on your setup
PATH_UPLOAD="/var/www/tempic/upload"

find "$PATH_UPLOAD/" -type f -mmin +60 ! -name ".gitkeep" -exec rm {} \;
