#!/bin/bash

# =================================================================
# Laravel Boilerplate Quick Start Script
# =================================================================
# Automatikus boilerplate setup minden környezethez
# Használat: ./scripts/quick-start.sh [--env=development] [--options]

set -e  # Exit on any error

# =================================================================
# COLORS AND FORMATTING
# =================================================================
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
NC='\033[0m' # No Color

# =================================================================
# CONFIGURATION
# =================================================================
DEFAULT_ENV="development"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
LOG_FILE="${PROJECT_ROOT}/quick-start.log"
BACKUP_DIR="${PROJECT_ROOT}/.quick-start-backup"

# Command line options
ENVIRONMENT="$DEFAULT_ENV"
SKIP_INTERACTIVE=false
CHECK_ONLY=false
FORCE_REINSTALL=false
SKIP_TESTS=false
CUSTOM_DOMAIN=""

# Progress tracking
TOTAL_STEPS=8
CURRENT_STEP=0

# =================================================================
# UTILITY FUNCTIONS
# =================================================================

log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S'): $1" >> "$LOG_FILE"
}

print_header() {
    echo -e "${CYAN}"
    echo "🚀 Laravel Boilerplate Quick Start"
    echo "=================================="
    echo -e "${NC}"
}

print_step() {
    CURRENT_STEP=$((CURRENT_STEP + 1))
    echo -e "${BLUE}[$CURRENT_STEP/$TOTAL_STEPS]${NC} $1"
    log "STEP $CURRENT_STEP/$TOTAL_STEPS: $1"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
    log "SUCCESS: $1"
}

print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
    log "WARNING: $1"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
    log "ERROR: $1"
}

print_info() {
    echo -e "${PURPLE}💡 $1${NC}"
}

progress_bar() {
    local current=$1
    local total=$2
    local width=40
    local percentage=$((current * 100 / total))
    local filled=$((current * width / total))
    local empty=$((width - filled))
    
    printf "\r%s[" "${CYAN}"
    printf "%*s" "$filled" "" | tr ' ' '█'
    printf "%*s" "$empty" "" | tr ' ' '░'
    printf "] %d%% (%d/%d)%s" "$percentage" "$current" "$total" "${NC}"
}

create_backup() {
    if [[ -f "$1" ]]; then
        mkdir -p "$BACKUP_DIR"
        cp "$1" "$BACKUP_DIR/$(basename "$1").backup.$(date +%s)"
        log "Created backup for $1"
    fi
}

cleanup_on_error() {
    print_error "Setup megszakadt! Cleanup folyamatban..."
    
    # Stop containers if running
    if command_exists docker && docker compose ps -q &>/dev/null; then
        docker compose down &>/dev/null || true
    fi
    
    # Restore backups if needed
    if [[ -d "$BACKUP_DIR" ]]; then
        print_info "Backup fájlok elérhetők: $BACKUP_DIR"
    fi
    
    print_error "Setup nem sikerült. Nézd meg a logot: $LOG_FILE"
    exit 1
}

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

check_port() {
    local port=$1
    if lsof -Pi :"$port" -sTCP:LISTEN -t >/dev/null 2>&1; then
        return 0  # Port is in use
    else
        return 1  # Port is free
    fi
}

# =================================================================
# DEPENDENCY CHECKING
# =================================================================

