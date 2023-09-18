import yaml
import json

# Read the contents of openapi_header.yml
with open('openapi_header.yml', 'r') as header_file:
    header = yaml.load(header_file, Loader=yaml.FullLoader)

# Read the contents of openapi.yml
with open('openapi.yml', 'r') as openapi_file:
    openapi = yaml.load(openapi_file, Loader=yaml.FullLoader)

# Combine the contents
merged = {**openapi, **header}

# Save the merged content to a new file
with open('merged_openapi.yml', 'w') as merged_file:
    yaml.dump(merged, merged_file)