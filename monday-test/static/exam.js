// Load exams for student
async function loadExams() {
  try {
    const res = await fetch("/api/exams");
    if (!res.ok) throw new Error("Failed to fetch exams");
    const exams = await res.json();

    const examList = document.getElementById("examList");
    if (exams.length === 0) {
      examList.innerHTML = "<p>No exams available.</p>";
      return;
    }

    examList.innerHTML = exams.map(e => `
      <div class="exam-card">
        <h3>${e.title}</h3>
        <p>${e.description || "No description"}</p>
        <p><strong>Start:</strong> ${new Date(e.start_time).toLocaleString()}</p>
        <p><strong>Duration:</strong> ${e.duration_minutes} minutes</p>
        <button onclick="startExam(${e.id})">Take Test</button>
      </div>
    `).join("");
  } catch (err) {
    console.error("Error loading exams:", err);
  }
}

// Example Take Test action
function startExam(examId) {
  alert("Starting exam " + examId);
  // 👉 Later: redirect to /exam/<id> page where questions will load
}

// Call on load
document.addEventListener("DOMContentLoaded", () => {
  loadExams();
});
