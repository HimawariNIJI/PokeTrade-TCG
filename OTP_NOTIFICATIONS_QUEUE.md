# OTP Email Notifications with Queue System

## Overview

OTP emails (for email verification and password reset) are now sent asynchronously using Laravel's notification system with database queue, ensuring form submissions are instant without waiting for email delivery.

## Architecture

OTP notifications follow the same pattern as other project notifications (auction, order, comment):
- `OtpEmailVerificationNotification` - Sent during registration and when resending verification OTP
- `OtpPasswordResetNotification` - Sent when user requests password reset

Both implement `ShouldQueue` interface for background processing.

## Flow

```
User Action (Register/Request OTP)
    ↓
OTP Token created in database
    ↓
Notification queued to database (jobs table)
    ↓
Form response sent to user (instant - no waiting)
    ↓
Queue worker processes job
    ↓
Email sent via configured mailer
```

## Setup Instructions

### Prerequisites
- Laravel migrations already include `jobs` and `failed_jobs` tables
- Queue driver configured to 'database' (see `config/queue.php`)

### Development Setup

1. **Start the queue worker in a separate terminal:**
   ```bash
   php artisan queue:work
   ```

   This will process jobs immediately as they're queued, perfect for testing.

2. **Test the flow:**
   - Register a new account or request password reset
   - Form submission should be instant (no email lag)
   - Check your email - OTP should arrive within seconds (processed by worker)

3. **Monitor queue in another terminal:**
   ```bash
   # Watch pending jobs
   watch -n 1 "php artisan queue:monitor"
   
   # View failed jobs
   php artisan queue:failed
   ```

### Production Setup

#### Option 1: Supervisor (Recommended)

Create `/etc/supervisor/conf.d/poketrade-worker.conf`:
```ini
[program:poketrade-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/PokeTrade-TCG/artisan queue:work --sleep=3 --tries=3 --timeout=60
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/PokeTrade-TCG/storage/logs/worker.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start poketrade-worker:*
```

#### Option 2: Systemd Service

Create `/etc/systemd/system/poketrade-queue.service`:
```ini
[Unit]
Description=PokeTrade Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/PokeTrade-TCG
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --timeout=60
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl daemon-reload
sudo systemctl enable poketrade-queue
sudo systemctl start poketrade-queue
```

#### Option 3: Manual Background Process
```bash
nohup php artisan queue:work --daemon > storage/logs/worker.log 2>&1 &
```

## Queue Management

### View Queue Status
```bash
# Pending jobs
SELECT COUNT(*) FROM jobs WHERE reserved = 0;

# In progress jobs
SELECT COUNT(*) FROM jobs WHERE reserved = 1;

# Failed jobs
php artisan queue:failed
```

### Retry Failed Jobs
```bash
# Retry all failed jobs
php artisan queue:retry

# Retry specific job ID
php artisan queue:retry 1

# Clear all failed jobs
php artisan queue:flush
```

### Clear Queue
```bash
# Delete all pending jobs
php artisan queue:flush

# Delete failed jobs
php artisan queue:failed-forget
```

## How OTP Notifications Work

### Email Verification (Registration)
```
RegisteredUserController::store()
  → OtpToken created
  → $user->notify(new OtpEmailVerificationNotification($otp, $expiresIn))
  → Notification queued
```

### Email Verification (Resend)
```
OtpEmailVerificationController::resendOtp()
  → OtpToken created
  → $user->notify(new OtpEmailVerificationNotification($otp, $expiresIn))
  → Notification queued
```

### Password Reset
```
OtpPasswordResetController::sendOtp()
  → OtpToken created
  → User found and notified
  → $user->notify(new OtpPasswordResetNotification($otp, $expiresIn))
  → Notification queued
```

## Integration with Deployment

Add to your `deploy.sh` script:
```bash
#!/bin/bash

# ... existing deployment steps ...

# Restart queue worker
if command -v supervisorctl &> /dev/null; then
    supervisorctl restart poketrade-worker:* 2>/dev/null || true
fi

if systemctl is-active --quiet poketrade-queue; then
    systemctl restart poketrade-queue
fi
```

## Performance Improvements

| Metric | Before | After |
|--------|--------|-------|
| Registration form submission | 5-10s | Instant |
| Password reset request | 5-10s | Instant |
| Verification resend | 5-10s | Instant |
| Email delivery | Immediate | 1-3s (background) |

## Troubleshooting

### Jobs Not Processing
1. Ensure worker is running: `pgrep -f "queue:work"`
2. Check logs: `tail -f storage/logs/laravel.log`
3. Verify database: `SELECT * FROM jobs;`
4. Restart worker: `php artisan queue:work`

### Jobs Keep Failing
```bash
# Check failed job details
php artisan queue:failed

# View full error in database
SELECT * FROM failed_jobs WHERE id = 1;

# Check if mailer is configured correctly
php artisan tinker
> config('mail.default')
> config('mail.mailers.smtp')
```

### Worker Process Keeps Dying
```bash
# Check process logs
tail -f /var/log/supervisor/poketrade-worker.log

# Verify permissions
ps aux | grep queue:work

# Increase timeout if too aggressive
# (update queue:work --timeout=60 in supervisor/systemd config)
```

## Testing

### Manual Test
```php
// In Laravel Tinker
php artisan tinker

// Create OTP token
$otpToken = OtpToken::create([
    'email' => 'test@example.com',
    'otp' => '123456',
    'expires_at' => now()->addMinutes(5),
    'attempts' => 0,
    'verified' => false,
    'type' => 'email_verification',
]);

// Find user and send notification
$user = User::where('email', 'test@example.com')->first();
$user->notify(new OtpEmailVerificationNotification('123456', 5));

// In another terminal, run worker
// php artisan queue:work

// Check if job was processed
SELECT * FROM jobs;
SELECT * FROM failed_jobs;
```

## Related Files

- `app/Notifications/OtpEmailVerificationNotification.php` - Email verification notification
- `app/Notifications/OtpPasswordResetNotification.php` - Password reset notification
- `resources/views/emails/otp-verification.blade.php` - Email template
- `config/queue.php` - Queue configuration
- `config/mail.php` - Mail configuration

## Laravel Docs References

- [Queues Documentation](https://laravel.com/docs/queues)
- [Notifications Documentation](https://laravel.com/docs/notifications)
- [Mail Configuration](https://laravel.com/docs/mail)
