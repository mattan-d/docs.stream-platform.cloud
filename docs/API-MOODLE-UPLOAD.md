# API: Moodle Plugin – Direct Video Upload

Documentation for the API endpoint that allows an external Moodle plugin to upload a video file directly to the stream platform, with optional metadata (e.g. course id, activity id).

---

## 1. Overview

| | |
|---|---|
| **Endpoint** | `POST /webservice/api/moodle-upload` |
| **Full URL example** | `https://your-stream-domain.example/webservice/api/moodle-upload` |
| **Authentication** | Bearer token (API key) |
| **Content-Type** | `multipart/form-data` |

---

## 2. Authentication

Send the API key in the request header:

```
Authorization: Bearer YOUR_API_KEY
```

The API key is configured in the stream platform (config / admin). Without this header or with an invalid key, the response will be an error JSON.

---

## 3. Parameters

### Required

| Parameter | Type | Description |
|-----------|------|-------------|
| **email** | string | Email of the user who owns the video. If the user does not exist, they are created when firstname, lastname, and username are also sent. |
| **file** or **videofile** | file | Video file (multipart). Supported formats: mp4, flv, webm, mkv, vob, ogv, ogg, avi, wmv, mov, mpeg, mpg, etc. |

### Optional

| Parameter | Type | Description |
|-----------|------|-------------|
| **title** | string | Video title. Default: filename without extension. |
| **description** | string | Video description. |
| **metadata** | string | JSON string with extra data (course id, activity id, etc.). Stored in the video's `metadata` field. |
| **firstname** | string | First name – required only when creating a new user. |
| **lastname** | string | Last name – required only when creating a new user. |
| **username** | string | Username – required only when creating a new user. |
| **privacy** | int | Privacy level (0=private, 1=public, 2=login required, 3=link only). Default: 2 (login required). |

---

## 4. HTTP Request Examples

### cURL (command line)

```bash
curl -X POST "https://your-stream-domain.example/webservice/api/moodle-upload" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -F "file=@/path/to/video.mp4" \
  -F "email=teacher@school.ac.il" \
  -F "title=Lesson 3 - Math" \
  -F "metadata={\"courseid\":123,\"cmid\":456,\"activity\":\"assign\"}"
```

### PHP (Moodle plugin – multipart)

```php
$stream_base_url = 'https://your-stream-domain.example';
$api_key = 'YOUR_API_KEY';
$video_path = '/var/moodledata/temp/uploaded_video.mp4';

$postdata = [
    'file' => new CURLFile($video_path, 'video/mp4', 'lesson_1.mp4'),
    'email' => $USER->email,
    'title' => 'Lesson 1 - Introduction',
    'metadata' => json_encode([
        'courseid' => $COURSE->id,
        'cmid' => $cm->id,
        'activity' => 'stream',
    ]),
];

if ($USER->firstname && $USER->lastname) {
    $postdata['firstname'] = $USER->firstname;
    $postdata['lastname'] = $USER->lastname;
    $postdata['username'] = $USER->username;
}

$ch = curl_init($stream_base_url . '/webservice/api/moodle-upload');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postdata,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $api_key,
    ],
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
if ($data && !empty($data['error']) === false && isset($data['streamid'])) {
    $stream_id = (int) $data['streamid'];
    // Save $stream_id in Moodle (e.g. in plugin's table) for embedding/links.
} else {
    $error_message = isset($data['message']) ? $data['message'] : 'Upload failed';
    // Handle error.
}
```

---

## 5. Responses

### Success (HTTP 200)

```json
{
  "error": false,
  "streamid": 12345,
  "topic": "Lesson 1 - Introduction",
  "thumbnail": "https://..."
}
```

- **streamid** – Video id in the stream platform. Store it in Moodle (e.g. in the plugin table) for embed or watch links.
- **topic** – Video title as stored.
- **thumbnail** – Thumbnail URL (after processing).

### Error (HTTP 200 with error: true)

```json
{
  "error": true,
  "message": "No video file received. Send a file in the \"file\" or \"videofile\" field."
}
```

Common error messages:

- `The API key is missing. Send Authorization: Bearer <apikey>.` – Missing Authorization header.
- `Invalid API key.` – Invalid API key.
- `The email field is required.` – Email field not sent.
- `No video file received...` – No file sent in `file` or `videofile`.
- File too large or invalid format – size/format validation error.

---

## 6. Metadata Field

The **metadata** field is stored as-is in the video's `metadata` column. Sending JSON is recommended, e.g.:

- `courseid` – Moodle course id  
- `cmid` – Course module id  
- `activity` – Activity type  
- Any other fields the plugin needs later

Example:

```json
{
  "courseid": 42,
  "cmid": 101,
  "activity": "stream",
  "section": 2
}
```

In the Moodle plugin: send the string from `json_encode($metadata_array)` in the `metadata` field.

---

## 7. Summary for Plugin Developers

1. **URL:** `POST {base_url}/webservice/api/moodle-upload`  
2. **Header:** `Authorization: Bearer {api_key}`  
3. **Body:** `multipart/form-data` with file field `file` (or `videofile`), required `email`, and optional `title`, `description`, `metadata` (JSON string), and `firstname`/`lastname`/`username` for user creation.  
4. **Response:** JSON with `error`, `streamid`, `topic`, `thumbnail`.  
5. **Storage in Moodle:** Store `streamid` in the activity module to build watch or embed links.
