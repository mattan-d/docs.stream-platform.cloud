# API: Video Thumbnail URL

Documentation for the API that returns the thumbnail URL of a video by id. Intended for external systems (Moodle, LMS, portals) that need to display video thumbnails without fetching the full video list.

---

## 1. Overview

| | |
|---|---|
| **Endpoint** | `GET` or `POST` `/webservice/api/video-thumbnail` |
| **Full URL example** | `https://your-stream-domain.example/webservice/api/video-thumbnail` |
| **Authentication** | Bearer token (API key) |
| **Response** | JSON |

---

## 2. Authentication

Send the API key in the request header:

```
Authorization: Bearer YOUR_API_KEY
```

The API key is the same as used for other stream APIs (moodle-upload, user-videos, etc.). Without the header or with an invalid key, the response will be JSON with `error: true` and an error message.

---

## 3. Parameters

### Required

| Parameter | Type | Description |
|-----------|------|-------------|
| **id** or **videoid** | int | Video id in the stream platform (e.g. `streamid` from moodle-upload or `id` from user-videos). |

Send either `id` or `videoid`; both are accepted.

### Optional

None.

Parameters can be sent via **GET** (query string) or **POST** (request body).

---

## 4. Request Examples

### GET (cURL)

```bash
curl -X GET "https://your-stream-domain.example/webservice/api/video-thumbnail?id=12345" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

With `videoid`:

```bash
curl -X GET "https://your-stream-domain.example/webservice/api/video-thumbnail?videoid=12345" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

### PHP (e.g. from Moodle)

```php
$stream_base = 'https://your-stream-domain.example';
$api_key = 'YOUR_API_KEY';
$video_id = 12345;  // e.g. from plugin storage (streamid)

$url = $stream_base . '/webservice/api/video-thumbnail?id=' . (int) $video_id;
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $api_key,
    ],
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if ($data && isset($data['error']) && $data['error'] === false && !empty($data['thumbnail_url'])) {
    $thumbnail_url = $data['thumbnail_url'];
    $title = $data['title'];
    // Use $thumbnail_url in <img src="..."> or similar
}
```

---

## 5. Responses

### Success (HTTP 200)

```json
{
  "error": false,
  "message": "",
  "thumbnail_url": "https://your-stream-domain.example/files/thumbnails/2024/01/abc123.jpg",
  "id": 12345,
  "title": "Lesson 1 - Introduction"
}
```

| Field | Type | Description |
|-------|------|-------------|
| **error** | bool | `false` on success. |
| **message** | string | Empty on success. |
| **thumbnail_url** | string | Full URL of the video thumbnail image. Use as `src` in `<img>` or in CSS. |
| **id** | int | Video id (echo of the requested id). |
| **title** | string | Video title (for convenience). |

### Error (HTTP 200 with error: true)

```json
{
  "error": true,
  "message": "Video not found or not ready.",
  "thumbnail_url": null,
  "id": null,
  "title": null
}
```

Common messages:

- `The API key is missing. Send Authorization: Bearer <apikey>.` – Missing Authorization header.
- `Invalid API key.` – API key is invalid.
- `Video id is required. Send id or videoid parameter.` – No `id` or `videoid` was sent.
- `Invalid video id.` – Non-positive or non-numeric id.
- `Video not found or not ready.` – No video with this id, or video is not yet in READY status (e.g. still processing).

---

## 6. Typical Use

- **Display a single thumbnail:** After storing a `streamid` (e.g. from moodle-upload), call this API with that id to get the current thumbnail URL for use in course pages, cards, or lists.
- **Cache:** You may cache `thumbnail_url` for a period; regenerate if the video is re-processed or if you need an updated image.
- **Fallback:** If the video is still processing, the API returns an error; show a placeholder or “Processing…” until the video is ready and the call succeeds.

---

## 7. Summary

| Topic | Value |
|-------|--------|
| **URL** | `GET` or `POST` `https://{domain}/webservice/api/video-thumbnail` |
| **Authentication** | `Authorization: Bearer {API_KEY}` |
| **Parameters** | `id` or `videoid` (required) |
| **Response** | JSON: `error`, `message`, `thumbnail_url`, `id`, `title` |
