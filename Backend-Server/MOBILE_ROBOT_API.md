# Mobile Robot API (delete / put / post + live location)

This document explains the API requests you need for:
1) Managing a **mobile robot definition** (`MobileSensor` with type `CAR_ROBOT`) via Admin CRUD.
2) Updating and reading the robot **live location**.

## Important note about Admin CRUD (why 422 happens)
The Admin CRUD controller submits Symfony form data using:
`$request->request->all()`

That means you must send the payload as **form fields**:
- `Content-Type: multipart/form-data` (recommended), or
- `Content-Type: application/x-www-form-urlencoded`

Sending raw JSON to these admin endpoints usually results in missing form fields and therefore a **422 validation error**.

---

## 1) Admin CRUD: Mobile Robot definition (`MobileSensor`)
Base endpoint: `/api/admin/mobile-sensors`

### 1.1 Create (POST)
- Endpoint: `POST /api/admin/mobile-sensors`

Headers:
- `Authorization: Bearer <token>`
- `Content-Type: multipart/form-data` *(recommended)* or `application/x-www-form-urlencoded`

Body payload (form fields):
- `name` (string)
- `type` (must be `CAR_ROBOT` for the mobile robot)
- `status` (string, e.g. `ACTIVE`)
- `latitude` (string)
- `longitude` (string)
- `lastSeenAt` (ISO datetime string)
- `center` (center id)

Example (form-data):
```bash
curl -X POST "https://YOUR_DOMAIN/api/admin/mobile-sensors" \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Robot Alpha" \
  -F "type=CAR_ROBOT" \
  -F "status=ACTIVE" \
  -F "latitude=32.62545" \
  -F "longitude=33.26225" \
  -F "lastSeenAt=2026-03-25T12:00:00+00:00" \
  -F "center=1"
```

### 1.2 Update (PUT)
- Endpoint: `PUT /api/admin/mobile-sensors/{id}`

Headers:
- `Authorization: Bearer <token>`
- `Content-Type: multipart/form-data` *(recommended)* or `application/x-www-form-urlencoded`

Body payload (form fields):
- Same fields as POST.
- The controller uses `submitAttempt(..., false)` for updates, so you *may* send a subset, but to avoid 422, safest is to send all required fields again.

Example (form-data):
```bash
curl -X PUT "https://YOUR_DOMAIN/api/admin/mobile-sensors/15" \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Robot Alpha Updated" \
  -F "type=CAR_ROBOT" \
  -F "status=ACTIVE" \
  -F "latitude=32.62545" \
  -F "longitude=33.26225" \
  -F "lastSeenAt=2026-03-25T12:00:00+00:00" \
  -F "center=1"
```

### 1.3 Delete (DELETE)
- Endpoint: `DELETE /api/admin/mobile-sensors/{id}`

Headers:
- `Authorization: Bearer <token>`

Body:
- Usually none.

Example:
```bash
curl -X DELETE "https://YOUR_DOMAIN/api/admin/mobile-sensors/15" \
  -H "Authorization: Bearer $TOKEN"
```

---

## 2) Live location: update the robot position

### 2.1 Device ingest (robot/device → backend)
- Endpoint: `POST /api/device-ingest/mobile-sensors/{id}/location`

Headers:
- `X-Device-Key: <shared static key>` *(required)*
- `Content-Type: application/json`

JSON body:
- required:
  - `latitude` (string)
  - `longitude` (string)
- optional:
  - `recordedAt` (ISO datetime string)

Example:
```bash
curl -X POST "https://YOUR_DOMAIN/api/device-ingest/mobile-sensors/15/location" \
  -H "X-Device-Key: YOUR_DEVICE_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": "32.62610",
    "longitude": "33.26300",
    "recordedAt": "2026-03-31T10:15:00+00:00"
  }'
```

This updates `LocationLive` (latest) and `LocationHistory` (log).

### 2.2 Mobile app update (brigade app → backend)
- Endpoint: `POST /api/mobile/fire-brigade/locations/update`

Headers:
- `Authorization: Bearer <mobile token>`
- `Content-Type: application/json`

JSON body (required):
- `sourceType`: must be `MOBILE_SENSOR`
- `sourceId`: the `MobileSensor` id (robot id)
- `latitude` (string)
- `longitude` (string)

JSON body (optional):
- `recordedAt` (ISO datetime string)

Example:
```bash
curl -X POST "https://YOUR_DOMAIN/api/mobile/fire-brigade/locations/update" \
  -H "Authorization: Bearer $MOBILE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sourceType": "MOBILE_SENSOR",
    "sourceId": 15,
    "latitude": "32.62610",
    "longitude": "33.26300",
    "recordedAt": "2026-03-31T10:15:00+00:00"
  }'
```

The backend validates that the `sourceId` belongs to the same `center` as the authenticated brigade.

---

## 3) Why you get 422 on Admin CRUD
If you see 422 on:
- `POST /api/admin/mobile-sensors`
- `PUT /api/admin/mobile-sensors/{id}`

Most common cause:
You sent JSON with `Content-Type: application/json`.

But the controller reads form fields from `$request->request->all()`, so the form is empty and validation fails (`NotBlank`, choice validation, etc.).

Fix:
Send the payload as `multipart/form-data` or `application/x-www-form-urlencoded` with the exact field names:
`name,type,status,longitude,latitude,lastSeenAt,center`

---

## 4) Read last live location (for verification)
- Endpoint: `GET /api/admin/locations/live`

Headers:
- `Authorization: Bearer <token>`

Query params:
- `sourceType`: `MOBILE_SENSOR`
- `sourceId`: `MobileSensor` id (recommended if you want a specific robot)
- `center`: center id (optional)

Example:
```bash
curl -X GET "https://YOUR_DOMAIN/api/admin/locations/live?sourceType=MOBILE_SENSOR&sourceId=15" \
  -H "Authorization: Bearer $TOKEN"
```

Response contains `items` including:
- `latitude`, `longitude`
- `recordedAt` (timestamp of last update)
