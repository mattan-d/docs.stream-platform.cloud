# Reels embed (chromeless player)

Use this embed when you want the video **without the standard player UI**: no Plyr control bar, no header/footer branding, no topics drawer, and no questions UI. Playback is **tap / click on the video** (play–pause). Intended for **reel** clips (videos tagged `reels` in the platform, e.g. AI highlight outputs).

---

## 1. URLs

| Type | Pattern | Example |
|------|---------|---------|
| **Pretty URL (recommended)** | `https://{domain}/embed-reels/{videoId}` | `https://stream.example/embed-reels/12345` |
| **With JWT in path** (same as classic embed) | `https://{domain}/embed-reels/{videoId}/{token}` | For password-protected videos when your integration passes the token segment |
| **Classic embed + query** | `https://{domain}/embed/{videoId}?reels=1` | Equivalent to `embed-reels/{videoId}` |

Optional query parameters still apply where supported (e.g. `token=` for JWT on the query string if your setup uses it).

### Autoplay (query)

Add **`autoplay=1`** or **`autoplay=true`** to start playback automatically (same on standard embed URLs). The player adds **`playsinline`** for mobile; **unmuted** autoplay is often blocked by browsers until the user interacts with the page—if playback does not start, check the iframe **`allow`** attribute and browser autoplay policy. Example:

`https://{domain}/embed-reels/12345?autoplay=1`

For iframes, include **`allow="autoplay; ..."`** (see §3).

---

## 2. Who can open it

- The video must be **published** (`READY`) and have the **`reels` tag**. Otherwise the page returns **404**.
- **Privacy** rules are the same as the normal embed: public / link / and password-protected when a valid **JWT** is provided (see [EMBED-EXTERNAL-JWT.md](EMBED-EXTERNAL-JWT.md)).

---

## 3. iframe example

```html
<iframe
  src="https://your-stream-domain.example/embed-reels/12345"
  width="360"
  height="640"
  style="border:0;"
  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
  allowfullscreen
  title="Reel"></iframe>
```

Vertical reels often use **9:16**; set `width` / `height` (or CSS aspect-ratio) to match your layout.

---

## 4. JWT (login / password-protected)

Same JWT flow as the standard embed:

1. Build a JWT signed with your instance API key (`HS256`), with `identifier` equal to the **video id** (see [EMBED-EXTERNAL-JWT.md](EMBED-EXTERNAL-JWT.md)).
2. Pass it either as:
   - **Path:** `https://{domain}/embed-reels/{videoId}/{jwt}`, or  
   - **Query:** `https://{domain}/embed-reels/{videoId}?token={jwt}` (if your server/router forwards query to `embed.php`).

---

## 5. Behaviour notes

| Topic | Behaviour |
|-------|-----------|
| **Controls** | No visible player buttons; **click / tap** toggles play and pause. |
| **Mobile (iOS / some Android)** | Native `<video>` path: no control strip; tap toggles playback. |
| **Desktop** | Plyr is initialized with an **empty** control list; UI chrome is also hidden with CSS. |
| **Captions** | If the video has captions/tracks, they can still be used by the engine where applicable; there is no captions menu in the chromeless UI. |
| **API list** | [API-USER-REELS.md](API-USER-REELS.md) returns `embed_reels_url` for each item. |
| **Autoplay** | `?autoplay=1` or `?autoplay=true` — not forced muted; may be blocked with sound until user gesture. |

---

## 6. Comparison with standard embed

| | Standard `/embed/{id}` | Reels `/embed-reels/{id}` |
|--|----------------------|----------------------------|
| Plyr controls | Yes | No |
| Header (title / user) | Yes | No |
| Footer logo | Yes | No |
| Topics / questions UI | Yes | Hidden |
| Tag requirement | Any published video | Must have **`reels` tag** |

---

## 7. Summary

| Item | Value |
|------|--------|
| **URL** | `https://{domain}/embed-reels/{videoId}` |
| **Alt** | `https://{domain}/embed/{videoId}?reels=1` |
| **Tag** | Video must be tagged **`reels`** |
| **Playback** | Click / tap video surface |
