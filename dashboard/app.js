// dashboard/app.js
const apiUrl = 'http://localhost:3001/api/mongo/etudiants';
const tableBody = document.querySelector('#studentsTable tbody');
const countCard = document.getElementById('countCard');

function render(list) {
  tableBody.innerHTML = '';
  list.forEach((s, i) => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${i+1}</td>
      <td>${s._id || ''}</td>
      <td>${s.num_etudiant || ''}</td>
      <td>${s.nom || ''}</td>
      <td>${s.prenom || ''}</td>
      <td>${s.email || ''}</td>
      <td>${s.filiere || ''}</td>
      <td>${s.annee_entree || ''}</td>
    `;
    tableBody.appendChild(row);
  });
  countCard.textContent = list.length;
}

// fetch initial
function fetchData() {
  axios.get(apiUrl).then(res => {
    window.render(res.data.students);
  }).catch(console.error);
}

// refresh button
const refreshBtn = document.getElementById('refreshBtn');
refreshBtn.addEventListener('click', fetchData);

fetchData();

// add form handling
const addForm = document.getElementById('addForm');
if (addForm) {
  addForm.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(addForm);
    axios.post('http://localhost:8000/add_student.php', formData)
      .then(() => {
        addForm.reset();
        fetchData();
      }).catch(err => {
        alert('Erreur ajout : ' + (err.response?.data?.error || err.message));
      });
  });
}
