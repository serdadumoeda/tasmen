# Tasmen API Documentation (V1)

This document provides instructions for integrating with the Tasmen application API. The API allows external systems, in accordance with SPBE principles, to access core data such as projects, users, and tasks in a secure, read-only manner.

## Base URL

All API endpoints are prefixed with the following base URL:

```
https://your-tasmen-app-url.go.id/api/v1
```

---

## Authentication

The API uses a **Bearer Token** model for authentication. Each external system must be configured as an **API Client** within the Tasmen application by a Superadmin.

### How to Get an API Key

1.  A **Superadmin** must log in to the Tasmen application.
2.  Navigate to **Manajemen Tim** -> **Manajemen Integrasi**.
3.  Create a new **API Client** (e.g., "e-SAKIP KemenpanRB").
4.  Generate a new **API Key** for that client.
5.  The generated key (a long string of characters) will be **displayed only once**. This key must be securely copied and provided to the technical team of the external system.

### Making Authenticated Requests

The external system must include the API Key in the `Authorization` header for every request.

**Header Format:**
```
Authorization: Bearer <YOUR_API_KEY_HERE>
```

---

## Standard Response Structure (Envelope)

All API responses, both for success and failure, follow a standardized JSON structure.

### Success Response

A successful request will always have `success: true` and the requested data will be inside the `data` object.

**Structure:**
```json
{
  "success": true,
  "message": "A descriptive message of the result.",
  "data": { ... }
}
```

### Error Response

A failed request will have `success: false`, a null `data` field, and an error message. It may also contain an `errors` object with more details.

**Structure:**
```json
{
  "success": false,
  "message": "A summary of why the request failed.",
  "data": null,
  "errors": { ... } // Optional, for validation errors
}
```

---

## API Endpoints

All endpoints listed below require the `Authorization: Bearer <token>` header.

### Status Check

A simple endpoint to verify that the API is running and that your API Key is valid.

- **Endpoint:** `GET /status`
- **Description:** Checks the API service status and confirms authentication.
- **Example Success Response (`200 OK`):**
  ```json
  {
      "success": true,
      "message": "API service is running and accessible.",
      "data": {
          "service_status": "online",
          "authenticated_client": "e-SAKIP KemenpanRB",
          "timestamp": "2025-08-17T21:00:00.000000Z"
      }
  }
  ```

### Projects

#### List Projects

- **Endpoint:** `GET /projects`
- **Description:** Retrieves a paginated list of all projects.
- **Example Success Response (`200 OK`):**
  ```json
  {
      "success": true,
      "message": "Projects retrieved successfully.",
      "data": {
          "current_page": 1,
          "data": [
              {
                  "id": 1,
                  "name": "Pengembangan Aplikasi E-Kinerja",
                  ...
              }
          ],
          ...
      }
  }
  ```

#### Get a Single Project

- **Endpoint:** `GET /projects/{id}`
- **Description:** Retrieves the details of a specific project.
- **Example Success Response (`200 OK`):**
  ```json
  {
      "success": true,
      "message": "Project retrieved successfully.",
      "data": {
          "id": 1,
          "name": "Pengembangan Aplikasi E-Kinerja",
          "owner": { ... },
          ...
      }
  }
  ```

### Users

#### List Users

- **Endpoint:** `GET /users`
- **Description:** Retrieves a paginated list of active users.
- **Example Success Response (`200 OK`):**
  ```json
  {
      "success": true,
      "message": "Users retrieved successfully.",
      "data": {
          "current_page": 1,
          "data": [
              {
                  "id": 10,
                  "name": "Andi Pratama",
                  ...
              }
          ],
          ...
      }
  }
  ```

#### Get a Single User

- **Endpoint:** `GET /users/{id}`
- **Description:** Retrieves the details of a specific user.
- **Example Success Response (`200 OK`):**
  ```json
  {
      "success": true,
      "message": "User retrieved successfully.",
      "data": {
          "id": 10,
          "name": "Andi Pratama",
          ...
      }
  }
  ```

### Tasks

#### List Tasks

- **Endpoint:** `GET /tasks`
- **Description:** Retrieves a paginated list of all tasks.
- **Example Success Response (`200 OK`):**
  ```json
  {
      "success": true,
      "message": "Tasks retrieved successfully.",
      "data": {
          "current_page": 1,
          "data": [
              {
                  "id": 150,
                  "title": "Membuat endpoint API untuk SKP",
                  ...
              }
          ],
          ...
      }
  }
  ```

#### Get a Single Task

- **Endpoint:** `GET /tasks/{id}`
- **Description:** Retrieves the details of a specific task.
- **Example Success Response (`200 OK`):**
  ```json
  {
      "success": true,
      "message": "Task retrieved successfully.",
      "data": {
          "id": 150,
          "title": "Membuat endpoint API untuk SKP",
          ...
      }
  }
  ```
