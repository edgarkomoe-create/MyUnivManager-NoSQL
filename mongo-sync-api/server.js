// mongo-sync-api/server.js
const express = require('express');
const mongoose = require('mongoose');
const { init: initSocket } = require('./socket');
const { startPolling } = require('./mysqlWatcher');
const Etudiant = require('./mongoModel');

const app = express();
app.use(express.json());

// CORS
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET,POST,OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type');
  if (req.method === 'OPTIONS') return res.end();
  next();
});

app.get('/api/mongo/etudiants', async (req, res) => {
  try {
    const list = await Etudiant.find().lean();
    res.json({ students: list });
  } catch (e) {
    res.status(500).json({ error: e.message });
  }
});

const PORT = process.env.PORT || 3001;

const server = app.listen(PORT, () => {
  console.log(`Mongo sync API listening on ${PORT}`);
});

initSocket(server);

mongoose.connect('mongodb://127.0.0.1:27017/myunivmanager', {
  useNewUrlParser: true,
  useUnifiedTopology: true,
}).then(() => {
  console.log('Connected to MongoDB');
  startPolling();
}).catch(err => {
  console.error('Mongo connection error', err);
});
