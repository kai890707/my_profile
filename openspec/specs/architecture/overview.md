# System Architecture Overview

**Project**: 業務推廣系統 (Business Promotion System Backend API)
**Framework**: Laravel 11.x
**Version**: 1.2.0
**Last Updated**: 2026-01-20

## System Purpose

轉型現有個人作品集系統為業務推廣平台，提供三種角色（業務員、一般使用者、Admin）的 RESTful API 服務。

## Architecture Layers

```
┌─────────────────────────────────────┐
│     Frontend (Next.js 15)           │
│     React 19 + TypeScript           │
│     http://localhost:3001           │
└─────────────────────────────────────┘
              ↓ HTTPS + JWT
┌─────────────────────────────────────┐
│     API Gateway (routes/api.php)    │
│     - CORS Middleware               │
│     - Sanctum Auth Middleware       │
│     - Role-based Middleware         │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│     Controllers (API Layer)         │
│     - AuthController                │
│     - SalespersonController         │
│     - SearchController              │
│     - AdminController               │
│     - CompanyController             │
│     - SwaggerController             │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│     Business Logic Layer            │
│     - Eloquent Models               │
│     - Form Request Validation       │
│     - Policy Authorization          │
│     - Service Classes               │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│     Database (MySQL 8.0)            │
│     - users, salesperson_profiles   │
│     - companies, certifications     │
│     - experiences, industries       │
│     - regions, approval_logs        │
└─────────────────────────────────────┘
```

## Core Components

### 1. Authentication & Authorization

**Laravel Sanctum** (Token-based Authentication)
- Token generation (Access + Refresh)
- Token verification and validation
- Token expiry management
- API token authentication

**Middleware**
- `auth:sanctum`: Sanctum authentication
- `EnsureUserRole`: RBAC permission check
- `CheckSalespersonStatus`: Salesperson approval check

**Token Configuration**
- Access Token: 60 minutes
- Refresh Token: 7 days
- Storage: Frontend localStorage + HTTP-only cookies
- Secure transmission: HTTPS only in production

### 2. Role-Based Access Control (RBAC)

**Three Roles**:
1. **admin**: Full system access, approval rights
2. **salesperson**: Manage own profile, upload data
3. **user**: Public search only (no registration required)

**Permission Matrix**:
```
Endpoint                    | Admin | Salesperson | Public
---------------------------|-------|-------------|--------
POST /auth/register        | ✓     | ✓           | ✓
POST /auth/login           | ✓     | ✓           | -
GET  /auth/me              | ✓     | ✓           | -
GET  /search/*             | ✓     | ✓           | ✓
GET  /salesperson/*        | -     | ✓ (own)     | -
POST /salesperson/*        | -     | ✓           | -
GET  /admin/*              | ✓     | -           | -
POST /admin/*              | ✓     | -           | -
```

### 3. Approval Workflow

**Approval Status States**:
- `pending`: Waiting for admin review
- `approved`: Approved by admin
- `rejected`: Rejected with reason

**Items Requiring Approval**:
1. **User Registration**: Salesperson sign-up
2. **Company Information**: Company name, tax_id
3. **Certifications**: Upload certificates/licenses
4. **Profile Updates**: Avatar uploads

**Items NOT Requiring Approval**:
- Bio, phone, specialties (general profile info)
- Work experiences (experiences table)

**Approval Process**:
```
1. Salesperson submits → status = 'pending'
2. Admin reviews → GET /admin/pending-approvals
3. Admin approves/rejects → POST /admin/approve-* or reject-*
4. Log recorded → approval_logs table
5. Status updated → 'approved' or 'rejected'
```

### 4. File Storage Strategy

**Storage Method**: Database BLOB (MEDIUMBLOB, max 16MB)

**File Types**:
- Avatar: JPG/PNG, max 5MB
- Certificate: JPG/PNG/PDF, max 5MB

**Process**:
1. Frontend: Upload as Base64 encoded string
2. Backend: Decode and store in BLOB column
3. Retrieval: Encode back to Base64 in API response

**Columns**:
- `{file}_data`: MEDIUMBLOB
- `{file}_mime`: VARCHAR (MIME type)
- `{file}_size`: INT (bytes)

### 5. Search Functionality

**Search Criteria**:
- `keyword`: Full-text search (name, bio, company)
- `company`: Company name (LIKE match)
- `industry_id`: Industry filter
- `region_id`: Service region filter
- Combination: All filters can be combined