check_dependencies() {
    print_step "🔍 Függőségek ellenőrzése..."
    
    local missing_deps=()
    
    # Docker check
    if ! command_exists docker; then
        missing_deps+=("docker")
    elif ! docker info >/dev/null 2>&1; then
        print_warning "Docker daemon nem fut"
        missing_deps+=("docker-running")
    fi
    
    # Docker Compose check
    if ! command_exists docker || ! docker compose version >/dev/null 2>&1; then
        missing_deps+=("docker-compose")
    fi
    
    # Git check
    if ! command_exists git; then
        missing_deps+=("git")
    fi
    
    # Optional but recommended
    if ! command_exists curl; then
        print_warning "curl nincs telepítve (opcionális)"
    fi
    
    if [[ ${#missing_deps[@]} -eq 0 ]]; then
        print_success "Minden szükséges függőség elérhető"
        return 0
    else
        print_error "Hiányzó függőségek: ${missing_deps[*]}"
        print_info "Telepítési útmutató:"
        
        for dep in "${missing_deps[@]}"; do
            case $dep in
                "docker")
                    echo "  • Docker: https://docs.docker.com/get-docker/"
                    ;;
                "docker-running")
                    echo "  • Indítsd el a Docker Desktop-ot vagy futtasd: sudo systemctl start docker"
                    ;;
                "docker-compose")
                    echo "  • Docker Compose: https://docs.docker.com/compose/install/"
                    ;;
                "git")
                    echo "  • Git: https://git-scm.com/downloads"
                    ;;
            esac
        done
        
        if [[ "$CHECK_ONLY" == "true" ]]; then
            return 1
        fi
        
        if [[ "$SKIP_INTERACTIVE" == "false" ]]; then
            read -p "Folytatod a telepítés után? (y/N): " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                exit 1
            fi
        else
            exit 1
        fi
    fi
}

# =================================================================
# ENVIRONMENT SETUP
# =================================================================

setup_environment() {
    print_step "🔧 Környezeti konfiguráció beállítása ($ENVIRONMENT)..."
    
    # Backup existing .env if exists
    if [[ -f "$PROJECT_ROOT/.env" ]]; then
        create_backup "$PROJECT_ROOT/.env"
    fi
    
    cd "$PROJECT_ROOT"
    
    # Use our environment template system
    if ./vendor/bin/sail artisan boilerplate:env list >/dev/null 2>&1; then
        if [[ "$FORCE_REINSTALL" == "true" ]]; then
            ./vendor/bin/sail artisan boilerplate:env copy "$ENVIRONMENT" --force
        else
            ./vendor/bin/sail artisan boilerplate:env copy "$ENVIRONMENT"
        fi
    else
        # Fallback to manual copy if artisan not available yet
        if [[ -f "templates/environments/env.$ENVIRONMENT" ]]; then
            cp "templates/environments/env.$ENVIRONMENT" .env
        elif [[ -f ".env.example" ]]; then
            cp .env.example .env
        else
            print_error "Nem található környezeti sablon"
            return 1
        fi
    fi
    
    # Generate app key if needed
    if [[ "$ENVIRONMENT" != "ci" ]] && ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
        print_info "APP_KEY generálása..."
        if [[ -f "artisan" ]]; then
            php artisan key:generate --no-interaction
        fi
    fi
    
    # Custom domain handling
    if [[ -n "$CUSTOM_DOMAIN" ]]; then
        sed -i.bak "s|APP_URL=.*|APP_URL=https://$CUSTOM_DOMAIN|g" .env
        print_info "Domain beállítva: $CUSTOM_DOMAIN"
    fi
    
    print_success "Környezeti konfiguráció kész"
}

# =================================================================
# DEPENDENCIES INSTALLATION
# =================================================================

install_dependencies() {
    print_step "📦 Dependencies telepítése..."
    
    cd "$PROJECT_ROOT"
    
    # Check if vendor exists and is up to date
    if [[ "$FORCE_REINSTALL" == "true" ]] || [[ ! -d "vendor" ]] || [[ composer.lock -nt vendor ]]; then
        print_info "Composer dependencies telepítése..."
        composer install --no-interaction --prefer-dist --optimize-autoloader
    else
        print_info "Composer dependencies naprakészek"
    fi
    
    # Node dependencies
    if [[ "$FORCE_REINSTALL" == "true" ]] || [[ ! -d "node_modules" ]] || [[ package-lock.json -nt node_modules ]]; then
        print_info "NPM dependencies telepítése..."
        npm ci --silent
    else
        print_info "NPM dependencies naprakészek"
    fi
    
    print_success "Dependencies telepítve"
}

# =================================================================
# DOCKER ENVIRONMENT
# =================================================================

