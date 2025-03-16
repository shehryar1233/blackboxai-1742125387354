#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Print banner
echo -e "${GREEN}"
echo "=================================="
echo "  Smart Restaurant Setup Script"
echo "=================================="
echo -e "${NC}"

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check PHP
if ! command_exists php; then
    echo -e "${RED}Error: PHP is not installed${NC}"
    exit 1
fi

# Check if running with sudo/root
if [ "$EUID" -ne 0 ]; then
    echo -e "${YELLOW}Warning: Some operations might require root privileges${NC}"
    echo "Continue anyway? (y/n)"
    read -r response
    if [[ ! "$response" =~ ^([yY][eE][sS]|[yY])+$ ]]; then
        echo "Please run this script with sudo"
        exit 1
    fi
fi

# Create required directories
echo -e "\n${GREEN}Creating required directories...${NC}"
mkdir -p uploads logs cache
chmod 755 uploads logs cache

# Run installation script
echo -e "\n${GREEN}Running installation script...${NC}"
if php install.php; then
    echo -e "${GREEN}Installation completed successfully${NC}"
else
    echo -e "${RED}Installation failed${NC}"
    exit 1
fi

# Run tests
echo -e "\n${GREEN}Running system tests...${NC}"
if php test.php; then
    echo -e "${GREEN}All tests passed${NC}"
else
    echo -e "${RED}Some tests failed${NC}"
    exit 1
fi

# Make start script executable
echo -e "\n${GREEN}Making start script executable...${NC}"
chmod +x start.sh

echo -e "\n${GREEN}Setup completed successfully!${NC}"
echo "=================================="
echo "You can now:"
echo "1. Start the server: ./start.sh"
echo "2. Access the application at: http://localhost:8000"
echo "3. Run tests anytime with: php test.php"
echo "=================================="

# Ask if user wants to start the server now
echo -e "\nWould you like to start the server now? (y/n)"
read -r response
if [[ "$response" =~ ^([yY][eE][sS]|[yY])+$ ]]; then
    ./start.sh
fi
