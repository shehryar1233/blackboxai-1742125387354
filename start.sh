#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Print banner
echo -e "${GREEN}"
echo "=================================="
echo "  Smart Restaurant Server Starter"
echo "=================================="
echo -e "${NC}"

# Check system requirements
echo "Checking system requirements..."

# Check PHP
if command_exists php; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    echo -e "PHP: ${GREEN}Found${NC} (version $PHP_VERSION)"
    if (( $(echo "$PHP_VERSION < 7.4" | bc -l) )); then
        echo -e "${RED}Error: PHP 7.4 or higher is required${NC}"
        exit 1
    fi
else
    echo -e "${RED}Error: PHP is not installed${NC}"
    exit 1
fi

# Check Python (for development server)
if command_exists python3; then
    PYTHON_VERSION=$(python3 --version | cut -d " " -f 2)
    echo -e "Python: ${GREEN}Found${NC} (version $PYTHON_VERSION)"
else
    echo -e "${RED}Error: Python 3 is not installed${NC}"
    exit 1
fi

# Check if config file exists
if [ ! -f "backend/config/config.php" ]; then
    echo -e "${YELLOW}Warning: Configuration file not found${NC}"
    echo "Would you like to run the installation script? (y/n)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])+$ ]]; then
        php install.php
    else
        echo "Please run 'php install.php' to set up the configuration"
        exit 1
    fi
fi

# Check if required directories exist
REQUIRED_DIRS=("uploads" "logs" "cache")
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        echo -e "${YELLOW}Creating directory: $dir${NC}"
        mkdir -p "$dir"
        chmod 755 "$dir"
    fi
done

# Check if port 8000 is available
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null ; then
    echo -e "${RED}Error: Port 8000 is already in use${NC}"
    echo "Would you like to kill the process using port 8000? (y/n)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])+$ ]]; then
        lsof -ti:8000 | xargs kill -9
        echo "Process killed"
    else
        exit 1
    fi
fi

# Start the server
echo -e "\n${GREEN}Starting development server...${NC}"
echo "=================================="
echo -e "Frontend: ${GREEN}http://localhost:8000${NC}"
echo -e "Backend API: ${GREEN}http://localhost:8000/backend${NC}"
echo -e "Admin Dashboard: ${GREEN}http://localhost:8000/pages/admin/dashboard.html${NC}"
echo "=================================="
echo -e "${YELLOW}Press Ctrl+C to stop the server${NC}\n"

# Run the Python server
python3 server.py
