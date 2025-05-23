#!/bin/bash

# Script to prepare the SahaCare project for deployment

# Create necessary directories
mkdir -p public/uploads/articles
chmod -R 777 public/uploads/articles

# Create entrypoints.json for Webpack
mkdir -p public/build
echo '{"entrypoints":{"app":{"js":[],"css":[]}}}' > public/build/entrypoints.json

# Create CKEditor directory
mkdir -p public/build/ckeditor
touch public/build/ckeditor/ckeditor.js

# Clear cache
php bin/console cache:clear

echo "Project preparation completed successfully!"
