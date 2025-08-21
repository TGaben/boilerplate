#!/bin/bash

# Laravel Boilerplate - Quality Check Script
# =========================================
# 
# Ez a script automatikusan futtatja az összes minőségbiztosítási ellenőrzést
# a helyes sorrendben, és automatikusan javítja a talált problémákat.
#
# Használat:
#   ./scripts/quality-check.sh [OPTIONS]
#
# Opciók:
#   --skip-tests    A tesztek kihagyása (csak kódminőség ellenőrzés)
#   --fix-only      Csak a kódstílus javítás, ellenőrzések kihagyása
#   --help          Súgó megjelenítése

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Emoji for better UX
ROCKET="🚀"
TEST_TUBE="🧪"
PAINT="🎨"
MAGNIFIER="🔍"
WRENCH="🔧"
CHECK_MARK="✅"
CROSS_MARK="❌"
WARNING="⚠️"

# Default options
SKIP_TESTS=false
FIX_ONLY=false
SHOW_HELP=false

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --skip-tests)
            SKIP_TESTS=true
            shift
            ;;
        --fix-only)
            FIX_ONLY=true
            shift
            ;;
        --help)
            SHOW_HELP=true
            shift
            ;;
        *)
            echo -e "${RED}${CROSS_MARK} Ismeretlen opció: $1${NC}"
            echo "Használd a --help opciót a súgóhoz."
            exit 1
            ;;
    esac
done

# Show help
if [ "$SHOW_HELP" = true ]; then
    echo -e "${BLUE}${ROCKET} Laravel Boilerplate - Quality Check Script${NC}"
    echo ""
    echo "Használat:"
    echo "  ./scripts/quality-check.sh [OPTIONS]"
    echo ""
    echo "Opciók:"
    echo "  --skip-tests    A tesztek kihagyása (csak kódminőség ellenőrzés)"
    echo "  --fix-only      Csak a kódstílus javítás, ellenőrzések kihagyása"
    echo "  --help          Súgó megjelenítése"
    echo ""
    echo "Példák:"
    echo "  ./scripts/quality-check.sh                    # Teljes ellenőrzés"
    echo "  ./scripts/quality-check.sh --skip-tests       # Tesztek nélkül"
    echo "  ./scripts/quality-check.sh --fix-only         # Csak Pint javítás"
    exit 0
fi

echo -e "${BLUE}${ROCKET} Laravel Boilerplate Quality Check${NC}"
echo "======================================"
echo ""

# Check if we're in Laravel Sail environment
if ! command -v ./vendor/bin/sail &> /dev/null; then
    echo -e "${RED}${CROSS_MARK} Laravel Sail nem található!${NC}"
    echo "Győződj meg róla, hogy a projekt gyökérkönyvtárában vagy."
    exit 1
fi

# Function to run command with better output
run_command() {
    local cmd="$1"
    local description="$2"
    local emoji="$3"
    
    echo -e "${BLUE}${emoji} ${description}...${NC}"
    
    if eval "$cmd"; then
        echo -e "${GREEN}${CHECK_MARK} ${description} sikeres!${NC}"
        echo ""
        return 0
    else
        echo -e "${RED}${CROSS_MARK} ${description} sikertelen!${NC}"
        return 1
    fi
}

# Function to check and fix code style
check_and_fix_style() {
    echo -e "${YELLOW}${PAINT} Kódstílus ellenőrzése...${NC}"
    
    # First check if there are style issues
    if ./vendor/bin/sail composer lint > /dev/null 2>&1; then
        echo -e "${GREEN}${CHECK_MARK} Kódstílus rendben!${NC}"
        echo ""
        return 0
    else
        echo -e "${YELLOW}${WARNING} Kódstílus problémák találva, javítás folyamatban...${NC}"
        
        if ./vendor/bin/sail pint; then
            echo -e "${GREEN}${CHECK_MARK} Kódstílus javítások alkalmazva!${NC}"
            echo ""
            return 0
        else
            echo -e "${RED}${CROSS_MARK} Kódstílus javítás sikertelen!${NC}"
            return 1
        fi
    fi
}

# Start timestamp for performance measurement
START_TIME=$(date +%s)

# Only fix code style if --fix-only is specified
if [ "$FIX_ONLY" = true ]; then
    check_and_fix_style
    
    END_TIME=$(date +%s)
    DURATION=$((END_TIME - START_TIME))
    
    echo -e "${GREEN}${CHECK_MARK} Kódstílus javítás befejezve! (${DURATION}s)${NC}"
    exit 0
fi

# Step 1: Run tests (unless skipped)
if [ "$SKIP_TESTS" = false ]; then
    if ! run_command "./vendor/bin/sail artisan test" "Tesztek futtatása" "$TEST_TUBE"; then
        echo -e "${RED}${CROSS_MARK} A tesztek sikertelenek! Javítsd ki a hibákat és próbáld újra.${NC}"
        exit 1
    fi
fi

# Step 2: Check and fix code style
if ! check_and_fix_style; then
    echo -e "${RED}${CROSS_MARK} Kódstílus problémák nem javíthatók automatikusan!${NC}"
    exit 1
fi

# Step 3: Run static analysis
if ! run_command "./vendor/bin/sail composer stan" "Statikus kódelemzés (PHPStan)" "$MAGNIFIER"; then
    echo -e "${RED}${CROSS_MARK} PHPStan hibákat talált! Javítsd ki a típusproblémákat.${NC}"
    exit 1
fi

# Step 4: Final test run (only if code style was fixed and tests weren't skipped)
if [ "$SKIP_TESTS" = false ]; then
    # Check if any files were modified by Pint
    if ! git diff --quiet; then
        echo -e "${YELLOW}${WARNING} Kódstílus változások történtek, tesztek újrafuttatása...${NC}"
        if ! run_command "./vendor/bin/sail artisan test" "Végleges teszt futtatás" "$TEST_TUBE"; then
            echo -e "${RED}${CROSS_MARK} A végleges tesztek sikertelenek! A kódstílus javítások hibát okoztak.${NC}"
            exit 1
        fi
    fi
fi

# Calculate execution time
END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))

# Success message
echo -e "${GREEN}${CHECK_MARK}${CHECK_MARK}${CHECK_MARK} MINDEN MINŐSÉGI ELLENŐRZÉS SIKERES! ${CHECK_MARK}${CHECK_MARK}${CHECK_MARK}${NC}"
echo ""
echo -e "${PURPLE}📊 Összefoglaló:${NC}"
echo -e "   ${TEST_TUBE} Tesztek: $([ "$SKIP_TESTS" = true ] && echo "Kihagyva" || echo "Sikeres")"
echo -e "   ${PAINT} Kódstílus: Megfelelő (PSR-12)"
echo -e "   ${MAGNIFIER} Statikus elemzés: Hibamentes (PHPStan Max Level)"
echo -e "   ⏱️  Futási idő: ${DURATION} másodperc"
echo ""
echo -e "${GREEN}${ROCKET} Készen állsz a commit-ra és push-ra!${NC}"
echo ""
echo "Következő lépések:"
echo "  git add ."
echo "  git commit -m \"feat: új funkció hozzáadása\""
echo "  git push origin main"
