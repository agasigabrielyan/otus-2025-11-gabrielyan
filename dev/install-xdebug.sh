#!/bin/bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
INI_SRC="${ROOT}/dev/xdebug.ini"

echo "Installing php8.4-xdebug..."
sudo apt-get update
sudo apt-get install -y php8.4-xdebug

echo "Applying config for Apache + CLI..."
sudo cp "${INI_SRC}" /etc/php/8.4/apache2/conf.d/20-xdebug.ini
sudo cp "${INI_SRC}" /etc/php/8.4/cli/conf.d/20-xdebug.ini

echo "Restarting Apache..."
sudo systemctl restart apache2

echo ""
echo "CLI:"
php -v | grep -i xdebug || true
echo ""
echo "Apache module (via php8.4 -m is CLI only; open phpinfo in browser to verify web SAPI)"
echo "Done. In VS Code: install extension 'PHP Debug', run 'Listen for Xdebug', trigger with ?XDEBUG_TRIGGER=1"
