// mongo-sync-api/mysqlWatcher.js
const mysql = require('mysql2/promise');
const Etudiant = require('./mongoModel');
const { emitStudents } = require('./socket');

// configuration - adjust as needed
const mysqlConfig = {
  host: '127.0.0.1',
  user: 'root',
  password: '',
  database: 'universite'
};

let lastSync = new Date(0);

async function poll() {
  const conn = await mysql.createConnection(mysqlConfig);
  const [rows] = await conn.execute(
    'SELECT * FROM etudiants WHERE updated_at > ?',
    [lastSync]
  );
  if (rows.length > 0) {
    lastSync = new Date();
    for (const r of rows) {
      await Etudiant.findOneAndUpdate(
        { num_etudiant: r.num_etudiant },
        { ...r, synced_at: new Date() },
        { upsert: true, setDefaultsOnInsert: true }
      );
    }
    const all = await Etudiant.find().lean();
    emitStudents(all);
  }
  await conn.end();
}

function startPolling(interval = 5000) {
  setInterval(() => {
    poll().catch(console.error);
  }, interval);
}

module.exports = { startPolling };
