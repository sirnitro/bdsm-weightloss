#!/bin/bash

# Set base path for project
PROJECT_DIR="bdsmweightloss.com"
ROOT_DIR="${PROJECT_DIR}/public"

echo "Creating directory structure for ${PROJECT_DIR}..."

# Create top-level project directory and .env one level above public root
mkdir -p "${PROJECT_DIR}"
touch "${PROJECT_DIR}/.env"
echo "# Environment variables" > "${PROJECT_DIR}/.env"

# Create public root
mkdir -p "${ROOT_DIR}"

# Create subdirectories
mkdir -p "${ROOT_DIR}/css"
mkdir -p "${ROOT_DIR}/js"
mkdir -p "${ROOT_DIR}/inc"
mkdir -p "${ROOT_DIR}/templates"
mkdir -p "${ROOT_DIR}/pages"
mkdir -p "${ROOT_DIR}/members"
mkdir -p "${ROOT_DIR}/admin"
mkdir -p "${ROOT_DIR}/uploads/progress_photos"
mkdir -p "${ROOT_DIR}/assets/fonts"
mkdir -p "${ROOT_DIR}/assets/icons"
mkdir -p "${ROOT_DIR}/api"

# Create base files
touch "${ROOT_DIR}/index.php"
touch "${ROOT_DIR}/.htaccess"
touch "${ROOT_DIR}/css/styles.css"
touch "${ROOT_DIR}/js/main.js"

# Create inc files
cat <<EOL > "${ROOT_DIR}/inc/config.php"
<?php
// Load environment variables
\$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
\$dotenv->load();

// Autoload Composer packages
require '/var/www/vendor/autoload.php';

// Database and app configuration here
EOL

touch "${ROOT_DIR}/inc/functions.php"
touch "${ROOT_DIR}/inc/header.php"
touch "${ROOT_DIR}/inc/footer.php"
touch "${ROOT_DIR}/inc/auth.php"

# Create template files
touch "${ROOT_DIR}/templates/dom-profile.php"
touch "${ROOT_DIR}/templates/program-card.php"
touch "${ROOT_DIR}/templates/testimonial-block.php"

# Create page files
touch "${ROOT_DIR}/pages/about.php"
touch "${ROOT_DIR}/pages/blog.php"
touch "${ROOT_DIR}/pages/contact.php"
touch "${ROOT_DIR}/pages/programs.php"
touch "${ROOT_DIR}/pages/how-it-works.php"
touch "${ROOT_DIR}/pages/login.php"

# Create member files
touch "${ROOT_DIR}/members/dashboard.php"
touch "${ROOT_DIR}/members/progress.php"
touch "${ROOT_DIR}/members/journal.php"

# Create admin files
touch "${ROOT_DIR}/admin/index.php"
touch "${ROOT_DIR}/admin/users.php"
touch "${ROOT_DIR}/admin/content.php"
touch "${ROOT_DIR}/admin/logs.php"

# Create API files
touch "${ROOT_DIR}/api/login.php"
touch "${ROOT_DIR}/api/update_progress.php"
touch "${ROOT_DIR}/api/submit_journal.php"
touch "${ROOT_DIR}/api/fetch_programs.php"

echo "Directory and file structure created successfully!"

