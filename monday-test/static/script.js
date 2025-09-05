// ========================
// Global State
// ========================
let noteReminders = JSON.parse(localStorage.getItem("noteReminders")) || [];
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

// ========================
// Init on Page Load
// ========================
document.addEventListener("DOMContentLoaded", () => {
  // Google API init
  initGoogleAPI();

  // Render UI sections
  renderMonthView();
  renderCalendar();
  loadNoteReminders();
  loadNotes();

  // Check reminders every minute
  setInterval(checkReminders, 60000);

  // Logout event
  const logoutBtn = document.querySelector(".logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      localStorage.clear();
      sessionStorage.clear();
      window.location.href = "/login";
    });
  }
});

// ========================
// Notes & Reminders
// ========================
function setNoteReminder() {
  const reminderType = document.getElementById("reminderType").value;
  const reminderTime = document.getElementById("noteReminderTime").value;
  const email = document.getElementById("reminderEmail").value;
  const notesArea = document.getElementById("notesArea");

  if (!reminderTime) {
    alert("Please select a reminder time");
    return;
  }

  const reminder = {
    id: Date.now(),
    type: reminderType,
    time: reminderTime,
    email,
    notePreview:
      notesArea.value.substring(0, 30) +
      (notesArea.value.length > 30 ? "..." : ""),
  };

  noteReminders.push(reminder);
  localStorage.setItem("noteReminders", JSON.stringify(noteReminders));

  renderNoteReminders();
  scheduleNotification(reminder);
  if (email) scheduleEmailNotification(reminder);

  alert(`Reminder set for ${new Date(reminderTime).toLocaleString()}`);
}

function renderNoteReminders() {
  const list = document.getElementById("noteRemindersList");
  list.innerHTML = noteReminders
    .map(
      (r) => `
    <div class="reminder-item" data-id="${r.id}">
      <span>⏰ ${new Date(r.time).toLocaleString()}</span>
      <p>${r.notePreview}</p>
      <button onclick="deleteReminder(${r.id})">×</button>
    </div>
  `
    )
    .join("");
}

function deleteReminder(id) {
  noteReminders = noteReminders.filter((r) => r.id !== id);
  localStorage.setItem("noteReminders", JSON.stringify(noteReminders));
  renderNoteReminders();
}

function checkReminders() {
  const now = new Date();
  noteReminders.forEach((r) => {
    if (new Date(r.time) <= now) {
      showNotification(`Notes Reminder: ${r.notePreview}`);
      deleteReminder(r.id);
    }
  });
}

function scheduleNotification(reminder) {
  const delay = new Date(reminder.time) - new Date();
  if (delay > 0) {
    setTimeout(() => {
      showNotification(`Reminder: ${reminder.notePreview}`);
      deleteReminder(reminder.id);
    }, delay);
  }
}

function showNotification(message) {
  if (!("Notification" in window)) {
    alert(message);
    return;
  }

  if (Notification.permission === "granted") {
    new Notification(message);
  } else if (Notification.permission !== "denied") {
    Notification.requestPermission().then((perm) => {
      if (perm === "granted") new Notification(message);
    });
  }

  // Always fallback
  alert(message);
}

function loadNoteReminders() {
  noteReminders = JSON.parse(localStorage.getItem("noteReminders")) || [];
  renderNoteReminders();
}

// ========================
// Notes
// ========================
function saveNotes() {
  const notesArea = document.getElementById("notesArea");
  localStorage.setItem("savedNotes", notesArea.value);
  alert("Notes saved successfully!");
}

function loadNotes() {
  const notesArea = document.getElementById("notesArea");
  const saved = localStorage.getItem("savedNotes");
  if (saved) notesArea.value = saved;
}

// ========================
// Calendar
// ========================
function renderCalendar() {
  const tests = JSON.parse(localStorage.getItem("tests")) || [];
  const calendar = document.getElementById("calendarView");

  const grouped = tests.reduce((acc, t) => {
    if (!acc[t.date]) acc[t.date] = [];
    acc[t.date].push(t);
    return acc;
  }, {});

  calendar.innerHTML = Object.entries(grouped)
    .map(
      ([date, tests]) => `
    <div class="calendar-day">
      <h3>${new Date(date).toLocaleDateString("en-US", {
        weekday: "long",
        month: "short",
        day: "numeric",
      })}</h3>
      ${tests
        .map(
          (t) => `
        <div class="calendar-event">
          <strong>${t.subject}</strong> at ${t.time}
        </div>`
        )
        .join("")}
    </div>`
    )
    .join("");
}

