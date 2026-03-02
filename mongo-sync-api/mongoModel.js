// mongo-sync-api/mongoModel.js
const mongoose = require('mongoose');

const EtudiantSchema = new mongoose.Schema({
  num_etudiant: { type: String, required: true, unique: true },
  nom: String,
  prenom: String,
  email: String,
  filiere: String,
  annee_entree: Number,
  created_at: Date,
  updated_at: Date,
  synced_at: Date
});

module.exports = mongoose.model('Etudiant', EtudiantSchema);
