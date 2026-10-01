# Project API

Client Project Tracker — CRUD for client projects. Base URL: `{API_BASE_URL}` (e.g. `http://localhost:8000/api`).

## Endpoints overview

| Method | Path | Purpose |
|---|---|---|
| GET | `/projects` | List projects (paginated, filterable) |
| GET | `/projects/{uuid}` | Get one project |
| POST | `/projects` | Create a project |
| PUT | `/projects/{uuid}` | Replace a project's fields |
| DELETE | `/projects/{uuid}` | Soft-delete a project |

> **Who can do what:** every endpoint requires a signed-in user (`Authorization: Bearer <token>` from `/auth/login` or `/auth/register`). Projects are **shared** — any authenticated user can view, create, update, and delete any project. No roles/permissions.

All requests send `Accept: application/json`. JSON bodies send `Content-Type: application/json`.

## Enums

**status**

| Value | Label |
|---|---|
| `planning` | Planning |
| `in_progress` | In Progress |
| `on_hold` | On Hold |
| `completed` | Completed |

**priority**

| Value | Label |
|---|---|
| `low` | Low |
| `medium` | Medium |
| `high` | High |

Send the **value**; the response returns both value and label.

## Response envelope

Every response, success or error:

```json
{ "success": true, "message": "string", "data": {} }
```

## Project object

```json
{
    "uuid": "9d3c1f7e-5b8a-4c2d-9e1f-2a3b4c5d6e7f",
    "client_name": "Acme Corp",
    "project_name": "Website Redesign",
    "description": "Full redesign of the marketing site.",
    "status": "in_progress",
    "status_label": "In Progress",
    "priority": "high",
    "priority_label": "High",
    "start_date": "2026-10-01",
    "due_date": "2026-12-15",
    "created_at": "2026-10-01T12:45:00.000000Z",
    "updated_at": "2026-10-01T12:45:00.000000Z"
}
```

| Field | Type | Notes |
|---|---|---|
| `uuid` | string | Public identifier — use it in URLs. There is no numeric `id` in the API. |
| `client_name` | string | |
| `project_name` | string | |
| `description` | string \| null | |
| `status` | enum string | See Enums |
| `status_label` | string | Display text for `status` |
| `priority` | enum string | See Enums |
| `priority_label` | string | Display text for `priority` |
| `start_date` | `YYYY-MM-DD` \| null | |
| `due_date` | `YYYY-MM-DD` \| null | |
| `created_at` | ISO 8601 | |
| `updated_at` | ISO 8601 | |

## Field rules (create and update)

| Field | Required | Rules |
|---|---|---|
| `client_name` | yes | string, max 255 |
| `project_name` | yes | string, max 255 |
| `description` | no | string, max 5000; omit or `null` to clear |
| `status` | yes | one of the status values |
| `priority` | yes | one of the priority values |
| `start_date` | no | `YYYY-MM-DD` |
| `due_date` | no | `YYYY-MM-DD`; must be on or after `start_date` |

---

## GET `/projects`

List projects. Default order: `due_date` ascending, projects with no due date last.

**Query parameters** (all optional)

| Param | Type | Notes |
|---|---|---|
| `status` | enum | Exact match |
| `priority` | enum | Exact match |
| `search` | string | Case-insensitive partial match on `client_name` or `project_name`; max 255 |
| `sort_by` | enum | `client_name`, `project_name`, `status`, `priority`, `start_date`, `due_date`, `created_at`. Default `due_date` |
| `sort_dir` | `asc` \| `desc` | Default `asc` |
| `page` | int ≥ 1 | Default `1` |
| `per_page` | int 1–100 | Default `15` |

**Sorting rules**
- `status` sorts by workflow order, not alphabetically: `planning` → `in_progress` → `on_hold` → `completed` (`asc`).
- `priority` sorts by rank: `low` → `medium` → `high` (`asc`). Use `sort_dir=desc` for high first.
- `start_date` / `due_date`: projects with no date always come last, in either direction.
- Ties are broken by soonest `due_date`, then newest created.

**Example:** `GET /projects?status=in_progress&search=acme&sort_by=priority&sort_dir=desc&page=1`

**200**

```json
{
    "success": true,
    "message": "Projects retrieved.",
    "data": {
        "projects": [ { "...Project object" } ],
        "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 }
    }
}
```

**Errors:** `401` unauthenticated · `422` invalid filter or sort value, e.g. `{ "data": { "status": ["Status must be one of: planning, in_progress, on_hold, completed."] } }`

---

## GET `/projects/{uuid}`

**200**

```json
{ "success": true, "message": "Project retrieved.", "data": { "project": { "...Project object" } } }
```

**Errors:** `401` · `404` `{ "success": false, "message": "Project not found.", "data": null }` (also returned for deleted projects)

---

## POST `/projects`

**Body**

```json
{
    "client_name": "Acme Corp",
    "project_name": "Website Redesign",
    "description": "Full redesign of the marketing site.",
    "status": "in_progress",
    "priority": "high",
    "start_date": "2026-10-01",
    "due_date": "2026-12-15"
}
```

**201**

```json
{ "success": true, "message": "Project created.", "data": { "project": { "...Project object" } } }
```

**Errors:** `401` · `422` validation (see below)

---

## PUT `/projects/{uuid}`

Full replacement: send **every** field, same body and rules as POST. An omitted optional field (`description`, `start_date`, `due_date`) is set to `null`.

**200**

```json
{ "success": true, "message": "Project updated.", "data": { "project": { "...Project object" } } }
```

**Errors:** `401` · `404` `Project not found.` · `422` validation

---

## DELETE `/projects/{uuid}`

Soft delete — the project disappears from every endpoint but stays recoverable in the database.

**200**

```json
{ "success": true, "message": "Project deleted.", "data": null }
```

**Errors:** `401` · `404` `Project not found.`

---

## Validation errors (422)

`message` is always `"The given data was invalid."`. `data` maps each field to an array of messages:

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "data": {
        "client_name": ["The client name field is required."],
        "project_name": ["The project name field is required."],
        "status": ["Status must be one of: planning, in_progress, on_hold, completed."],
        "priority": ["Priority must be one of: low, medium, high."],
        "due_date": ["Due date cannot be earlier than the start date."]
    }
}
```

Dates not in `YYYY-MM-DD` return: `The start date field must match the format Y-m-d.`

## Error status summary

| Status | When | `message` |
|---|---|---|
| 401 | Missing, invalid, or revoked token | `Unauthenticated.` |
| 404 | Unknown or deleted project `uuid` | `Project not found.` |
| 422 | Validation failure | `The given data was invalid.` |
| 429 | More than 60 requests/minute from one user | `Too many requests. Please try again in {n} seconds.` |

## Rate limiting

All project endpoints share a limit of **60 requests per minute per user** (tracked by account, not IP), together with `/auth/me` and `/auth/logout`. Every response carries `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers; a 429 also carries `Retry-After` (seconds). The `data` field is `null` on a 429.
