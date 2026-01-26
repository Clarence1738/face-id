# Encryption Setup Guide

This system encrypts face descriptors before storing them in the database using AES-256-CBC encryption. This protects biometric data even if the database is compromised.

## Why Encryption?

Face descriptors are sensitive biometric data. Encrypting them ensures:
- Database breaches don't expose raw face data
- Compliance with privacy regulations (GDPR, CCPA)
- Added security layer beyond database access controls

## Quick Setup (New Installation)

### Step 1: Generate Encryption Key

```bash
php backend/scripts/generate_key.php
```

This outputs a secure 32-byte key. Copy it - you'll need it in the next step.

### Step 2: Configure the Key

**Windows (PowerShell):**
```powershell
# Temporary (current session)
$env:FACE_ENCRYPTION_KEY = "paste-your-key-here"

# Permanent (recommended)
[System.Environment]::SetEnvironmentVariable('FACE_ENCRYPTION_KEY', 'paste-your-key-here', 'User')
```

**Linux/Mac:**
```bash
# Temporary
export FACE_ENCRYPTION_KEY="paste-your-key-here"

# Permanent (add to ~/.bashrc or ~/.zshrc)
echo 'export FACE_ENCRYPTION_KEY="paste-your-key-here"' >> ~/.bashrc
source ~/.bashrc
```

**Apache Server:**

Add to `.htaccess` or `httpd.conf`:
```apache
SetEnv FACE_ENCRYPTION_KEY "paste-your-key-here"
```

### Step 3: Update Database

Run this SQL to allow larger encrypted data:

```sql
ALTER TABLE users MODIFY COLUMN descriptor TEXT;
```

### Step 4: Test It

```bash
php backend/scripts/test_encryption.php
```

You should see all tests pass. If not, check that the environment variable is set correctly.

## Upgrading Existing Installation

If you already have users registered with unencrypted data:

```bash
php backend/scripts/migrate_encryption.php
```

This script:
- Checks each user record
- Encrypts unencrypted descriptors
- Skips already-encrypted records
- Reports progress and any errors

## How It Works

### During Registration
1. User's face is captured by webcam
2. face-api.js extracts a 128-number "face descriptor"
3. Backend receives descriptor as JSON array
4. Encryption.php encrypts the array using AES-256-CBC
5. Encrypted string (looks like random text) is stored in database

### During Recognition
1. User's face is captured
2. Descriptor extracted and sent to backend
3. Backend fetches all encrypted descriptors from database
4. Each descriptor is decrypted in memory
5. Decrypted descriptor compared with captured one
6. Match found if distance below threshold

### Technical Details

**Algorithm:** AES-256-CBC  
**Key Size:** 256 bits (32 bytes)  
**IV Size:** 128 bits (16 bytes, randomly generated)  
**Format:** Base64(IV + EncryptedData)

Each encryption uses a fresh random IV, so encrypting the same data twice produces different outputs. This is a security feature.

## Configuration Options

### Option 1: Environment Variable (Recommended)

Best for production. Key stays out of codebase.

```bash
# Set via system/user environment
```

### Option 2: Apache Configuration

Good for shared hosting where you control Apache config.

```apache
SetEnv FACE_ENCRYPTION_KEY "key"
```

### Option 3: Direct Code Edit (Development Only)

Edit `backend/utils/Encryption.php`:

```php
private static $encryption_key = 'your-key-here';
```

**WARNING:** Never commit this to Git! Only use for local development.

## Verification

Check if encryption is working:

```sql
SELECT id, name, LEFT(descriptor, 50) FROM users LIMIT 1;
```

**Encrypted (correct):** `Y2x3bkZHNVh6UjVMM3J5RmxQVGVvUT09...`  
**Not encrypted (problem):** `[0.123, 0.456, 0.789, ...]`

## Troubleshooting

**"Encryption failed" error:**
- Check if OpenSSL extension is enabled: `php -m | grep openssl`
- Install OpenSSL if missing

**"Decryption failed" error:**
- Verify the encryption key is set correctly
- Check if key matches the one used for encryption
- Ensure data wasn't corrupted

**Recognition not working after enabling encryption:**
- Run `test_encryption.php` to verify encryption works
- Check database column is TEXT type, not VARCHAR
- Verify all descriptors were migrated properly

**Environment variable not recognized:**
- Restart terminal/server after setting
- Check variable name spelling (case-sensitive on Linux)
- For Apache, verify httpd.conf or .htaccess syntax

## Security Best Practices

**DO:**
- Generate a strong random key using the provided script
- Store key in environment variables
- Back up the key in a secure location (separate from database backups)
- Use HTTPS in production
- Restrict database access
- Document key storage location for your team

**DON'T:**
- Commit encryption keys to Git
- Share keys via email or chat
- Use weak or guessable keys
- Store keys in the same place as database backups
- Leave keys in code comments

## Key Rotation

If you need to change the encryption key:

1. Generate new key: `php backend/scripts/generate_key.php`
2. Keep old key accessible temporarily
3. Decrypt all records with old key
4. Set new key in environment
5. Re-encrypt all records with new key
6. Verify all records work
7. Securely delete old key

There's no automated script for this yet - it's an advanced operation.

## Backup Strategy

**Critical:** If you lose the encryption key, you lose all face data permanently. No recovery possible.

**Backup the key:**
- Store in password manager (1Password, LastPass, etc.)
- Keep encrypted copy on separate storage
- Document in team runbook
- Include in disaster recovery plan

**DO NOT backup key with:**
- Database dumps
- Application code repository
- Server filesystem backups (unless encrypted separately)

## Production Checklist

Before going live:

- [ ] Encryption key generated
- [ ] Key set in production environment
- [ ] Database schema updated
- [ ] Encryption tested successfully
- [ ] Key backed up securely
- [ ] Key storage documented
- [ ] HTTPS enabled
- [ ] `.env` files in `.gitignore`
- [ ] Team knows where key is stored
- [ ] Disaster recovery plan includes key recovery

## Getting Help

If you encounter issues:

1. Run `php backend/scripts/test_encryption.php` - this tests encryption independently
2. Check PHP error logs for specific errors
3. Verify OpenSSL is installed and working
4. Ensure database column type is TEXT
5. Confirm environment variable is actually set
