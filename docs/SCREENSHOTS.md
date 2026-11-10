# Generating screenshots for guides

Use `node docs/screenshot.js <url> <output-path>` from the project root. Video ID for screenshots: **1253** (see `.cursor/rules/screenshot-video-id.mdc`).

## Video settings guide (guides/video-settings.html)

To update the images shown in **guides/video-settings.html**, run the following. Base URL: `https://dev.video` (replace with your environment). Video ID: **1253**.

```bash
node docs/screenshot.js "https://dev.video/videos/edit/1253" docs/screenshot/video-settings-overview.png
node docs/screenshot.js "https://dev.video/videos/edit/1253#section-details" docs/screenshot/video-settings-details.png
node docs/screenshot.js "https://dev.video/videos/edit/1253#section-points" docs/screenshot/video-settings-points.png
node docs/screenshot.js "https://dev.video/videos/edit/1253#section-tags" docs/screenshot/video-settings-tags.png
node docs/screenshot.js "https://dev.video/videos/edit/1253#section-privacy" docs/screenshot/video-settings-privacy.png
node docs/screenshot.js "https://dev.video/videos/edit/1253#section-viewlimit" docs/screenshot/video-settings-viewlimit.png
node docs/screenshot.js "https://dev.video/videos/edit/1253#section-transcription" docs/screenshot/video-settings-transcription.png
node docs/screenshot.js "https://dev.video/videos/edit/1253#section-video" docs/screenshot/video-settings-trim.png
```

After running, refresh the guide at `guides/video-settings.html` to see the images.

## Video trim guide (guides/video-trim.html)

Dedicated screenshots in `docs/screenshot/`:

| File | Content |
|------|---------|
| `video-trim-timeline.png` | Timeline editor – tracks overview |
| `video-trim-cut-selection.png` | Range selection and cut action bar |
| `video-trim-captions-list.png` | Caption list editor |
| `video-trim-ai.png` | AI section (chapters / Reels buttons) |

To refresh via Playwright (video ID **1253**), capture the relevant sections on `/videos/edit/1253` and save under the names above. After updating images, refresh `guides/video-trim.html`.

## Admin settings guide (guides/admin-settings.html)

To update the images shown in **guides/admin-settings.html**, run the following. Base URL: `https://dev.video`. Page: `/admin/settings`. **Requires admin login** (the screenshot script must use an admin user).

```bash
node docs/screenshot.js "https://dev.video/admin/settings" docs/screenshot/admin-settings-overview.png
```

Optional: capture specific sections with hash anchors (e.g. `#section-general`, `#section-youtube`, `#section-saml2`):

```bash
node docs/screenshot.js "https://dev.video/admin/settings#section-general" docs/screenshot/admin-settings-general.png
node docs/screenshot.js "https://dev.video/admin/settings#section-youtube" docs/screenshot/admin-settings-youtube.png
```

After running, refresh the guide at `guides/admin-settings.html` to see the updated images.

## Studio guide (guides/studio.html)

Base URL: `https://dev.video` (replace with your environment). Studio page has no video ID. Use optional selectors to open the segment modal and capture the segments panel.

```bash
node docs/screenshot.js "https://dev.video/studio" docs/screenshot/studio-overview.png
node docs/screenshot.js "https://dev.video/studio" docs/screenshot/studio-source-list.png
node docs/screenshot.js "https://dev.video/studio" docs/screenshot/studio-segment-modal.png ".studio-add-segment"
node docs/screenshot.js "https://dev.video/studio" docs/screenshot/studio-segments-panel.png ".studio-add-segment" "#studio-confirm-segment"
```

- `studio-overview.png` / `studio-source-list.png`: full page of /studio.
- `studio-segment-modal.png`: opens first "הוסף מקטע", then captures with modal (בחירת מקטע) visible.
- `studio-segments-panel.png`: opens first "הוסף מקטע", clicks "הוסף מקטע" in modal to add one segment, then captures the right panel (מקטעים בסרטון החדש).

## Comments guide (guides/videos-comments.html)

Base URL: `https://dev.video`. Page: `/videos/comments` (ניהול הערות).

```bash
node docs/screenshot.js "https://dev.video/videos/comments" docs/screenshot/comments-overview.png
node docs/screenshot.js "https://dev.video/videos/comments" docs/screenshot/comments-filters.png
node docs/screenshot.js "https://dev.video/videos/comments" docs/screenshot/comments-table.png
node docs/screenshot.js "https://dev.video/videos/comments/edit/1" docs/screenshot/comments-edit.png
```

Replace `1` in the edit URL with a real question/comment ID from your system (e.g. from the ניהול הערות table). Same base URL for the first three; the full-page capture shows both sidebar filters and the table.

## Video watch page guide (guides/video-watch.html)

Base URL: `https://dev.video`. Use video ID **1253** (or your chosen watch ID). Page: `/watch/1253`.

```bash
node docs/screenshot.js "https://dev.video/watch/1253" docs/screenshot/watch-overview.png
node docs/screenshot.js "https://dev.video/watch/1253" docs/screenshot/watch-actions.png
node docs/screenshot.js "https://dev.video/watch/1253" docs/screenshot/watch-description.png
node docs/screenshot.js "https://dev.video/watch/1253" docs/screenshot/watch-sidebar.png
```

