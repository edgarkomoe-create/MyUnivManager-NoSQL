# MyUnivManager

Hybrid university management demo with PHP/MySQL backend, Node.js/MongoDB synchronizer and a real-time dashboard.

## Overview

- **MySQL** is the source of truth, holding student records.
- **PHP API** exposes CRUD endpoints to frontend (writes only to MySQL).
- **Node.js service** polls MySQL, upserts into MongoDB, and emits socket.io events.
- **Dashboard** reads from MongoDB and updates in real time.

## Setup

1. **MySQL**
   - Import `schema.sql` into a local MySQL server (database `universite`).
   - Example: `mysql -u root < schema.sql`
2. **PHP Backend**
   - Serve `backend-php` folder with PHP (built-in server or Apache).
   - Example: `php -S localhost:8000 -t backend-php`.
3. **Mongo Sync API**
   - Navigate to `mongo-sync-api` and run `npm install`.
   - Start: `node server.js` (listens on 3001).
4. **Dashboard**
   - Open `dashboard/index.html` in browser or serve via simple server.
   - It will fetch data from Node API and connect socket.io.

## Demonstration

1. Use HTML form or Postman to POST to `backend-php/add_student.php`.
2. Confirm row in MySQL (`SELECT * FROM etudiants`).
3. Node watcher will detect change and upsert into Mongo.
4. Dashboard will receive `students_updated` event and refresh table.

## Architecture

```
[Browser Form] --> PHP API --> MySQL
                     |         |
                     |         v
                     |     Node.js Poller --> MongoDB
                     |                         |
                     v                         v
                 Dashboard <--- socket.io events
```

## Notes

- Frontend must never write directly to MongoDB.
- Synchronization is eventual (poll interval default 5s).
- Code is modular and commented for clarity.

