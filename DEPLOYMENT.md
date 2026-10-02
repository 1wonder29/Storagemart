# Deployment Guide - Storage Mart TMS

## Pre-Deployment Checklist

### 1. Environment Configuration
- [ ] Copy `.env.example` to `.env` on the deployment server
- [ ] Update `.env` with production database credentials
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Ensure `.env` is **NOT** committed to git (already in `.gitignore`)

```bash
cp .env.example .env
# Edit .env with production values
```

### 2. Production Environment Variables
Create `.env` with these values:

```env
APP_ENV=production
BASE_URL=https://your-domain.com

# Database (use strong credentials)
DB_HOST=your_db_host
DB_PORT=3306
DB_NAME=howard_tms
DB_USER=storagemart_prod_user
DB_PASS=your_very_strong_password_here
```

### 3. Directory Permissions

Set proper file permissions on the server:
```bash
# Ensure logs directory is writable
chmod 755 app/logs
chmod 644 app/logs/*

# Restrict sensitive files
chmod 644 config/config.php
chmod 644 .env
chmod 600 .env  # If server supports more restrictive permissions
```

### 4. Error Logging

- Error logs are automatically written to `app/logs/php_errors.log`
- **Never commit debug logs** - they're in `.gitignore` now
- Monitor logs regularly in production:
  ```bash
  tail -f app/logs/php_errors.log
  ```

### 5. Database Setup

1. Create a new database on production server:
   ```sql
   CREATE DATABASE howard_tms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Create a dedicated database user:
   ```sql
   CREATE USER 'storagemart_prod_user'@'localhost' IDENTIFIED BY 'your_strong_password';
   GRANT ALL PRIVILEGES ON howard_tms.* TO 'storagemart_prod_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Import the database schema:
   ```bash
   mysql -u storagemart_prod_user -p howard_tms < howard_tms.sql
   ```

### 6. Deployment Steps

1. **Clone the repository:**
   ```bash
   git clone https://github.com/1wonder29/Storagemart.git
   cd Storagemart
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env with production credentials
   nano .env
   ```

3. **Set permissions:**
   ```bash
   chmod -R 755 app/logs
   chmod 644 app/logs/*
   chmod 600 .env
   ```

4. **Verify deployment (using Railway or similar):**
   - Check that `APP_ENV=production` is set
   - Verify `.env` is NOT accessible via web browser
   - Test database connection
   - Test a login to verify session handling

### 7. Security Hardening

- [ ] Enable HTTPS/SSL on production domain
- [ ] Update PHP to latest version (8.1+)
- [ ] Disable directory listing in Apache
- [ ] Set secure session cookies (check Session.php)
- [ ] Implement rate limiting on login attempts
- [ ] Set CORS headers if needed
- [ ] Regular security updates

### 8. Monitoring & Maintenance

- [ ] Set up log rotation for `app/logs/php_errors.log`
- [ ] Monitor database performance
- [ ] Regular database backups (daily)
- [ ] Check error logs weekly
- [ ] Test disaster recovery procedures

### 9. Production URLs to Block

Ensure these paths are not publicly accessible:
- `/config/`
- `/app/`
- `/scripts/`
- `/.env`
- `/.git`

Update `.htaccess` if needed:
```apache
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "^\.git">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 10. After Deployment

1. Test all user roles (Admin, IT, Employee, Head)
2. Create a test ticket and verify workflow
3. Test PDF generation
4. Verify notifications are working
5. Check database backups are running
6. Review error logs in first 24 hours

## Environment Variables Reference

| Variable | Default | Production Value | Purpose |
|----------|---------|------------------|---------|
| APP_ENV | development | production | Controls error display behavior |
| BASE_URL | (empty) | https://domain.com | Application base URL |
| DB_HOST | localhost | prod-db-host | Database host |
| DB_PORT | 3306 | 3306 | Database port |
| DB_NAME | howard_tms | howard_tms | Database name |
| DB_USER | root | storagemart_prod_user | Database user |
| DB_PASS | (empty) | strong_password | Database password |

## Troubleshooting

**Database Connection Error:**
- Verify .env credentials match actual database
- Check DB_HOST is accessible from server
- Ensure database user has proper permissions

**Permission Denied Writing Logs:**
- Check `app/logs/` directory exists and is writable
- Run: `chmod 755 app/logs`

**Blank Page on Production:**
- Check `app/logs/php_errors.log` for errors
- Verify `.env` file exists and is readable
- Ensure `APP_ENV=production` is set

## Support

For issues or questions:
1. Check `app/logs/php_errors.log`
2. Review this deployment guide
3. Check GitHub issues: https://github.com/1wonder29/Storagemart/issues
