#!/bin/bash

# Specify the path to the OpenAPI specification file
openapi_file="openapi.yml"

# Remove the headers in the response section using yq
yq eval 'del(.. | .responses? | .[]? | .headers)' "$openapi_file" > "$openapi_file.tmp"

# Replace the original file with the updated contents
mv "$openapi_file.tmp" "$openapi_file"