function renderMonthView() {
  const monthNames = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
  ];

  document.getElementById("currentMonth").textContent =
    `${monthNames[currentMonth]} ${currentYear}`;

  const firstDay = new Date(currentYear, currentMonth, 1);
  const lastDay = new Date(currentYear, currentMonth + 1, 0);
  const daysInMonth = lastDay.getDate();

  const tests = JSON.parse(localStorage.getItem("tests")) || [];
  const grouped = tests.reduce((acc, t) => {
    const d = new Date(t.date);
    if (d.getMonth() === currentMonth && d.getFullYear() === currentYear) {
      const day = d.getDate();
      if (!acc[day]) acc[day] = [];
      acc[day].push(t);
    }
    return acc;
  }, {});

  let html = '<div class="weekdays">' +
    ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"].map(d => `<div>${d}</div>`).join("") +
    '</div><div class="days">';

  for (let i = 0; i < firstDay.getDay(); i++) {
    html += '<div class="empty"></div>';
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const events = grouped[day] || [];
    html += `
      <div class="day ${events.length ? "has-events" : ""}">
        <div class="day-number">${day}</div>
        ${events
          .map(
            (e) => `
          <div class="calendar-event">
            <strong>${e.subject}</strong> at ${e.time}
          </div>`
          )
          .join("")}
      </div>`;
  }

  html += "</div>";
  document.getElementById("monthView").innerHTML = html;
}

function changeMonth(offset) {
  currentMonth += offset;
  if (currentMonth > 11) {
    currentMonth = 0;
    currentYear++;
  } else if (currentMonth < 0) {
    currentMonth = 11;
    currentYear--;
  }
  renderMonthView();
}

// ========================
// Google Calendar
// ========================
const CLIENT_ID = "YOUR_CLIENT_ID.apps.googleusercontent.com"; 
const API_KEY = "YOUR_API_KEY"; 
const DISCOVERY_DOC = "https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest";
const SCOPES = "https://www.googleapis.com/auth/calendar.events";

function initGoogleAPI() {
  if (!window.gapi) return;
  gapi.load("client:auth2", () => {
    gapi.client.init({
      apiKey: API_KEY,
      clientId: CLIENT_ID,
      discoveryDocs: [DISCOVERY_DOC],
      scope: SCOPES,
    });
  });
}

async function exportToGoogleCalendar() {
  try {
    await gapi.auth2.getAuthInstance().signIn();
    const tests = JSON.parse(localStorage.getItem("tests")) || [];

    for (const t of tests) {
      const event = {
        summary: `Test: ${t.subject}`,
        start: {
          dateTime: `${t.date}T${t.time}:00`,
          timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        },
        end: {
          dateTime: `${t.date}T${addHours(t.time, 1)}:00`,
          timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        },
      };

      await gapi.client.calendar.events.insert({
        calendarId: "primary",
        resource: event,
      });
    }

    alert("All tests exported to Google Calendar!");
  } catch (err) {
    console.error("Export failed:", err);
    alert("Failed to export to Google Calendar");
  }
}

function addHours(timeStr, hours) {
  const [h, m] = timeStr.split(":").map(Number);
  const date = new Date();
  date.setHours(h + hours, m);
  return date.toTimeString().slice(0, 5);
}

// ========================
// Email Notifications (Simulated)
// ========================
function scheduleEmailNotification(reminder) {
  console.log(
    `(Simulated) Would send email to ${reminder.email} at ${reminder.time}`
  );
}

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
  // Save selected exam in sessionStorage so exam.html can load it
  sessionStorage.setItem("currentExamId", examId);

  // Redirect to exam page
  window.location.href = `/exam/${examId}`;
}


// Call on load
document.addEventListener("DOMContentLoaded", () => {
  loadExams();
});
