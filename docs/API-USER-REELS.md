# API: User Reels List

Documentation for the API that returns **reel** videos (short highlights) for a given user. Reels are normal published videos that are linked to the platform tag **`reels`** (for example, clips created by the AI highlight flow). Results are ordered **newest first** (`timecreated` descending).

---

## 1. Overview

| | |
|---|---|
| **Endpoint** | `GET` or `POST` `/webservice/api/user-reels` |
| **Full URL example** | `https://your-stream-domain.example/webservice/api/user-reels` |
| **Authentication** | Bearer token (API key, same as other instance APIs) |
| **Response** | JSON |

---

## 2. Authentication

```
Authorization: Bearer YOUR_API_KEY
```

Use the same API key as for [user videos](API-USER-VIDEOS.md), Moodle upload, etc. Invalid or missing key returns JSON with `error: true`.

---

## 3. Parameters

### Required (one of)

| Parameter | Type | Description |
|-----------|------|-------------|
| **email** | string | User email in the stream platform (case-insensitive match). |
| **userid** | int | User id in the stream platform (`users.id`). |

### Optional (pagination)

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| **page** | int | `1` | Page number (1-based). |
| **per_page** | int | `20` | Items per page (1–100). |

**Sorting:** always **newest to oldest** (`timecreated` DESC).

**Filter:** only videos with **`status` = published (ready)** and the **`reels`** tag. If the `reels` tag does not exist yet in the database, the API returns an empty list (not an error).

Parameters may be sent as **GET** query string or **POST** body (e.g. `application/x-www-form-urlencoded`).

---

## 4. Request examples

### GET (cURL)

```bash
curl -G "https://your-stream-domain.example/webservice/api/user-reels" \
  --data-urlencode "email=teacher@school.ac.il" \
  --data-urlencode "page=1" \
  --data-urlencode "per_page=10" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

By user id:

```bash
curl -G "https://your-stream-domain.example/webservice/api/user-reels" \
  --data-urlencode "userid=42" \
  --data-urlencode "page=2" \
  --data-urlencode "per_page=20" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

### POST (PHP example)

```php
$stream_base = 'https://your-stream-domain.example';
$api_key = 'YOUR_API_KEY';
$user_email = $USER->email;

$ch = curl_init($stream_base . '/webservice/api/user-reels');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'email' => $user_email,
        'page' => 1,
        'per_page' => 25,
    ]),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $api_key,
    ],
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if ($data && empty($data['error']) && isset($data['reels'])) {
    foreach ($data['reels'] as $reel) {
        // $reel['watch_url'], $reel['embed_url'], $reel['embed_reels_url'] (chromeless — see EMBED-REELS.md)
    }
    $total = $data['total'];
    $total_pages = $data['total_pages'];
}
```

---

## 5. Response

### Success (HTTP 200)

```json
{
  "error": false,
  "message": "",
  "reels": [
    {
      "id": 12345,
      "title": "Highlight clip",
      "description": "",
      "thumbnail": "https://...",
      "duration": "00:28",
      "timecreated": 1700000000,
      "timecreated_formatted": "15/11/2023 10:00:00",
      "privacy": 1,
      "views": 10,
      "watch_url": "https://your-stream-domain.example/watch/12345",
      "embed_url": "https://your-stream-domain.example/embed/12345",
      "embed_reels_url": "https://your-stream-domain.example/embed-reels/12345"
    }
  ],
  "total": 42,
  "page": 1,
  "per_page": 20,
  "total_pages": 3
}
```

| Field | Description |
|-------|-------------|
| **reels** | Array of reel video objects (same core fields as [API-USER-VIDEOS](API-USER-VIDEOS.md), plus **`embed_reels_url`**). |
| **total** | Total number of matching reels for this user (all pages). |
| **page** | Current page. |
| **per_page** | Page size used. |
| **total_pages** | `ceil(total / per_page)`; `0` when `total` is 0. |

### Error (`error: true`)

Same style as user-videos: `message` explains the failure; `reels` is `[]`, `total` is `0`.

Common messages:

- `The API key is missing...`
- `Invalid API key.`
- `Either email or userid is required.`
- `User not found for the given email.`
- `User not found for the given userid.`

---

## 6. Pagination

- First page: `page=1`, `per_page=20` (default).
- Next page: `page=2`, same `per_page`.
- Use **`total_pages`** or **`total`** with **`per_page`** to build UI (e.g. “Page 2 of 5”).

---

## 7. Related

| Topic | Document |
|-------|----------|
| All videos for a user (not only reels) | [API-USER-VIDEOS.md](API-USER-VIDEOS.md) |
| Embed with JWT | [EMBED-EXTERNAL-JWT.md](EMBED-EXTERNAL-JWT.md) |
| Chromeless reels iframe | [EMBED-REELS.md](EMBED-REELS.md) (`embed_reels_url`) |

---

## 8. Summary

| Topic | Value |
|-------|--------|
| **URL** | `GET` or `POST` `https://{domain}/webservice/api/user-reels` |
| **Auth** | `Authorization: Bearer {API_KEY}` |
| **User** | `email` or `userid` (one required) |
| **Pagination** | `page` (≥1), `per_page` (1–100) |
| **Order** | Newest first |
| **Payload** | JSON: `error`, `message`, `reels[]`, `total`, `page`, `per_page`, `total_pages` |
