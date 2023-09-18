#!/bin/bash

# run command: ./p2omac/postman2openapi postman/Prometeus\ Accounting\ API.postman_collection.json > openapi.yml
./p2omac/postman2openapi postman/Prometeus\ Accounting\ API.postman_collection.json > openapi.yml

# run command: ./rm-header.sh

# check if ./rm-header.sh is exists
./rm-header.sh
# run command: python3 merge_yaml.py

# check if ./merge_yaml.py is exists and python3 is exists
python3 merge_yaml.py