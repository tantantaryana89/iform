# Deployment Status

## Current Mode

- Deployment mode: XAMPP/MAMP (active)
- Containerized mode: removed from active project workflow

## Ready

- Yii2 application code
- Migration scripts
- RBAC initialization commands
- API endpoints for form/checksheet

## Next Operational Checks

1. Verify Apache virtual host points to `web/`.
2. Verify database connection in `config/db.php`.
3. Run `php yii migrate`.
4. Run `php yii rbac/init`.
5. Assign admin role and validate login/API.
