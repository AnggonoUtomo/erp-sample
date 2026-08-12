# API Specification

## Overview

API specification untuk ERP system dengan arsitektur DDD-Lite Modular Monolith. API digunakan untuk komunikasi internal antar modul dan eksternal untuk frontend.

## Base URL

```
/api/v1
```

## Authentication

Semua endpoint memerlukan authentication via Laravel Sanctum bearer token.

```
Authorization: Bearer {token}
```

## Response Format

### Success Response
```json
{
  "success": true,
  "data": {},
  "meta": {
    "request_id": "uuid",
    "timestamp": "ISO-8601"
  }
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable message",
    "details": []
  },
  "meta": {
    "request_id": "uuid",
    "timestamp": "ISO-8601"
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5,
    "has_next": true,
    "has_prev": true
  },
  "meta": {
    "request_id": "uuid",
    "timestamp": "ISO-8601"
  }
}
```

## Endpoints

### HR/Employees

#### GET /api/v1/employees
List all employees with filtering and pagination.

**Query Parameters:**
- `search` (string): Search by name or employee number
- `status` (string): Filter by employment status
- `location` (integer): Filter by work location ID
- `position` (integer): Filter by position ID
- `department` (integer): Filter by department ID
- `page` (integer): Page number
- `per_page` (integer): Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_number": "EMP-001",
      "full_name": "John Doe",
      "email": "john@example.com",
      "position": { "id": 1, "title": "Software Engineer" },
      "work_location": { "id": 1, "name": "Jakarta Office" },
      "employment_status": { "id": 1, "name": "Active" },
      "hire_date": "2024-01-15",
      "is_active": true
    }
  ],
  "pagination": { ... }
}
```

#### GET /api/v1/employees/{id}
Get employee detail.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "employee_number": "EMP-001",
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+628123456789",
    "birth_date": "1990-01-01",
    "gender": "male",
    "position": { "id": 1, "title": "Software Engineer" },
    "work_location": { "id": 1, "name": "Jakarta Office" },
    "employment_type": { "id": 1, "name": "Permanent" },
    "employment_status": { "id": 1, "name": "Active" },
    "hire_date": "2024-01-15",
    "contracts": [...],
    "documents": [...]
  }
}
```

#### POST /api/v1/employees
Create new employee.

**Request Body:**
```json
{
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+628123456789",
  "birth_date": "1990-01-01",
  "gender": "male",
  "work_location_id": 1,
  "position_id": 1,
  "employment_type_id": 1,
  "hire_date": "2024-01-15"
}
```

#### PUT /api/v1/employees/{id}
Update employee.

#### DELETE /api/v1/employees/{id}
Archive employee (soft delete).

### HR/Onboardings

#### GET /api/v1/onboardings
List onboardings.

#### GET /api/v1/onboardings/{id}
Get onboarding detail.

#### POST /api/v1/onboardings
Create onboarding (auto-created when employee created).

#### POST /api/v1/onboardings/{id}/complete
Complete onboarding.

#### GET /api/v1/onboardings/{id}/tasks
List onboarding tasks.

#### POST /api/v1/onboardings/{id}/tasks/{taskId}/complete
Complete specific task.

### HR/Offboardings

#### GET /api/v1/offboardings
List offboardings.

#### POST /api/v1/offboardings
Create offboarding.

**Request Body:**
```json
{
  "employee_id": 1,
  "reason": "Resignation",
  "resignation_type": "voluntary",
  "requested_date": "2024-06-01",
  "effective_date": "2024-06-30"
}
```

#### POST /api/v1/offboardings/{id}/complete
Complete offboarding.

### HR/EmployeeDocuments

#### GET /api/v1/employees/{id}/documents
List employee documents.

#### POST /api/v1/employees/{id}/documents
Upload employee document.

**Request Body:**
```json
{
  "document_type_id": 1,
  "title": "KTP",
  "issue_date": "2020-01-01",
  "expiry_date": "2030-01-01",
  "file": "base64_encoded_file"
}
```

#### PUT /api/v1/employees/{id}/documents/{docId}
Update document.

#### DELETE /api/v1/employees/{id}/documents/{docId}
Delete document.

#### POST /api/v1/employees/{id}/documents/{docId}/verify
Verify document.

### HR/EmployeeContracts

#### GET /api/v1/employees/{id}/contracts
List employee contracts.

#### POST /api/v1/employees/{id}/contracts
Create contract.

