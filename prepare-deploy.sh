#!/bin/bash
#=============================================================================
# PHP Matrimony - Deployment Preparation Script
#=============================================================================
# This script prepares your project for deployment to Hostinger
# Run this script before uploading files to your hosting
#=============================================================================

echo "=========================================="
echo "PHP Matrimony - Deployment Preparation"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Create deployment directory
DEPLOY_DIR="deploy_package"
echo -e "${YELLOW}Creating deployment package...${NC}"

# Remove old deployment directory if exists
rm -rf "$DEPLOY_DIR"
mkdir -p "$DEPLOY_DIR"

# Copy all necessary files
echo "Copying files..."

# PHP files
cp -r *.php "$DEPLOY_DIR/" 2>/dev/null
cp -r admin "$DEPLOY_DIR/"
cp -r api "$DEPLOY_DIR/"
cp -r auth "$DEPLOY_DIR/"
cp -r includes "$DEPLOY_DIR/"

# Static assets
cp -r css "$DEPLOY_DIR/"
cp -r js "$DEPLOY_DIR/"
cp -r fonts "$DEPLOY_DIR/"
cp -r images "$DEPLOY_DIR/"

# Database files
mkdir -p "$DEPLOY_DIR/db"
cp -r db/*.sql "$DEPLOY_DIR/db/" 2>/dev/null
cp -r db/migrations "$DEPLOY_DIR/db/" 2>/dev/null

# Create required directories
mkdir -p "$DEPLOY_DIR/uploads"
mkdir -p "$DEPLOY_DIR/uploads/homepage"
mkdir -p "$DEPLOY_DIR/uploads/banners"
mkdir -p "$DEPLOY_DIR/profile"

# Copy .htaccess template
cp htaccess-hostinger.txt "$DEPLOY_DIR/.htaccess"

# Copy config sample
cp config.sample.php "$DEPLOY_DIR/config.sample.php"

# Copy install script
cp install.php "$DEPLOY_DIR/"

# Copy documentation
cp HOSTINGER_SETUP.md "$DEPLOY_DIR/"
cp README.md "$DEPLOY_DIR/"

# Remove development files
echo "Removing development files..."
rm -f "$DEPLOY_DIR/debug.php" 2>/dev/null
rm -rf "$DEPLOY_DIR/.git" 2>/dev/null
rm -f "$DEPLOY_DIR/.gitignore" 2>/dev/null
rm -rf "$DEPLOY_DIR/phpmyadmin" 2>/dev/null

# Create placeholder files for empty directories
touch "$DEPLOY_DIR/uploads/.gitkeep"
touch "$DEPLOY_DIR/uploads/homepage/.gitkeep"
touch "$DEPLOY_DIR/uploads/banners/.gitkeep"
touch "$DEPLOY_DIR/profile/.gitkeep"

# Create zip file
echo ""
echo -e "${YELLOW}Creating ZIP file...${NC}"
cd "$DEPLOY_DIR"
zip -r "../matrimony-deploy.zip" . -x "*.DS_Store" -x "*__MACOSX*"
cd ..

# Calculate size
SIZE=$(du -sh matrimony-deploy.zip | cut -f1)

echo ""
echo -e "${GREEN}=========================================="
echo "Deployment Package Ready!"
echo "==========================================${NC}"
echo ""
echo "Package Location: matrimony-deploy.zip"
echo "Package Size: $SIZE"
echo ""
echo "Next Steps:"
echo "1. Upload matrimony-deploy.zip to Hostinger"
echo "2. Extract to public_html folder"
echo "3. Import db/matrimony.sql via phpMyAdmin"
echo "4. Visit http://yourdomain.com/install.php"
echo "5. Follow the installation wizard"
echo ""
echo -e "${YELLOW}Important:${NC}"
echo "- Delete install.php after installation"
echo "- Change admin password (default: admin123)"
echo ""