**Search Query**:
```sql
SELECT sp.*, c.name as company_name, i.name as industry_name
FROM salesperson_profiles sp
LEFT JOIN companies c ON c.id = sp.company_id
LEFT JOIN industries i ON i.id = c.industry_id
WHERE sp.approval_status = 'approved'
  AND c.approval_status = 'approved'
  [AND filters...]
ORDER BY sp.created_at DESC
LIMIT 20 OFFSET 0
```

**Pagination**:
- Default: 20 items per page
- Max: 50 items per page
- Response includes: total, current_page, last_page

## Technology Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Laravel | 11.x |
| Language | PHP | 8.4 |
| Database | MySQL | 8.0 |
| Authentication | Laravel Sanctum | 4.x |
| Container | Docker + Docker Compose | Latest |
| Web Server | Nginx (Production) / PHP Dev Server | Latest |
| Testing | Pest PHP | 3.x |
| Static Analysis | PHPStan | Level 9 |
| API Docs | Swagger/OpenAPI | 3.0 |

## Deployment Environment

**Docker Services**:
- `app`: PHP 8.2 + Apache + CodeIgniter 4
- `db`: MySQL 8.0
- `pma`: phpMyAdmin (optional)

**Ports**:
- API: http://localhost:8080
- Database: localhost:3306
- phpMyAdmin: http://localhost:8081

**Environment Variables** (`.env`):
```
APP_ENV=local
APP_URL=http://localhost:8080
APP_PORT=8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=my_profile_laravel
DB_USERNAME=sail
DB_PASSWORD=password

SANCTUM_STATEFUL_DOMAINS=localhost:3001
SESSION_DOMAIN=localhost
SESSION_LIFETIME=120

FRONTEND_URL=http://localhost:3001
```

## Security Measures

1. **Password Security**: bcrypt hashing (PASSWORD_BCRYPT)
2. **SQL Injection Prevention**: Query Builder (parameterized queries)
3. **XSS Prevention**: esc() function on all outputs
4. **CSRF Protection**: Disabled for API (token-based auth)
5. **CORS Configuration**: Whitelist allowed origins
6. **Rate Limiting**: (Future enhancement)
7. **HTTPS Only**: (Production requirement)

## Scalability Considerations

**Current Limitations**:
- File storage in database (max 16MB per file)
- No caching layer
- Single database instance

**Future Enhancements**:
1. Move file storage to S3/Cloud Storage
2. Implement Redis caching for frequently accessed data
3. Add database read replicas
4. Implement CDN for static assets
5. Add API rate limiting
6. Implement full-text search (Elasticsearch)

## Error Handling

**Unified Response Format**:
```json
{
  "status": "success|error",
  "message": "Human-readable message",
  "data": {...}  // Optional
}
```

**HTTP Status Codes**:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized (invalid token)
- 403: Forbidden (insufficient permissions)
- 404: Not Found
- 422: Validation Error
- 500: Internal Server Error

## Performance Benchmarks

**Target Performance**:
- Search API: < 500ms response time
- Login API: < 300ms response time
- Simple CRUD: < 200ms response time

**Database Indexes**:
- `users(email, username, role)`
- `salesperson_profiles(user_id, approval_status)`
- `companies(tax_id, industry_id, approval_status)`
- `certifications(user_id, approval_status)`

## Maintenance & Monitoring

**Health Checks**:
```bash
# API Health
curl http://localhost:8080/

# Database Connection
docker-compose exec db mysql -u root -p -e "SELECT 1"

# Logs
docker-compose logs -f app
```

**Backup Strategy**:
```bash
# Database Backup
docker-compose exec db mysqldump -u root -p my_profile > backup.sql

# Restore
docker-compose exec -T db mysql -u root -p my_profile < backup.sql
```

**Migration Management**:
```bash
# Run migrations
docker exec -it my_profile_laravel_app php artisan migrate

# Rollback
docker exec -it my_profile_laravel_app php artisan migrate:rollback

# Status
docker exec -it my_profile_laravel_app php artisan migrate:status

# Fresh with seeding
docker exec -it my_profile_laravel_app php artisan migrate:fresh --seed
```

---

## Related Documents

- [API Specifications](../api/endpoints.md) - Detailed API endpoint documentation (37 endpoints)
- [Data Models](../models/data-models.md) - Database schema and relationships
- [Business Rules](../business-rules.md) - System business rules and validation
- [Frontend Specs](../frontend/README.md) - Frontend architecture and components
- [Swagger Documentation](http://localhost:8080/api/docs) - Interactive API documentation

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-01-10 | CodeIgniter 4 → Laravel 11 migration complete |
| 1.1.0 | 2026-01-09 | Frontend SPA completed (Next.js 15) |
| 1.2.0 | 2026-01-11 | User Registration Refactor |
| 1.2.1 | 2026-01-20 | Documentation updates |