**Request Body:**
```json
{
  "contract_type": "permanent",
  "start_date": "2024-01-15",
  "end_date": null,
  "salary": 1500000000,
  "currency": "IDR",
  "benefits": { "health": true, "insurance": true }
}
```

#### PUT /api/v1/employees/{id}/contracts/{contractId}
Update contract.

#### POST /api/v1/employees/{id}/contracts/{contractId}/renew
Renew contract.

### DocumentManagement

#### GET /api/v1/documents
List documents.

**Query Parameters:**
- `category` (integer): Filter by category
- `status` (string): Filter by status
- `owner_type` (string): Filter by owner type
- `owner_id` (integer): Filter by owner ID

#### GET /api/v1/documents/{id}
Get document detail with versions.

#### POST /api/v1/documents
Create document.

**Request Body:**
```json
{
  "category_id": 1,
  "title": "Company Policy",
  "owner_type": "App\\Modules\\DocumentManagement\\Models\\Document",
  "owner_id": null,
  "is_confidential": false
}
```

#### PUT /api/v1/documents/{id}
Update document.

#### DELETE /api/v1/documents/{id}
Archive document.

#### POST /api/v1/documents/{id}/versions
Upload new version.

**Request Body:**
```json
{
  "file": "base64_encoded_file",
  "change_summary": "Updated section 3"
}
```

#### GET /api/v1/documents/{id}/versions/{versionId}/download
Download specific version.

#### POST /api/v1/documents/{id}/approve
Approve document.

#### POST /api/v1/documents/{id}/reject
Reject document.

### HR/Positions

#### GET /api/v1/positions
List positions.

#### GET /api/v1/positions/{id}
Get position detail.

#### POST /api/v1/positions
Create position.

#### PUT /api/v1/positions/{id}
Update position.

#### DELETE /api/v1/positions/{id}
Archive position.

### HR/OrganizationStructures

#### GET /api/v1/organization-structures
List organization structure.

#### GET /api/v1/organization-structures/{id}
Get organization structure detail.

#### POST /api/v1/organization-structures
Create organization structure.

#### PUT /api/v1/organization-structures/{id}
Update organization structure.

#### DELETE /api/v1/organization-structures/{id}
Archive organization structure.

### HR/WorkLocations

#### GET /api/v1/work-locations
List work locations.

#### GET /api/v1/work-locations/{id}
Get work location detail.

#### POST /api/v1/work-locations
Create work location.

#### PUT /api/v1/work-locations/{id}
Update work location.

#### DELETE /api/v1/work-locations/{id}
Archive work location.

### HR/IntegrationContracts

#### GET /api/v1/integration/employees/{id}/snapshot
Get employee snapshot for integration.

**Response:**
```json
{
  "success": true,
  "data": {
    "employee_id": 1,
    "employee_number": "EMP-001",
    "full_name": "John Doe",
    "email": "john@example.com",
    "position": "Software Engineer",
    "department": "Engineering",
    "work_location": "Jakarta Office",
    "employment_type": "Permanent",
    "employment_status": "Active",
    "hire_date": "2024-01-15",
    "manager": "Jane Smith",
    "active_contracts": [...],
    "documents": [...]
  }
}
```

#### GET /api/v1/integration/employees/{id}/contract-snapshot
Get employee contract snapshot.

#### GET /api/v1/integration/employees/{id}/document-compliance-snapshot
Get employee document compliance snapshot.

### Reports

#### GET /api/v1/reports/employee-headcount
Employee headcount report.

#### GET /api/v1/reports/document-compliance
Document compliance report.

#### GET /api/v1/reports/onboarding-status
Onboarding status report.

#### GET /api/v1/reports/offboarding-status
Offboarding status report.

#### GET /api/v1/reports/document-expiry
Document expiry report.

## Rate Limiting

- 60 requests per minute per user
- 1000 requests per minute per API token

## Error Codes

| Code | Description |
|---|---|
| VALIDATION_ERROR | Input validation failed |
| NOT_FOUND | Resource not found |
| FORBIDDEN | Insufficient permissions |
| UNAUTHORIZED | Authentication required |
| CONFLICT | Resource conflict |
| SERVER_ERROR | Internal server error |
| RATE_LIMITED | Too many requests |

## Versioning

API versioning via URL path: `/api/v1/...`

Deprecated versions will be supported for 6 months after deprecation notice.