Full-page captures; you can use the same URL for all and crop later, or scroll to capture specific sections.

## Playlist guide (guides/playlist.html)

To update the images shown in **guides/playlist.html**, run the following. Base URL: `https://dev.video`. Page: `/playlist` (list) and `/playlist/instance` (new/edit form). Use selectors to open the delete-confirmation modal before capturing.

```bash
# רשימת פלייליסטים – מבט כללי
node docs/screenshot.js "https://dev.video/playlist" docs/screenshot/playlist-overview.png
# לחיצה על "מחיקה מרובה" – פתיחת חלון אישור מחיקה
node docs/screenshot.js "https://dev.video/playlist" docs/screenshot/playlist-delete-modal.png ".videos-admin-filters-actions a.modal-item-delete"
# טופס פלייליסט חדש (או עריכת פלייליסט)
node docs/screenshot.js "https://dev.video/playlist/instance" docs/screenshot/playlist-instance.png
```

- `playlist-overview.png`: full page of /playlist (sidebar + table).
- `playlist-delete-modal.png`: click sidebar "מחיקה מרובה" link, then capture modal.
- `playlist-instance.png`: form at /playlist/instance (title, available videos, videos in playlist). For an existing playlist use `/playlist/instance/{id}` (replace `{id}` with a real playlist ID).
- After running, refresh the guide at `guides/playlist.html` to see the updated images.

## User admin guide (guides/user-admin.html)

To update the images shown in **guides/user-admin.html**, run the following. Base URL: `https://dev.video`. Page: `/user/admin`. **Requires admin login.** Use a selector to open the delete-confirmation modal before capturing.

```bash
# רשימת משתמשים – מבט כללי
node docs/screenshot.js "https://dev.video/user/admin" docs/screenshot/user-admin-overview.png
# לחיצה על כפתור מחיקה בכרטיס משתמש – פתיחת חלון אישור מחיקה
node docs/screenshot.js "https://dev.video/user/admin" docs/screenshot/user-admin-delete-modal.png ".videos-admin-card-actions a.modal-item-delete"
```

- `user-admin-overview.png`: full page of /user/admin (sidebar filters + user cards).
- `user-admin-delete-modal.png`: click first delete button in a user card, then capture modal.
- After running, refresh the guide at `guides/user-admin.html` to see the updated images.

## Videos admin guide (guides/videos-admin.html)

To update the images shown in **guides/videos-admin.html**, run the following. Base URL: `https://dev.video`. Page: `/videos/admin`. **Requires admin login.** Use selectors to show the actions bar (after selecting a video) and the delete modal.

```bash
# רשימת סרטונים – מבט כללי
node docs/screenshot.js "https://dev.video/videos/admin" docs/screenshot/videos-admin-overview.png
# לחיצה על תיבת בחירה של סרטון – הצגת סרגל "סרטונים נבחרו"
node docs/screenshot.js "https://dev.video/videos/admin" docs/screenshot/videos-admin-actions-bar.png ".video-select-check"
# לחיצה על כפתור מחיקה בכרטיס – פתיחת חלון אישור מחיקה
node docs/screenshot.js "https://dev.video/videos/admin" docs/screenshot/videos-admin-delete-modal.png ".videos-admin-card-actions a.modal-item-delete"
```

- `videos-admin-overview.png`: full page of /videos/admin (sidebar filters + video cards).
- `videos-admin-actions-bar.png`: click first video checkbox so the "סרטונים נבחרו" bar with הורדה/מחיקה appears, then capture.
- `videos-admin-delete-modal.png`: click first delete button in a card, then capture modal.
- After running, refresh the guide at `guides/videos-admin.html` to see the updated images.

## Upload guide (guides/video-upload.html)

To update the images shown in **guides/video-upload.html**, run the following. Base URL: `https://dev.video`. Page: `/videos/upload`. No video ID. Use selectors to click media-type buttons so each source (מחשב, YouTube, Facebook) is captured after the relevant click.

```bash
# מבט כללי + סוג מקור (ברירת מחדל – מחשב)
node docs/screenshot.js "https://dev.video/videos/upload" docs/screenshot/upload-overview.png
node docs/screenshot.js "https://dev.video/videos/upload" docs/screenshot/upload-source.png
# אזור גרירה (מחשב נבחר)
node docs/screenshot.js "https://dev.video/videos/upload" docs/screenshot/upload-dropzone.png
# שדות כותרת, תיאור, פרטיות (גלילה או אותו דף)
node docs/screenshot.js "https://dev.video/videos/upload" docs/screenshot/upload-form.png
# לחיצה על YouTube – שדה URL של YouTube
node docs/screenshot.js "https://dev.video/videos/upload" docs/screenshot/upload-youtube.png "label[for=\"media_2\"]"
# לחיצה על Facebook – שדה URL של Facebook
node docs/screenshot.js "https://dev.video/videos/upload" docs/screenshot/upload-facebook.png "label[for=\"media_3\"]"
```

- `media_1` = מחשב (ברירת מחדל), `media_2` = YouTube, `media_3` = Facebook (see `Core::media()` / VIDEO_MEDIA_*).
- After running, refresh the guide at `guides/video-upload.html` to see the updated images.
