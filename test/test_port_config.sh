#!/bin/bash
# Comprehensive localhost:8000 Port Configuration Fixer
# This script tests if the application is correctly configured

echo "=== STORAGE MART PORT CONFIGURATION TEST ==="
echo ""
echo "Testing what URL the application is actually running on..."
echo ""

# Check if Apache/PHP is running
if netstat -tuln 2>/dev/null | grep -q ":80 "; then
    echo "✓ Port 80 is listening (Apache/Web Server)"
else
    echo "✗ Port 80 is NOT listening"
fi

if netstat -tuln 2>/dev/null | grep -q ":8000 "; then
    echo "⚠️  WARNING: Port 8000 is listening!"
    echo "    This might be causing the localhost:8000 error."
    echo "    Run: lsof -i :8000 (or Get-Process -Name * | Select Port on Windows)"
else
    echo "✓ Port 8000 is NOT listening"
fi

# Check if MySQL is running
if netstat -tuln 2>/dev/null | grep -q ":3306 "; then
    echo "✓ Port 3306 is listening (MySQL)"
else
    echo "✗ Port 3306 is NOT listening (MySQL not running?)"
fi

echo ""
echo "=== CONFIGURATION FILES ==="
echo ""

# Check .env file
if [ -f "/c/xampp/htdocs/be/Storagemart/.env" ]; then
    echo "✓ .env file exists"
    BASE_URL=$(grep "^BASE_URL=" /c/xampp/htdocs/be/Storagemart/.env | cut -d'=' -f2)
    echo "  BASE_URL value: '$BASE_URL' (empty is correct)"
else
    echo "✗ .env file NOT found"
fi

echo ""
echo "=== NEXT STEPS ==="
echo ""
echo "1. Open browser and access: http://localhost/"
echo "   (NOT http://localhost:8000)"
echo ""
echo "2. If you still see localhost:8000 error, check:"
echo "   - What URL is displayed in browser address bar?"
echo "   - Is the app in an iframe? (Embedded in another page?)"
echo "   - Are you using a dev tool like BrowserSync? (Kill it)"
echo ""
echo "3. Clear browser cache: Ctrl+Shift+Delete"
echo ""
echo "4. Try incognito mode: Ctrl+Shift+N"
echo ""
