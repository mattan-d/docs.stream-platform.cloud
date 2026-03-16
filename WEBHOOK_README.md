# GitHub Webhook – משיכה אוטומטית

## 1. הגדרת הסוד (Secret)

- **אפשרות א':** משתנה סביבה  
  בהגדרות השרת (Apache/Nginx/PHP-FPM) הגדר:
  ```bash
  GITHUB_WEBHOOK_SECRET=הסיסמה-שבחרת
  ```
- **אפשרות ב':** ערוך ב-`webhook.php` את השורה:
  ```php
  $WEBHOOK_SECRET = 'הסיסמה-שבחרת';
  ```

## 2. הגדרת Webhook ב-GitHub

1. בריפו: **Settings** → **Webhooks** → **Add webhook**
2. **Payload URL:** `https://הדומיין-שלך/doc.trustgrade.cloud/webhook.php`
3. **Content type:** `application/json`
4. **Secret:** אותה סיסמה שהגדרת למעלה
5. **Which events:** Just the push event (או "Let me select" → Push events)
6. **Add webhook**

## 3. הרשאות לשרת

משתמש ה־web (למשל `www-data`) צריך הרשאה לריצת `git pull` בתיקייה:

```bash
# דוגמה: הבעלות על התיקייה
sudo chown -R www-data:www-data /path/to/doc.trustgrade.cloud

# או הוספת הרשאה ל־git (אם הפרויקט כבר קלון)
cd /path/to/doc.trustgrade.cloud
sudo -u www-data git pull
```

אם יש בעיית הרשאות, הלוג ייכתב ב־`webhook.log`.

## 4. לוג

כל קריאה ל־webhook ו־`git pull` נרשמים בקובץ `webhook.log` באותה תיקייה.

## 5. אבטחה (אופציונלי)

- מומלץ להגביל גישה ל־`webhook.php` לפי IP של GitHub (לחפש ברשת: GitHub webhook IP ranges).
- אפשר לשים את הסיסמה רק ב־`GITHUB_WEBHOOK_SECRET` ולא בקוד.
