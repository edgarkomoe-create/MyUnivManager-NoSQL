// mongo-sync-api/socket.js
const { Server } = require('socket.io');
let io;

function init(server) {
  io = new Server(server, {
    cors: { origin: '*' }
  });
}

function emitStudents(list) {
  if (io) {
    io.emit('students_updated', list);
  }
}

module.exports = { init, emitStudents };
