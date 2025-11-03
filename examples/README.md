# Examples Index

Central index of runnable example scripts demonstrating Mailtrap PHP SDK features. Each script is intentionally minimal; open the file to inspect parameters. Run examples from project root (adjust env vars first).

## Prerequisites
* Export or place inside the file your ENV variables (MAILTRAP_API_KEY, MAILTRAP_INBOX_ID, etc)
* Some examples require a verified sending domain (production / bulk / templates).

## Contents

### 1. Sending (Transactional / Bulk Streams)
| Purpose | File |
|---------|------|
| Minimal transactional send | [`sending/minimal.php`](sending/minimal.php) |
| Full featured send (headers, vars, attachments) | [`sending/all.php`](sending/all.php) |
| Send using a template (transactional stream) | [`sending/template.php`](sending/template.php) |
| Suppressions API usage | [`sending/suppressions.php`](sending/suppressions.php) |
| Bulk API single send (stream selection) | [`bulk/bulk.php`](bulk/bulk.php) |
| Bulk API template send | [`bulk/bulk_template.php`](bulk/bulk_template.php) |

### 2. Batch Sending (multiple messages in one call)
| Purpose | File |
|---------|------|
| Transactional batch send | [`batch/transactional.php`](batch/transactional.php) |
| Transactional batch send (template) | [`batch/transactional_template.php`](batch/transactional_template.php) |
| Bulk batch send | [`batch/bulk.php`](batch/bulk.php) |
| Bulk batch send (template) | [`batch/bulk_template.php`](batch/bulk_template.php) |
| Sandbox batch send | [`batch/sandbox.php`](batch/sandbox.php) |
| Sandbox batch send (template) | [`batch/sandbox_template.php`](batch/sandbox_template.php) |

### 3. Sandbox (Email Testing)
| Purpose | File |
|---------|------|
| Sandbox transactional send | [`testing/send-mail.php`](testing/send-mail.php) |
| Sandbox send with template | [`testing/template.php`](testing/template.php) |
| Manage attachments of a message | [`testing/attachments.php`](testing/attachments.php) |
| Inbox CRUD / listing | [`testing/inboxes.php`](testing/inboxes.php) |
| Message CRUD / listing | [`testing/messages.php`](testing/messages.php) |
| Project CRUD / listing | [`testing/projects.php`](testing/projects.php) |

### 4. Contact Management
| Purpose | File |
|---------|------|
| Contacts CRUD + list | [`contacts/all.php`](contacts/all.php) |
| Contact lists CRUD | [`contact-lists/all.php`](contact-lists/all.php) |
| Custom fields CRUD | [`contact-fields/all.php`](contact-fields/all.php) |

### 5. Templates & Domains
| Purpose | File |
|---------|------|
| Templates CRUD | [`templates/all.php`](templates/all.php) |
| Sending domains CRUD | [`sending-domains/all.php`](sending-domains/all.php) |

### 6. General API
| Purpose | File |
|---------|------|
| Accounts info | [`general/accounts.php`](general/accounts.php) |
| Billing info | [`general/billing.php`](general/billing.php) |
| Permissions listing | [`general/permissions.php`](general/permissions.php) |
| Users listing | [`general/users.php`](general/users.php) |

### 7. Framework Bridges
| Framework | Files |
|-----------|-------|
| Laravel (transactional, sandbox, template, bulk) | [`laravel/transactional.php`](laravel/transactional.php), [`laravel/sandbox.php`](laravel/sandbox.php), [`laravel/template.php`](laravel/template.php), [`laravel/bulk.php`](laravel/bulk.php) |
| Symfony (transactional, sandbox, template, bulk) | [`symfony/transactional.php`](symfony/transactional.php), [`symfony/sandbox.php`](symfony/sandbox.php), [`symfony/template.php`](symfony/template.php), [`symfony/bulk.php`](symfony/bulk.php) |

### 8. Configuration Utilities
| Purpose | File |
|---------|------|
| Showcase of combined config usage / initialization patterns | [`config/all.php`](config/all.php) |

## Running an Example
From project root:
```
php examples/sending/minimal.php
```
For batch examples, ensure the stream (bulk vs transactional vs sandbox) matches your API token and domain verification status.

## Tips
- Keep secrets out of committed files; rely on environment variables.
- Template examples require existing template UUIDs in your Mailtrap account.
- Sandbox examples need `MAILTRAP_INBOX_ID`.

---
