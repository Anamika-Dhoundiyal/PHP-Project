# Quick GitHub Setup Guide

## Step 1: Create Repository on GitHub

1. Go to [github.com](https://github.com)
2. Login with your account: **Anamika-Dhoundiyal**
3. Click **New Repository**
4. Name: `GROCERY-STORE-MANAGEMENT-SYSTEM`
5. Description: `A comprehensive web-based grocery store management system built with PHP and MySQL`
6. Choose **Public** (for portfolio)
7. Initialize with:
   - ✅ Add a README (skip - we have one)
   - ❌ .gitignore (skip - we have one)
   - ✅ License (MIT is good)
8. Click **Create Repository**

## Step 2: Connect Local Repository to GitHub

Copy the repository URL from GitHub (HTTPS format):
```
https://github.com/Anamika-Dhoundiyal/GROCERY-STORE-MANAGEMENT-SYSTEM.git
```

Then run in terminal:
```bash
cd C:\xampp\htdocs\GROCERY-STORE-MANAGEMENT-SYSTEM
git remote add origin https://github.com/Anamika-Dhoundiyal/GROCERY-STORE-MANAGEMENT-SYSTEM.git
git branch -M main
git push -u origin main
```

## Step 3: Add GitHub Credentials

You'll need to authenticate. Choose one:

### Option A: Personal Access Token (Recommended)
1. Go to GitHub → Settings → Developer settings → Personal access tokens
2. Click "Generate new token"
3. Name: "Local Development"
4. Expiration: 90 days
5. Scopes: ✅ repo (full control of private repos)
6. Generate and copy the token
7. When asked for password, paste the token

### Option B: SSH Key (Advanced)
1. Generate SSH key: `ssh-keygen -t rsa -b 4096`
2. Add to GitHub: Settings → SSH and GPG keys
3. Use SSH URL instead: `git@github.com:Anamika-Dhoundiyal/GROCERY-STORE-MANAGEMENT-SYSTEM.git`

## Step 4: Verify Push

After running push command, verify on GitHub:
- Visit: https://github.com/Anamika-Dhoundiyal/GROCERY-STORE-MANAGEMENT-SYSTEM
- Check that files are there
- Verify README displays correctly

## Step 5: Add More Information (GitHub Profile)

1. Add repository topics: grocery, store, management, php, mysql
2. Add description in repo settings
3. Enable GitHub Pages if you want website (optional)
4. Add collaborators if needed

## Future Updates

To push updates:
```bash
git add .
git commit -m "[TYPE] Description of changes"
git push origin main
```

## Common Commands

```bash
# Check status
git status

# View changes
git diff

# View commit history
git log --oneline

# Undo changes
git checkout .

# Remove files from tracking
git rm --cached filename

# Tag a release
git tag -a v1.0 -m "Version 1.0 Release"
git push origin v1.0
```

---

**Next:** For deployment options, see [DEPLOYMENT.md](DEPLOYMENT.md)
