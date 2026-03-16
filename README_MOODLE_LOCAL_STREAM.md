## Stream Local Plugin

The **Stream** local plugin adds automatic handling of online meeting recordings (for example, Zoom, Microsoft Teams, Unicko) and embeds them directly into Moodle courses, according to your configuration.

This document explains what the plugin does and how to use it, with a special focus on Zoom integration and automatic embedding.

---

## הסבר קצר (בעברית): תוסף אינטגרציה ל‑Moodle

תוסף ה‑**Stream** למודל הוא תוסף מקומי (local plugin) שמטרתו **לחבר בין מערכות הוידאו החיצוניות** (כגון Zoom, Microsoft Teams, Unicko) לבין קורסי ה‑Moodle, ולבצע:

- **איתור אוטומטי של הקלטות אונליין** – התוסף ניגש לממשקי ה‑API של Zoom / Teams / Unicko, ומאתר הקלטות חדשות שהסתיימו ועברו עיבוד.
- **יצירה אוטומטית של משאב במודל** – לכל הקלטה שנמצאה, התוסף יוצר פעילות/משאב במודל (לרוב `Page` או פעילות מוגדרת אחרת) שמטמיע את ההקלטה בתוך הקורס.
- **שיוך לקורס ולחלק הנכון** – התוסף משייך את ההקלטה לקורס הנכון ולחלק (section) הנכון, ובתצורה טיפוסית ממקם את ההקלטה מתחת לפעילות המקורית (למשל פעילות Zoom בקורס).
- **מניעת כפילויות** – התוסף מזהה הקלטות כפולות (למשל ע״פ מזהה Zoom UUID) ודואג שלא ליצור מספר משאבים עבור אותה הקלטה.
- **התראות לסטודנטים (אופציונלי)** – אם מופעל, התוסף שולח הודעה למשתתפי הקורס כאשר הקלטה חדשה מוטמעת בקורס.

בקצרה: המרצה יוצר מפגש Zoom/Teams מתוך הקורס, מקליט לענן, ותוסף Stream דואג מאחורי הקלעים שההקלטה תופיע אוטומטית כמשאב נפרד בקורס – ללא צורך בהורדה/העלאה ידנית מצד המרצה.

להגדרות מפורטות, זרימת עבודה ומידע עבור מנהלי מערכת, מרצים וסטודנטים – ראו את ההמשך באנגלית.

---

## Main Features

- **Automatic recording discovery**
  - Connects to the configured video platform (Zoom / Teams / Unicko).
  - Periodically checks for new recordings that are ready.

- **Automatic embedding in Moodle courses**
  - Creates or reuses a Moodle activity (for example, Page / LTI / Zoom / msteams) to display the recording.
  - Places the recording in the correct course and section, based on the meeting data.
  - Can place the recording activity directly under the original meeting activity (for example, under the Zoom meeting).

- **De-duplication of recordings**
  - Identifies duplicate recordings (for example, same Zoom UUID) and marks them so they are not embedded multiple times.

- **Notifications to enrolled users (optional)**
  - Can send notifications to enrolled users in the course when a new recording is embedded.
  - Uses Moodle’s scheduled tasks and adhoc tasks to send messages.

---

## For Site Administrators

### Installation

1. Copy the plugin folder into `local/stream` in your Moodle codebase.
2. Visit *Site administration → Notifications* and follow the standard Moodle upgrade steps.
3. After installation, configure the plugin settings.

### Configuration

Go to:

- **Site administration → Plugins → Local plugins → Stream** (or similar link in the admin menu).

Key settings (names may vary slightly depending on your version):

- **Platform**
  - Choose the platform that provides the recordings:
    - `Zoom`
    - `Microsoft Teams`
    - `Unicko` (via LTI)

- **API / Integration settings**
  - For **Zoom**:
    - Enter the Zoom API credentials and required keys/secrets (JWT / OAuth / Server-to-server configuration, depending on your environment).
    - Make sure Zoom is allowed to provide recording data (cloud recordings).
  - For **Teams / Unicko**:
    - Configure the corresponding credentials / URLs / LTI settings.

- **Based grouping / duplicate handling**
  - Optionally group by unique meeting identifier (for example, Zoom UUID) to avoid embedding multiple versions of the same recording.

- **Embed order**
  - Controls whether the recording activity is moved under the original meeting activity (for example, the Zoom activity) or simply placed in the same section.

- **Scheduled task**
  - Ensure the scheduled task `local_stream\task\embed` is **enabled**:
    - Go to *Site administration → Server → Scheduled tasks*.
    - Find **Embedded recordings (local_stream)**.
    - Verify it is enabled and configured to run at appropriate intervals (for example, every 5–15 minutes, or hourly, depending on your needs).

### Zoom Integration – High-Level Flow

1. A teacher creates a **Zoom meeting** in a Moodle course (using the Zoom activity module).
2. The meeting is held and Zoom creates a **cloud recording**.
3. The Stream plugin’s scheduled task runs and:
   - Fetches the list of recordings from Zoom.
   - Matches each recording to the corresponding Moodle course and Zoom activity.
4. For each new recording:
   - The plugin creates a **Page (or other configured activity type)** containing the embedded recording link or player.
   - The plugin moves the new Page into the same course and section as the original Zoom activity.
   - Optionally, it moves the Page **directly under** the Zoom activity in the course section.
5. If notifications are enabled:
   - Enrolled users in the course receive a notification that a new recording is available.

---

## For Teachers / Course Managers

### What You Need To Do

In most cases, teachers do **not** need to manage the plugin directly. Typical workflow:

1. **Create a Zoom meeting in your course**
   - Add a new **Zoom** activity in the required course.
   - Configure time, topic, and other options as usual.

2. **Hold the meeting and record it in Zoom**
   - Make sure the meeting is set to be recorded to the **cloud**.

3. **Wait for automatic embedding**
   - After Zoom finishes processing the recording, the Stream plugin’s scheduled task will:
     - Detect the new recording.
     - Automatically create an activity in your Moodle course for the recording.
     - Place it in the same section as the original Zoom activity, and (depending on configuration) directly below it.

4. **Check your course page**
   - You should see a new resource/activity with the recording.
   - Students will see it in the course and may receive a notification, depending on the site configuration.

### Notes for Teachers

- You do **not** need to manually download / upload recordings from Zoom.
- If a recording does not appear:
  - Confirm that the meeting was recorded to the **cloud**.
  - Check with the site administrator that:
    - The plugin is installed and configured correctly.
    - The scheduled task `local_stream\task\embed` is running without errors.

---

## For Regular Users (Students)

- After your teacher records a Zoom session and the recording is ready:
  - The recording will appear automatically in the relevant course.
  - You usually will find it:
    - In the same section as the Zoom meeting, often directly under the meeting link.
- You may receive a notification in Moodle when a new recording is added (depends on site configuration).
- Just click the new recording activity to watch the session.

---

## Troubleshooting (Summary)

- **Recording not embedded**
  - Check that the scheduled task *Embedded recordings (local_stream)* is enabled and running.
  - For Zoom, verify that:
    - Cloud recordings are enabled.
    - API credentials are correct.

- **Recording in wrong course or section**
  - Confirm that the meeting topic / course mapping rules are correct (for Teams / Unicko flows).
  - Check plugin settings for how sections and order are chosen.

- **Duplicate recordings**
  - Make sure the “based grouping” setting is correctly configured so that multiple versions of the same Zoom recording are not embedded multiple times.

If problems continue, enable debugging in Moodle, run the `local_stream\task\embed` scheduled task manually (via CLI or scheduled tasks page), and review the log output for error messages.

# Stream Integration Moodle Plugin