# External Embed with JWT

Documentation for embedding a stream platform video on an external page (e.g. Moodle, LMS) using JWT authentication, so that password-protected videos can be viewed without the user logging in to the stream platform.

---

## 1. Overview

| | |
|---|---|
| **Purpose** | Display a stream video in an iframe on an external page, with access to login-protected videos via JWT. |
| **Embed URL** | `https://{stream-domain}/embed/{videoid}` or with token: `https://{stream-domain}/embed/{videoid}?token={JWT}` |
| **Reels (chromeless)** | `https://{stream-domain}/embed-reels/{videoid}?token={JWT}` — see [EMBED-REELS.md](EMBED-REELS.md) (video must have the `reels` tag). |
| **Autoplay (optional)** | Append `&autoplay=1` (or `?autoplay=1` if there is no query yet). Not forced muted; many browsers block **audible** autoplay without a user gesture—use `allow="autoplay; ..."` on the iframe. |
| **Authentication** | JWT signed with HS256 using the instance API key (same key as for video upload API). |

---

## 2. Embed URLs

### Without Token (public / link-only videos)

- `https://{stream-domain}/embed/{videoid}`  
- Suitable for videos with **public** or **link-only** privacy.
- Videos with **login required** privacy will not play without a token.

### With Token (JWT) – recommended for external systems

- `https://{stream-domain}/embed/{videoid}?token={JWT}`
- Or (per rewrite rules): `https://{stream-domain}/embed/{videoid}/{JWT}`

When the JWT is valid and contains `identifier` equal to `videoid`, the platform allows playback of **login required** videos as well.

---

## 3. JWT Payload

The token must be signed with **HS256** using the stream instance **API key** (the `apikey` value in config).

Payload claims:

| Claim | Type | Required | Description |
|-------|-----|----------|-------------|
| **fullname** | string | yes | Viewer's full name (used for user creation/lookup). |
| **email** | string | yes | Viewer's email (normalized to lowercase). |
| **identifier** | int | yes | Video id (videoid). **Must equal the videoid in the URL** to allow playback of login-protected videos. |
| **matricula** | string | no | Student/user identifier (optional). |
| **nbf** | int | no | Not Before – Unix time from which the token is valid. |
| **exp** | int | no | Expiration – Unix time when the token expires. |

Example payload (before signing):

```json
{
  "fullname": "John Doe",
  "email": "student@school.ac.il",
  "identifier": 12345,
  "matricula": "12345678",
  "nbf": 1700000000,
  "exp": 1700086400
}
```

- `identifier` must be the **video id** (same as in the embed URL).
- `nbf` and `exp` are recommended to limit validity to a session or short period.

---

## 4. Generating the JWT in the External System

The API key (secret) must match the one configured for the stream instance. Store it securely in the external system (e.g. Moodle settings).

### PHP (e.g. Moodle) – with firebase/php-jwt

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$stream_apikey = 'YOUR_STREAM_API_KEY';
$videoid = 12345;
$user = $USER;

$payload = [
    'fullname' => $user->firstname . ' ' . $user->lastname,
    'email'    => strtolower($user->email),
    'identifier' => (int) $videoid,
    'matricula' => $user->idnumber ?? '',
    'nbf'      => time(),
    'exp'      => time() + (2 * 3600),
];

$jwt = JWT::encode($payload, $stream_apikey, 'HS256');
$embed_url = 'https://your-stream-domain.example/embed/' . $videoid . '?token=' . $jwt;
```

### PHP – with another library (HS256)

Generate a JWT with:

- Algorithm: **HS256**
- Secret: stream instance **API key**
- Payload: the claims above (fullname, email, identifier, optional matricula, nbf, exp)

### JavaScript (Node.js) – jsonwebtoken

```javascript
const jwt = require('jsonwebtoken');

const streamApikey = 'YOUR_STREAM_API_KEY';
const videoid = 12345;
const user = { fullname: 'Israel Israeli', email: 'student@school.ac.il' };

const payload = {
  fullname: user.fullname,
  email: user.email.toLowerCase(),
  identifier: videoid,
  nbf: Math.floor(Date.now() / 1000),
  exp: Math.floor(Date.now() / 1000) + 7200,
};

const token = jwt.sign(payload, streamApikey, { algorithm: 'HS256' });
const embedUrl = `https://your-stream-domain.example/embed/${videoid}?token=${token}`;
```

---

## 5. Embedding in a Page

Once you have the `embed_url` (embed URL including the token):

```html
<iframe
  src="<?= htmlspecialchars($embed_url) ?>"
  width="640"
  height="360"
  allowfullscreen
  allow="autoplay; fullscreen"
></iframe>
```

**Important:** Do not share or store the token in a static page. Generate a new token per user/session with short validity (e.g. until end of class or 2 hours).

---

## 6. Recommended Flow

1. User opens a page in the external system (e.g. a Moodle activity).
2. The server identifies the user and knows which `videoid` to show.
3. The server builds a JWT with `fullname`, `email`, `identifier` = `videoid`, and `nbf`/`exp`.
4. The server returns a page with an iframe whose `src` is:  
   `https://{stream-domain}/embed/{videoid}?token={JWT}`.
5. The browser loads the embed; the stream server validates the JWT and allows playback (including login-protected videos when `identifier` matches).

---

## 7. Summary

| Topic | Value |
|-------|--------|
| **URL without token** | `https://{domain}/embed/{videoid}` |
| **URL with token** | `https://{domain}/embed/{videoid}?token={JWT}` |
| **Algorithm** | HS256 |
| **Secret** | Stream instance API key |
| **Required claims** | fullname, email, identifier (= videoid) |
| **Validity** | Recommended: nbf, exp (e.g. 2 hours) |

With access to the API key (e.g. from the plugin settings in Moodle), you can generate the JWT on the server and embed the video on an external page with controlled access.
