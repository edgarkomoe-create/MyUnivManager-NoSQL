// dashboard/socket-client.js
const socket = io('http://localhost:3001');

socket.on('connect', () => {
  console.log('connected to socket');
});

socket.on('students_updated', (list) => {
  console.log('students_updated', list);
  if (window.render) {
    window.render(list);
  }
});
