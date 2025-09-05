let currentPage = 1;
const studentsPerPage = 5;

document.addEventListener('DOMContentLoaded', () => {
  // Session check
  const authUser = sessionStorage.getItem('authUser');
  if (!authUser) {
    window.location.href = '/login';
    return;
  }

  const { role } = JSON.parse(authUser);
  if (role !== 'admin') {
    document.getElementById('adminWarning').hidden = false;
    document.querySelector('.admin-container').style.display = 'none';
    return;
  }

  // Logout
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
      sessionStorage.clear();
      localStorage.clear();
      window.location.href = '/login';
    });
  }

  loadStudents(currentPage);
  loadStudentActivity();
});

// Load students (replace dummy with API)
async function loadStudents(page = 1) {
  try {
    const res = await fetch('/api/students');
    const students = await res.json();

    const tbody = document.getElementById('studentTable');
    tbody.innerHTML = students.map(student => `
      <tr>
        <td>${student.username}</td>
        <td>${new Date(student.created_at).toLocaleDateString()}</td>
        <td>${student.last_login ? new Date(student.last_login).toLocaleString() : 'Never'}</td>
        <td class="actions">
          <button onclick="editStudent('${student.id}')" class="action-btn edit"><i class="fas fa-edit"></i></button>
          <button onclick="confirmDelete('${student.id}')" class="action-btn delete"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
    `).join('');

    document.getElementById('pageInfo').textContent = `Page ${page}`;
  } catch (err) {
    showAdminError(err.message);
  }
}

// Edit student
async function editStudent(id) {
  const newUsername = prompt('Enter new username:');
  if (!newUsername) return;
  alert(`Would update student ${id} to username: ${newUsername}`);
}

// Delete student
function confirmDelete(id) {
  if (confirm('Are you sure you want to delete this student?')) {
    alert(`Would delete student with ID: ${id}`);
  }
}

// Pagination
function changePage(offset) {
  currentPage += offset;
  if (currentPage < 1) currentPage = 1;
  loadStudents(currentPage);
}

// Modal
function openModal() {
  document.getElementById('confirmModal').style.display = 'block';
}
function closeModal() {
  document.getElementById('confirmModal').style.display = 'none';
}
function confirmAction() {
  alert('System action confirmed!');
  closeModal();
}

// Error
function showAdminError(msg) {
  alert("Admin Error: " + msg);
}

// Create exam
document.getElementById('createExamForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const title = document.getElementById('examTitle').value.trim();
  const description = document.getElementById('examDesc').value.trim();
  const start_time = document.getElementById('examStartTime').value;
  const duration_minutes = parseInt(document.getElementById('examDuration').value, 10);

  if (!title || !start_time || !duration_minutes) {
    alert("Please fill all required fields!");
    return;
  }

  try {
    const res = await fetch('/api/exams', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ title, description, start_time, duration_minutes })
    });

    const data = await res.json();
    if (res.ok) {
      alert(data.message);
      document.getElementById('createExamForm').reset(); // Clear form
    } else {
      alert("Error: " + (data.error || "Something went wrong"));
    }
  } catch (err) {
    console.error("Exam creation failed:", err);
    alert("Failed to create exam. Check console for details.");
  }
});


// Export exam data
function exportData() {
  window.location.href = '/api/export';
}

// Monitor student activity (placeholder, needs backend support)
async function loadStudentActivity() {
  try {
    const res = await fetch('/api/students');
    const students = await res.json();

    let html = "<table><tr><th>Student</th><th>Logged In</th><th>Exam Attempted</th></tr>";
    students.forEach(s => {
      html += `<tr>
        <td>${s.username}</td>
        <td>${s.logged_in ? "✅" : "❌"}</td>
        <td>${s.attempted_exam ? "✅" : "❌"}</td>
      </tr>`;
    });
    html += "</table>";
    document.getElementById('studentActivity').innerHTML = html;
  } catch (err) {
    showAdminError("Activity load failed: " + err.message);
  }
}
