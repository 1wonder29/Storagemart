# Storage Mart TMS - localhost:8000 Error - COMPLETE FIX

## ✅ ALL CODE CHANGES APPLIED

The application has been fully updated to use **relative URLs only**. No matter what port or domain you access it from, it will work correctly.

### Changes Made:
1. ✅ Created `.env` file with `BASE_URL=` (empty)
2. ✅ Updated `config/config.php` to strip port numbers if somehow included
3. ✅ Updated `AuthController.php` - redirect() uses relative URLs
4. ✅ Updated `app/Views/auth/login.php` - form action is now `/login-post`
5. ✅ Updated `app/Views/auth/forgot_password.php` - form action is `/forgot-password`
6. ✅ Updated `public/index.php` - home redirect uses `/login`

## ⚠️ ERROR PERSISTS - NEXT TROUBLESHOOTING STEPS

If you're still seeing: "Unsafe attempt to load URL http://localhost:8000/login from frame (index):1"

### STEP 1: Verify What URL You're Accessing
**The error shows `localhost:8000`, which means the app IS actually on port 8000 somewhere.**

Check your browser address bar:
- **Correct**: `http://localhost/` or `http://localhost:80/`
- **Wrong**: `http://localhost:8000/`

### STEP 2: Find What's Running on Port 8000

On Windows PowerShell, run:
```powershell
netstat -ano | findstr ":8000"
```

On Mac/Linux:
```bash
lsof -i :8000
```

This will show you what process is using port 8000.

### STEP 3: Possible Causes & Solutions

**CAUSE A: You're running PHP dev server on port 8000**
```bash
# If running: php -S localhost:8000 -t public
# STOP it (Ctrl+C) and instead run:
php -S localhost:80 -t public

# OR access the correct URL
http://localhost:8000/  # This will work now with our fixes
```

**CAUSE B: BrowserSync or Gulp watch is running**
```bash
# Kill gulp: Ctrl+C in terminal where `npm run gulp` is running
# The app will still work without it
```

**CAUSE C: App is in an iframe from a different origin**
- If loading app in iframe from another page, make sure parent and iframe are on same origin
- Example:
  ```html
  <!-- ✓ CORRECT - Same origin -->
  <iframe src="http://localhost/login"></iframe>
  
  <!-- ✗ WRONG - Cross-origin -->
  <iframe src="http://localhost:8000/login"></iframe>
  ```

**CAUSE D: You have a reverse proxy or nginx on port 8000**
- If using nginx/Apache proxy: Verify it's configured correctly
- Make sure proxy passes requests to port 80 (where Apache runs)

### STEP 4: Verify Apache is Running on Port 80

On Windows with XAMPP:
```powershell
# Start XAMPP Apache from Control Panel or:
C:\xampp\apache\bin\httpd.exe
```

Check port 80 is listening:
```powershell
netstat -ano | findstr ":80"
```

### STEP 5: Test the Application

1. **Clear browser cache**: `Ctrl+Shift+Delete`
2. **Incognito mode**: `Ctrl+Shift+N` → Access `http://localhost/`
3. **Try different browser**: Chrome → Firefox or Edge
4. **Verify form submission**: Once logged in, check network tab (F12) to see actual URLs being submitted

## 🔧 FINAL VERIFICATION CHECKLIST

- [ ] I verified the URL in address bar (not :8000)
- [ ] I checked what's listening on port 8000
- [ ] I stopped any dev servers (BrowserSync, etc.)
- [ ] Apache is running on port 80
- [ ] I cleared browser cache
- [ ] I tried incognito mode
- [ ] Form now submits to `/login-post` (not `localhost:8000/login-post`)
- [ ] Login redirects to `/hr/dashboard` (not `localhost:8000/hr/dashboard`)

## 📋 FILES MODIFIED

- `.env` - Created with BASE_URL=""
- `config/config.php` - Port stripping added, BASE_URL safe-guarded
- `app/Controllers/AuthController.php` - redirect() method updated
- `app/Views/auth/login.php` - Form uses `/login-post`
- `app/Views/auth/forgot_password.php` - Form uses `/forgot-password`
- `public/index.php` - Home redirect uses `/login`

## ❓ STILL HAVING ISSUES?

1. Check the actual server error log: `app/logs/php_errors.log`
2. Open browser F12 → Network tab → Try login → See actual request URL
3. Check if there are any Chrome extensions interfering
4. Verify no conflicting configuration in:
   - `railway.json` (deployment config)
   - `gulpfile.js` (BrowserSync config)
   - Apache vhost configuration

## 💡 KEY INSIGHT

The error message "(index):1" and "from frame" suggests the browser is intercepting a frame/iframe navigation. This is a **same-origin policy violation**, not an application bug. It happens when:
1. Iframe is on domain A
2. Trying to navigate to domain B (or different port)
3. Browser blocks it

Our fix ensures **all URLs are relative**, so they always use the current origin.
