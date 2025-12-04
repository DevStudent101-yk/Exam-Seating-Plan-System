/* UTIL */
function qs(s, r = document) { return r.querySelector(s); }
function getParams() { return Object.fromEntries(new URLSearchParams(location.search).entries()); }

/* ------------------------------
   STUDENT LOOKUP (index.html)
------------------------------ */
document.addEventListener("DOMContentLoaded", () => {
  const lookupForm = qs("#lookupForm");

  if (lookupForm) {
    lookupForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const cms = qs("#cms").value.trim();

      if (!cms) {
        alert("Please enter your CMS ID");
        return;
      }

      const res = await fetch(`http://localhost/EXAM-SEATING/backend/fetch-seat.php?cms=${encodeURIComponent(cms)}`);

      let text = await res.text();
      console.log("RAW RESPONSE:", text);

      let data;
      try {
        data = JSON.parse(text);
      } catch (err) {
        alert("Server returned invalid JSON. Check PHP errors in browser DevTools.");
        return;
      }

      if (!data.success) {
        alert(data.error);
        return;
      }

      // ✅ FIXED REDIRECT
      window.location.href =
  `seating.html?cms=${cms}`
  + `&name=${encodeURIComponent(data.student.full_name)}`
  + `&room=${data.student.room_no}`
  + `&row=${data.student.seat_row}`
  + `&col=${data.student.seat_col}`;

    });
  }
});



/* ------------------------------
   SEATING PAGE LOGIC (seating.html)
------------------------------ */

document.addEventListener("DOMContentLoaded", () => {
  // Check if we are on seating.html
  if (!document.querySelector("#gridContainer")) return;

  // Read URL parameters
  const url = new URL(window.location.href);

  const cms = url.searchParams.get("cms");
  const name = url.searchParams.get("name");
  const room = url.searchParams.get("room");
  const row = parseInt(url.searchParams.get("row"));
  const col = parseInt(url.searchParams.get("col"));

  // Fill student info panel
  qs("#studentName").innerText = name || "Unknown";
  qs("#studentCMS").innerText = cms || "—";
  qs("#studentRoom").innerText = room || "—";
  qs("#studentSeat").innerText = `Row ${row}, Col ${col}`;

  // Fetch room layout (max rows & columns)
  fetch(`http://localhost/EXAM-SEATING/backend/fetch-seat.php?cms=${cms}`)
    .then(res => res.json())
    .then(data => {
      const maxRow = data.room.max_row;
      const maxCol = data.room.max_col;

      buildGrid(maxRow, maxCol, row, col);
    })
    .catch(err => console.error("Grid error:", err));
});


// ---------------- GRID BUILDER ----------------
function buildGrid(maxRow, maxCol, studentRow, studentCol) {
  const grid = qs("#gridContainer");
  grid.innerHTML = "";

  grid.style.gridTemplateColumns = `repeat(${maxCol}, 48px)`;

  for (let r = 1; r <= maxRow; r++) {
    for (let c = 1; c <= maxCol; c++) {
      const seat = document.createElement("div");
      seat.classList.add("seat");

      // Highlight student's seat
      if (r === studentRow && c === studentCol) {
        seat.classList.add("active-seat");
        seat.innerText = "YOU";
      } else {
        seat.innerText = `${r}-${c}`;
      }

      grid.appendChild(seat);
    }
  }
}

/* ------------------------------
     ADMIN LOGIN
------------------------------ */
const loginForm = qs("#adminLoginForm");
if (loginForm) {
  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const fd = new FormData();
    fd.append("username", qs("#loginUsername").value.trim());
    fd.append("password", qs("#loginPassword").value.trim());

    const res = await fetch("/EXAM-SEATING/backend/admin-login.php", { method: "POST", body: fd });
    const data = await res.json();

    if (!data.success) {
      qs("#loginMsg").textContent = data.error;
      return;
    }

    location.href = "admin-dashboard.html";
  });
}

/* ------------------------------
   ADMIN REGISTER
------------------------------ */
const regForm = qs("#adminRegisterForm");
if (regForm) {
  regForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const p1 = qs("#regPass").value;
    const p2 = qs("#regPass2").value;

    if (p1 !== p2) {
      qs("#regMsg").textContent = "Passwords do not match.";
      return;
    }

    const res = await fetch("/EXAM-SEATING/backend/admin-register.php", {
      method: "POST",
      body: new FormData(regForm)
    });

    const data = await res.json();
    qs("#regMsg").textContent = data.success ? "Account created. You can login now." : data.message;
    qs("#regMsg").style.color = data.success ? "green" : "red";
  });
}

/* ------------------------------
     ADMIN DASHBOARD
------------------------------ */
const uploadBtn = qs("#uploadCsvBtn");
if (uploadBtn) {
  uploadBtn.addEventListener("click", async () => {
    const file = qs("#csvFile").files[0];
    if (!file) return alert("Choose CSV");

    const fd = new FormData();
    fd.append("csv_file", file);

    const res = await fetch("/EXAM-SEATING/backend/upload-csv.php", { method: "POST", body: fd });
    const data = await res.json();

    qs("#csvMsg").textContent =
      data.success
        ? `Uploaded ${data.inserted} rows, Skipped ${data.skipped}`
        : data.error;

    qs("#csvMsg").style.color = data.success ? "green" : "red";
  });
}

// Manual Student Add
const addForm = qs("#addStudentForm");
if (addForm) {
  addForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const res = await fetch("/EXAM-SEATING/backend/add-student.php", {
      method: "POST",
      body: new FormData(addForm)
    });

    const data = await res.json();
    const msg = qs("#manualMsg");

    msg.textContent = data.message;
    msg.style.color = data.success ? "green" : "red";

    if (data.success) addForm.reset();
  });
}
