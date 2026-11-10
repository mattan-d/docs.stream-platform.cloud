# API: User Videos List

Documentation for the API that returns the list of videos belonging to a given user. Intended for external systems (Moodle, LMS, portals) that display a user's videos or build watch/embed links.

---

## 1. Overview

| | |
|---|---|
| **Endpoint** | `GET` or `POST` `/webservice/api/user-videos` |
| **Full URL example** | `https://your-stream-domain.example/webservice/api/user-videos` |
| **Authentication** | Bearer token (API key) |
| **Response** | JSON |

---

## 2. Authentication

Send the API key in the request header:

```
Authorization: Bearer YOUR_API_KEY
```

The API key is the same as used for video upload (moodle-upload) and other instance APIs. Without the header or with an invalid key, the response will be JSON with `error: true` and an error message.

---

## 3. Parameters

### Required (one of)

| Parameter | Type | Description |
|-----------|------|-------------|
| **email** | string | User's email in the stream platform. Returns only videos owned by the user with this email. |
| **userid** | int | User id in the stream platform (id from users table). Returns only videos owned by this user. |

### Optional

| Parameter | Type | Description |
|-----------|------|-------------|
| **limit** | int | Maximum number of videos in the response (1–100). Default: 50. |
| **offset** | int | Number of records to skip (pagination). Default: 0. E.g. page 2: offset=50 with limit=50. |

Parameters can be sent via **GET** (query string) or **POST** (request body, e.g. form-urlencoded).

---

## 4. Request Examples

### GET (browser / cURL)

```bash
curl -X GET "https://your-stream-domain.example/webservice/api/user-videos?email=teacher%40school.ac.il&limit=20" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

By user id:

```bash
curl -X GET "https://your-stream-domain.example/webservice/api/user-videos?userid=42&limit=10&offset=0" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

### POST (e.g. from Moodle plugin)

```php
$stream_base = 'https://your-stream-domain.example';
$api_key = 'YOUR_API_KEY';
$user_email = $USER->email;

$ch = curl_init($stream_base . '/webservice/api/user-videos');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'email' => $user_email,
        'limit' => 25,
        'offset' => 0,
    ]),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $api_key,
    ],
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
if ($data && isset($data['error']) && $data['error'] === false && isset($data['videos'])) {
    $videos = $data['videos'];
    $total = $data['total'];
    // Display list, watch/embed links, etc.
}
```

---

## 5. Responses

### Success (HTTP 200)

```json
{
  "error": false,
  "message": "",
  "videos": [
    {
      "id": 12345,
      "title": "Lesson 1 - Introduction",
      "description": "Video description",
      "thumbnail": "https://...",
      "duration": "12:34",
      "timecreated": 1700000000,
      "timecreated_formatted": "15/11/2023 10:00:00",
      "privacy": 2,
      "views": 150,
      "watch_url": "https://your-stream-domain.example/watch/12345",
      "embed_url": "https://your-stream-domain.example/embed/12345",
      "has_subtitles": true
    }
  ],
  "total": 1
}
```

| Field | Description |
|-------|-------------|
| **error** | Always `false` on success. |
| **message** | Empty on success. |
| **videos** | Array of video objects (see below). |
| **total** | Total number of videos for the user (before limit/offset). |

Fields in each video object in **videos**:

| Field | Type | Description |
|-------|------|-------------|
| **id** | int | Video id in the stream platform. |
| **title** | string | Video title. |
| **description** | string | Description (plain text). |
| **thumbnail** | string | Thumbnail image URL. |
| **duration** | string | Video length (e.g. "12:34"). |
| **timecreated** | int | Creation time (Unix timestamp). |
| **timecreated_formatted** | string | Formatted creation time (e.g. dd/mm/yyyy hh:mm:ss). |
| **privacy** | int | Privacy level: 0=private, 1=public, 2=login required, 3=link only. |
| **views** | int | View count. |
| **watch_url** | string | Direct link to the watch page. |
| **embed_url** | string | Base embed URL. For embed with JWT add `?token=...` – see [EMBED-EXTERNAL-JWT.md](EMBED-EXTERNAL-JWT.md). |
| **has_subtitles** | bool | `true` when captions are ready and available for AI features; otherwise `false`. |

### Error (HTTP 200 with error: true)

```json
{
  "error": true,
  "message": "Either email or userid is required.",
  "videos": [],
  "total": 0
}
```

Common messages:

- `The API key is missing. Send Authorization: Bearer <apikey>.` – Missing Authorization header.
- `Invalid API key.` – API key is invalid.
- `Either email or userid is required.` – Neither email nor userid was sent.
- `User not found for the given email.` – No user with the given email.
- `User not found for the given userid.` – No user with the given id.

---

## 6. Typical Use

1. **Display "My videos" in an external system**  
   Send the logged-in user's `email` (or `userid`), get `videos` and `total`, and render a list with `watch_url` or `embed_url`.

2. **Show subtitle/AI availability in UI**  
   Use `has_subtitles` to show whether each video currently has captions ready (e.g. enable "Ask AI" / "Generate questions" buttons only when `true`).

3. **Pagination**  
   Send `limit` and `offset` (e.g. `limit=20`, `offset=0` for first page, `offset=20` for second). Use `total` for the overall count.

4. **Embed with JWT**  
   Use `embed_url` as the base and create a JWT (see [EMBED-EXTERNAL-JWT.md](EMBED-EXTERNAL-JWT.md)), then append `?token={JWT}` to the URL to show login-protected videos.

---

## 7. Related APIs

- **Reels only** (videos tagged `reels`, page-based pagination, newest first): [API-USER-REELS.md](API-USER-REELS.md) — `GET`/`POST` `/webservice/api/user-reels`.

---

## 8. Summary

| Topic | Value |
|-------|--------|
| **URL** | `GET` or `POST` `https://{domain}/webservice/api/user-videos` |
| **Authentication** | `Authorization: Bearer {API_KEY}` |
| **User identification** | `email` or `userid` (one required) |
| **Pagination** | `limit` (1–100), `offset` (0+) |
| **Response** | JSON: `error`, `message`, `videos[]` (including `has_subtitles`), `total` |