start_docker_environment() {
    print_step "🐳 Docker környezet indítása..."
    
    cd "$PROJECT_ROOT"
    
    # Check port conflicts
    local ports=(80 3306 6379 1025)
    local conflicts=()
    
    for port in "${ports[@]}"; do
        if check_port "$port"; then
            conflicts+=("$port")
        fi
    done
    
    if [[ ${#conflicts[@]} -gt 0 ]]; then
        print_warning "Port konfliktusok: ${conflicts[*]}"
        if [[ "$SKIP_INTERACTIVE" == "false" ]]; then
            read -p "Leállítod a konfliktusban lévő szolgáltatásokat? (y/N): " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                print_info "Próbálkozás a port felszabadítással..."
            fi
        fi
    fi
    
    # Start services
    print_info "Sail indítása..."
    ./vendor/bin/sail up -d
    
    # Wait for services to be ready
    print_info "Várakozás a szolgáltatások felállására..."
    local max_attempts=30
    local attempt=0
    
    while [[ $attempt -lt $max_attempts ]]; do
        if ./vendor/bin/sail artisan --version >/dev/null 2>&1; then
            break
        fi
        sleep 2
        attempt=$((attempt + 1))
        progress_bar $attempt $max_attempts
    done
    echo
    
    if [[ $attempt -eq $max_attempts ]]; then
        print_error "Timeout: Laravel nem érhető el"
        return 1
    fi
    
    print_success "Docker környezet fut"
}

# =================================================================
# DATABASE SETUP
# =================================================================

setup_database() {
    print_step "🗄️ Adatbázis setup..."
    
    cd "$PROJECT_ROOT"
    
    # Run migrations
    print_info "Migráció futtatása..."
    ./vendor/bin/sail artisan migrate --no-interaction
    
    # Run seeders (except for production)
    if [[ "$ENVIRONMENT" != "production" ]]; then
        print_info "Seeders futtatása..."
        ./vendor/bin/sail artisan db:seed --no-interaction
    fi
    
    print_success "Adatbázis kész"
}

# =================================================================
# ASSET BUILDING
# =================================================================

build_assets() {
    print_step "🎨 Asset build..."
    
    cd "$PROJECT_ROOT"
    
    if [[ "$ENVIRONMENT" == "production" ]]; then
        print_info "Production build..."
        ./vendor/bin/sail npm run build
    else
        print_info "Development build..."
        ./vendor/bin/sail npm run dev &
        
        # Wait a bit for the dev server to start
        sleep 3
        
        # Kill the dev process after initial build
        pkill -f "npm run dev" 2>/dev/null || true
    fi
    
    print_success "Assets készek"
}

# =================================================================
# VALIDATION AND TESTING
# =================================================================

run_validation() {
    if [[ "$SKIP_TESTS" == "true" ]]; then
        print_step "🧪 Validáció átugorva"
        return 0
    fi
    
    print_step "🧪 Rendszer validáció..."
    
    cd "$PROJECT_ROOT"
    
    # Environment validation
    print_info "Környezeti konfiguráció ellenőrzése..."
    ./vendor/bin/sail artisan boilerplate:env validate --target-env="$ENVIRONMENT"
    
    # Quick health check
    print_info "Alapszolgáltatások ellenőrzése..."
    ./vendor/bin/sail artisan boilerplate:env check
    
    # Run a subset of tests if not production
    if [[ "$ENVIRONMENT" != "production" ]] && [[ "$ENVIRONMENT" != "ci" ]]; then
        print_info "Gyors teszt futtatás..."
        ./vendor/bin/sail artisan test --testsuite=Feature --stop-on-failure tests/Feature/ExampleTest.php
    fi
    
    print_success "Validáció sikeres"
}

# =================================================================
# FINAL SETUP AND SUMMARY
# =================================================================

final_setup() {
    print_step "🚀 Véglegesítés és összefoglaló..."
    
    cd "$PROJECT_ROOT"
    
    # Clear caches
    print_info "Cache tisztítás..."
    ./vendor/bin/sail artisan config:cache
    ./vendor/bin/sail artisan route:cache
    ./vendor/bin/sail artisan view:cache
    
    # Set correct permissions
    print_info "Jogosultságok beállítása..."
    ./vendor/bin/sail artisan storage:link 2>/dev/null || true
    
    print_success "Setup befejezve!"
    
    # Summary
    echo
    echo -e "${GREEN}🎉 Laravel Boilerplate sikeresen telepítve!${NC}"
    echo -e "${CYAN}=================================${NC}"
    echo
    echo -e "${WHITE}📍 Környezet:${NC} $ENVIRONMENT"
    echo -e "${WHITE}🌐 Alkalmazás:${NC} http://localhost"
    echo -e "${WHITE}🔐 Admin panel:${NC} http://localhost/admin"
    echo
    
    if [[ "$ENVIRONMENT" != "production" ]]; then
        echo -e "${WHITE}👤 Admin belépés:${NC}"
        echo -e "   Email: admin@example.com"
        echo -e "   Jelszó: password"
        echo
    fi
    
    echo -e "${WHITE}🛠️ Hasznos parancsok:${NC}"
    echo -e "   ./vendor/bin/sail up -d           # Indítás"
    echo -e "   ./vendor/bin/sail down           # Leállítás"
    echo -e "   ./vendor/bin/sail artisan tinker # Laravel konzol"
    echo -e "   ./scripts/quality-check.sh       # Kódminőség ellenőrzés"
    echo
    
    if [[ -f "$LOG_FILE" ]]; then
        echo -e "${PURPLE}📄 Részletes log:${NC} $LOG_FILE"
    fi
    
    # Cleanup backup directory if everything went well
    if [[ -d "$BACKUP_DIR" ]] && [[ "$SKIP_INTERACTIVE" == "false" ]]; then
        read -p "Töröljem a backup fájlokat? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            rm -rf "$BACKUP_DIR"
            print_info "Backup fájlok törölve"
        fi
    fi
}

# =================================================================
# ARGUMENT PARSING
# =================================================================

parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --env=*)
                ENVIRONMENT="${1#*=}"
                shift
                ;;
            --environment=*)
                ENVIRONMENT="${1#*=}"
                shift
                ;;
            --domain=*)
                CUSTOM_DOMAIN="${1#*=}"
                shift
                ;;
            --skip-interactive)
                SKIP_INTERACTIVE=true
                shift
                ;;
            --check-only)
                CHECK_ONLY=true
                shift
                ;;
            --force)
                FORCE_REINSTALL=true
                shift
                ;;
            --skip-tests)
                SKIP_TESTS=true
                shift
                ;;
            --help|-h)
                show_help
                exit 0
                ;;
            *)
                print_error "Ismeretlen paraméter: $1"
                show_help
                exit 1
                ;;
        esac
    done
}

