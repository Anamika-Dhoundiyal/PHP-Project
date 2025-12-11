# Deployment Guide

## 🚀 Deployment Options

### Option 1: Local XAMPP/WAMP Development

See the main [README.md](README.md) for local setup instructions.

### Option 2: Cloud Hosting (Recommended for Production)

#### A. Using Heroku with PHP Buildpack
1. Install Heroku CLI
2. Create Procfile:
   ```
   web: vendor/bin/heroku-php-apache2 Grocery/
   ```
3. Deploy:
   ```bash
   heroku login
   heroku create your-app-name
   git push heroku main
   ```

#### B. Using shared hosting (GoDaddy, Bluehost, etc.)
1. FTP to your hosting account
2. Upload all files to public_html or www folder
3. Create database via hosting control panel
4. Update `Grocery/db_connection.php` with hosting credentials
5. Import `grocery_store_schema.sql` via phpMyAdmin

#### C. Using AWS/DigitalOcean/Linode
1. Set up Ubuntu/CentOS server with Apache, PHP, MySQL
2. Clone repository to `/var/www/html`
3. Update file permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/GROCERY-STORE-MANAGEMENT-SYSTEM
   sudo chmod -R 755 /var/www/html/GROCERY-STORE-MANAGEMENT-SYSTEM
   ```
4. Configure Apache virtual host
5. Set up SSL certificate (Let's Encrypt)
6. Configure database

### Option 3: Docker (Advanced)

Create `Dockerfile`:
```dockerfile
FROM php:7.4-apache

# Install MySQLi
RUN docker-php-ext-install mysqli

# Copy project
COPY Grocery/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Enable mod_rewrite
RUN a2enmod rewrite

EXPOSE 80
```

Build and run:
```bash
docker build -t grocery-store .
docker run -p 80:80 -e DB_HOST=mysql_host grocery-store
```

## 🔐 Security Checklist

- [ ] Change default admin password
- [ ] Remove sample customer accounts
- [ ] Enable HTTPS/SSL certificate
- [ ] Configure firewall rules
- [ ] Set up database backups
- [ ] Enable error logging instead of displaying errors
- [ ] Update PHP to latest version
- [ ] Keep MySQL/Apache updated
- [ ] Use environment variables for database credentials
- [ ] Implement rate limiting on login
- [ ] Enable prepared statements (already implemented)
- [ ] Regular security audits

## 📊 Performance Optimization

1. **Database**:
   - Add indexes to frequently searched columns
   - Regular OPTIMIZE TABLE maintenance
   - Enable query caching if available

2. **PHP**:
   - Enable opcache
   - Use persistent connections when safe
   - Implement caching layer (Redis/Memcached)

3. **Web Server**:
   - Enable GZIP compression
   - Configure browser caching headers
   - Use CDN for static assets

4. **Application**:
   - Optimize images before uploading
   - Minify CSS and JavaScript
   - Implement pagination for large datasets

## 📈 Monitoring & Maintenance

### Regular Tasks
- Daily: Check error logs
- Weekly: Verify backups, monitor database size
- Monthly: Review user activity, test disaster recovery
- Quarterly: Security audit, dependency updates

### Logging Setup
Edit `Grocery/db_connection.php`:
```php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php-errors.log');
```

## 🔄 Backup Strategy

### Database Backup
```bash
# Daily automated backup
mysqldump -u root -p grocery_store > backup_$(date +%Y%m%d).sql
```

### File Backup
```bash
# Weekly file backup
tar -czf backup_files_$(date +%Y%m%d).tar.gz Grocery/
```

### Cloud Backup
- Use AWS S3, Google Cloud Storage, or Azure Blob
- Set retention policies
- Test restore procedures

## 📝 Environment Configuration

Create `.env` file in project root (add to .gitignore):
```
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=grocery_store
ADMIN_EMAIL=admin@example.com
APP_URL=https://yourdomain.com
APP_ENV=production
```

Then update `Grocery/db_connection.php`:
```php
require_once __DIR__ . '/.env';
$server = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$database = $_ENV['DB_NAME'];
```

## 🐛 Troubleshooting Deployment

### Database Connection Error
```
Error: Connection refused
```
- Verify MySQL is running
- Check credentials match hosting environment
- Ensure database exists

### 404 Errors
- Verify files uploaded completely
- Check file permissions (755 for directories, 644 for files)
- Verify URL paths in code match file structure

### White Screen of Death
- Check error log: `tail -f /var/log/apache2/error.log`
- Enable error display temporarily for debugging
- Verify PHP version compatibility

### Slow Performance
- Check database indexes
- Review slow query log
- Optimize images
- Enable caching

## 📱 Mobile Optimization

The application uses Bootstrap for responsive design and should work on:
- Desktop (1920px+)
- Tablet (768px - 1024px)
- Mobile (320px - 767px)

Test on actual devices or use browser DevTools.

## 🔄 Continuous Integration/Deployment

### GitHub Actions Example
Create `.github/workflows/deploy.yml`:
```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Deploy via SSH
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /var/www/html/GROCERY-STORE
            git pull origin main
            composer install
```

## 📞 Support & Issues

For deployment issues:
1. Check error logs
2. Review this guide
3. Create GitHub issue with:
   - Hosting platform
   - PHP/MySQL versions
   - Error messages
   - Steps to reproduce

---

For more details, see [README.md](README.md)