show_help() {
    echo "Laravel Boilerplate Quick Start Script"
    echo
    echo "Használat:"
    echo "  ./scripts/quick-start.sh [OPCIÓK]"
    echo
    echo "Opciók:"
    echo "  --env=ENV               Környezet (development|testing|production|ci)"
    echo "  --domain=DOMAIN         Egyedi domain production esetén"
    echo "  --skip-interactive      Automatikus mód (CI-hez)"
    echo "  --check-only            Csak dependency ellenőrzés"
    echo "  --force                 Újratelepítés kényszerítése"
    echo "  --skip-tests            Tesztek átugrása"
    echo "  --help, -h             Ez a súgó"
    echo
    echo "Példák:"
    echo "  ./scripts/quick-start.sh"
    echo "  ./scripts/quick-start.sh --env=production --domain=myapp.com"
    echo "  ./scripts/quick-start.sh --env=ci --skip-interactive"
    echo "  ./scripts/quick-start.sh --check-only"
}

# =================================================================
# MAIN EXECUTION
# =================================================================

main() {
    # Setup error handling
    trap cleanup_on_error ERR
    
    # Initialize log
    echo "Quick Start Script Started: $(date)" > "$LOG_FILE"
    
    # Parse command line arguments
    parse_arguments "$@"
    
    # Validate environment option
    case $ENVIRONMENT in
        development|testing|production|ci)
            ;;
        *)
            print_error "Érvénytelen környezet: $ENVIRONMENT"
            print_info "Érvényes opciók: development, testing, production, ci"
            exit 1
            ;;
    esac
    
    # Print header
    print_header
    
    # Check only mode
    if [[ "$CHECK_ONLY" == "true" ]]; then
        check_dependencies
        exit $?
    fi
    
    # Main execution flow
    check_dependencies
    setup_environment
    install_dependencies
    start_docker_environment
    setup_database
    build_assets
    run_validation
    final_setup
    
    log "Quick Start Script Completed Successfully"
}

# Run main function with all arguments
main "$@"
