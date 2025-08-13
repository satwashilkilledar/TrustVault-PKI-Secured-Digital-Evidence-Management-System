<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Auditor Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body, html { height: 100%; margin: 0; font-family: 'Inter', sans-serif; background-color: white; color: #1e293b; }
    #app { position: relative; z-index: 10; }
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-thumb { background-color: #3b82f6; border-radius: 8px; }
    .btn-custom { min-width: 80px; font-size: 0.85rem; padding: 0.25rem 0.75rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem; transition: all 0.3s ease; border: none; border-radius: 0.375rem; }
    .btn-approve { background-color: #16a34a; }
    .btn-approve:hover { background-color: #15803d; }
    .btn-reject { background-color: #dc2626; }
    .btn-reject:hover { background-color: #b91c1c; }
    /* --- Updated Evidence Table CSS --- */
    .styled-table {
      border-collapse: separate !important;
      border-spacing: 0;
      width: 100%;
      background: #fff;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 6px 22px rgba(60, 112, 220, 0.09);
    }
    .styled-table thead tr {
      background: linear-gradient(90deg, #dbeafe 0%, #60a5fa 100%);
      color: #174ea6;
      font-size: 1.05rem;
      letter-spacing: 0.02em;
    }
    .styled-table th,
    .styled-table td {
      padding: 14px 12px;
      vertical-align: middle !important;
    }
    .styled-table tbody tr {
      border-bottom: 1.5px solid #e5e7eb;
      transition: background 0.18s;
    }
    .styled-table tbody tr:hover {
      background: #e0f2fe;
    }
    .styled-table .status-pending { color: #f59e42; font-weight: 600; }
    .styled-table .status-approved { color: #16a34a; font-weight: 700;}
    .styled-table .status-rejected { color: #dc2626; font-weight: 700; }
    .styled-table .download-link { color: #2563eb; font-weight: 500; transition: color 0.2s;}
    .styled-table .download-link:hover { color: #1d4ed8; text-decoration: underline;}
    /* Chain of Custody timeline styles */
    .chain-timeline {
      border-left: 3px solid #2563eb;
      margin-left: 20px;
      margin-top: 18px;
      padding-left: 30px;
      position: relative;
    }
    .chain-timeline .event {
      margin-bottom: 1.5rem;
    }
    .chain-timeline .event-marker {
      position: absolute;
      left: -25px;
      background: #fff;
      border: 3px solid #2563eb;
      border-radius: 50%;
      width: 18px; height: 18px;
      top: 13px;
      z-index: 1;
    }
    .chain-timeline .event-info {
      margin-left: 10px;
    }
    .chain-timeline .event-time {
      color: #0ea5e9;
      font-size: 0.96rem;
      margin-bottom: 0.3rem;
    }
    .chain-timeline .event-user {
      color: #0f172a;
      font-weight: 500;
      font-size: 1.02rem;
    }
    .chain-timeline .event-action {
      color: #0369a1;
      font-size: 0.97rem;
    }
  </style>
</head>
<body>
<div id="app" class="flex min-h-screen">
  <!-- Sidebar -->
  <aside
    class="w-64 bg-blue-100 text-slate-800 p-6 flex flex-col justify-between rounded-tr-3xl rounded-br-3xl shadow-lg sticky top-0 h-screen"
  >
    <div>
      <h2 class="text-3xl font-extrabold text-blue-700 mb-8 select-none flex items-center gap-2">
        <i class="fas fa-user-shield"></i> Auditor Panel
      </h2>
      <nav class="space-y-5 text-lg font-medium">
        <a href="#evidence" class="block hover:text-blue-900 transition duration-300">📁 View Evidence</a>
        <a href="#verify" class="block hover:text-blue-900 transition duration-300">🛡️ Verify Hash</a>
        <a href="#custody" class="block hover:text-blue-900 transition duration-300">🔗 Chain of Custody</a>
        <a href="#logs" class="block hover:text-blue-900 transition duration-300">📋 Access Logs</a>
        <a href="#report" class="block hover:text-blue-900 transition duration-300">📄 Generate Report</a>
        <a href="http://localhost:3000" target="_blank" rel="noopener noreferrer"
          class="block hover:text-blue-900 transition duration-300">📊 Monitoring (Grafana)</a>
      </nav>
    </div>
  </aside>
  <!-- Main Content -->
  <main class="flex-1 p-10 overflow-y-auto max-h-screen">
    <div class="fixed top-6 right-6 z-50">
      <a href="logout.php" class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full shadow-lg font-semibold transition duration-300" title="Logout">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
    <!-- Evidence Table -->
    <section id="evidence" class="mb-16">
      <h2 class="text-4xl font-bold mb-6 text-blue-700 drop-shadow-sm">Submitted Evidence</h2>
      <div class="overflow-auto rounded-xl shadow border border-blue-300 bg-white">
        <table class="styled-table">
          <thead>
            <tr>
              <th>Filename</th>
              <th>Category</th>
              <th>Notes</th>
              <th>Uploaded By</th>
              <th>Upload Time</th>
              <th class="font-mono text-xs">Hash</th>
              <th>Approval Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $userQuery = "SELECT id, filename, category, uploaded_by, upload_time, hash_value, approval_status, notes, 'user' AS source FROM evidence";
            $investigatorQuery = "SELECT id, filename, category, uploaded_by, upload_time, hash_value, approval_status, notes, 'investigator' AS source FROM evidence1";
            $combinedQuery = "($userQuery) UNION ALL ($investigatorQuery) ORDER BY upload_time DESC";
            $result = $conn->query($combinedQuery);

            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $filename = htmlspecialchars($row['filename']);
                $category = htmlspecialchars($row['category']);
                $notes = htmlspecialchars($row['notes'] ?? '');
                $uploaded_by = htmlspecialchars($row['uploaded_by']);
                $upload_time = htmlspecialchars($row['upload_time']);
                $approval_status = htmlspecialchars($row['approval_status'] ?? 'Pending');
                $hash_short = substr($row['hash_value'], 0, 16) . '...';
                $download_url = 'uploads/' . urlencode($row['filename']);
                $source = htmlspecialchars($row['source']);
                $id = (int)$row['id'];
                $buttonsDisabled = ($approval_status === 'Approved' || $approval_status === 'Rejected') ? 'disabled' : '';
                $notes_display = strlen($notes) > 60 ? htmlspecialchars(substr($notes, 0, 57)) . '...' : $notes;
                // Status color
                $statusClass = "status-pending";
                if (strtolower($approval_status) === "approved") $statusClass = "status-approved";
                if (strtolower($approval_status) === "rejected") $statusClass = "status-rejected";
                echo <<<ROW
<tr>
  <td title="$filename">$filename</td>
  <td>$category</td>
  <td title="$notes">$notes_display</td>
  <td>$uploaded_by</td>
  <td>$upload_time</td>
  <td class="font-mono text-xs">$hash_short</td>
  <td class="$statusClass">$approval_status</td>
  <td class="flex items-center space-x-2">
    <button class="btn-custom btn-approve approve-btn" data-id="$id" data-source="$source" $buttonsDisabled>
      <i class="fas fa-check"></i> Approve
    </button>
    <button class="btn-custom btn-reject reject-btn" data-id="$id" data-source="$source" $buttonsDisabled>
      <i class="fas fa-times"></i> Reject
    </button>
    <a href="$download_url" download class="download-link ml-2">Download</a>
  </td>
</tr>
ROW;
              }
            } else {
              echo '<tr><td colspan="8" class="text-center py-6 text-gray-400">No evidence found.</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>
    <!-- Verify Hash -->
    <section id="verify" class="mb-16">
      <h2 class="text-4xl font-bold mb-6 text-blue-700 drop-shadow-sm">Verify File Hash</h2>
      <form action="verify_hash.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl p-8 shadow max-w-lg">
        <input type="file" name="checkFile" required class="w-full p-3 rounded-md border border-blue-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <button type="submit" class="mt-6 w-full btn-custom bg-blue-600 hover:bg-blue-700 transition duration-300 text-white font-semibold rounded-md py-3 flex justify-center items-center gap-2">
          <i class="fas fa-shield-alt"></i> Verify
        </button>
      </form>
    </section>
    <!-- Fixed Chain of Custody Section -->
    <section id="custody" class="mb-16">
      <h2 class="text-4xl font-bold mb-6 text-blue-700 drop-shadow-sm">Chain of Custody</h2>
      <div class="bg-white rounded-xl p-6 shadow max-w-4xl overflow-auto" style="max-height: 350px;">
        <?php
        // Demo placeholder data (replace with your actual DB chain-of-custody logic)
        $chain = [
          [
            'user' => 'investigator01',
            'action' => 'Uploaded evidence',
            'timestamp' => '2025-07-29 16:05:21',
            'hash' => '85ff48a1a8a9d...'
          ],
          [
            'user' => 'auditorA',
            'action' => 'Approved evidence',
            'timestamp' => '2025-07-29 16:17:55',
            'hash' => '85ff48a1a8a9d...'
          ],
          [
            'user' => 'admin',
            'action' => 'Updated category',
            'timestamp' => '2025-07-29 18:05:02',
            'hash' => '2199ad28b134d...'
          ],
        ];
        echo '<div class="chain-timeline">';
        foreach ($chain as $event) {
          echo '<div class="event">';
          echo '<div class="event-marker"></div>';
          echo '<div class="event-info">';
          echo '<div class="event-time"><i class="fa fa-clock"></i> '.$event['timestamp'].'</div>';
          echo '<div class="event-user"><i class="fa fa-user"></i> '.htmlspecialchars($event['user']).'</div>';
          echo '<div class="event-action"><i class="fa fa-edit"></i> '.$event['action'].'</div>';
          echo '<div class="event-hash text-gray-500" style="font-size:0.91rem;"><i class="fa fa-link"></i> '.$event['hash'].'</div>';
          echo '</div></div>';
        }
        echo '</div>';
        ?>
      </div>
    </section>
    <!-- Access Logs -->
    <section id="logs" class="mb-16">
      <h2 class="text-4xl font-bold mb-6 text-blue-700 drop-shadow-sm">Access Logs</h2>
      <div class="overflow-auto bg-white rounded-xl shadow max-w-5xl border border-blue-300" style="max-height: 350px;">
        <table class="min-w-full divide-y divide-gray-300 text-gray-700 text-sm">
          <thead class="bg-blue-100">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">Action</th>
              <th class="px-6 py-3 text-left font-semibold">Performed By</th>
              <th class="px-6 py-3 text-left font-semibold">Role</th>
              <th class="px-6 py-3 text-left font-semibold">Date/Time</th>
              <th class="px-6 py-3 text-left font-semibold">IP Address</th>
            </tr>
          </thead>
          <tbody id="logsBody" class="divide-y divide-gray-300"></tbody>
        </table>
      </div>
    </section>
    <!-- Generate Report -->
    <section id="report" class="mb-16 max-w-4xl">
      <h2 class="text-4xl font-bold mb-6 text-blue-700 drop-shadow-sm">Generate Audit Report</h2>
      <form action="generate_report.php" method="POST" class="bg-white rounded-xl p-8 shadow space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label class="block mb-2 text-blue-700 font-semibold">From Date</label>
            <input type="date" name="from_date" required class="w-full p-3 rounded-md border border-blue-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block mb-2 text-blue-700 font-semibold">To Date</label>
            <input type="date" name="to_date" required class="w-full p-3 rounded-md border border-blue-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block mb-2 text-blue-700 font-semibold">User Role</label>
            <select name="role" class="w-full p-3 rounded-md border border-blue-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option>All Roles</option>
              <option>Administrator</option>
              <option>Investigator</option>
              <option>Auditor</option>
              <option>Legal</option>
            </select>
          </div>
        </div>
        <button type="submit" class="w-full btn-custom bg-green-600 hover:bg-green-700 transition duration-300 text-white font-semibold rounded-md py-3 flex justify-center items-center gap-2">
          <i class="fas fa-file-csv"></i> Generate Report
        </button>
      </form>
    </section>
  </main>
</div>
<!-- Fetch Logs Script --><script>
async function fetchLogs() {
  try {
    const response = await fetch('get_logs.php');
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    const logsBody = document.getElementById('logsBody');
    logsBody.innerHTML = '';
    data.forEach((log) => {
      logsBody.innerHTML += `
        <tr class="hover:bg-blue-50 transition duration-300 cursor-default">
          <td class="px-6 py-3">${log.action}</td>
          <td class="px-6 py-3">${log.username}</td>
          <td class="px-6 py-3">${log.role}</td>
          <td class="px-6 py-3">${log.timestamp}</td>
          <td class="px-6 py-3">${log.ip_address}</td>
        </tr>`;
    });
  } catch (error) {
    console.error('Error fetching logs:', error);
  }
}
fetchLogs();
setInterval(fetchLogs, 5000);
</script>
<!-- Approve/Reject Script -->
<script>
async function handleApproval(id, source, action, button) {
  if (!confirm(`Are you sure you want to ${action} this file?`)) return;
  button.disabled = true;
  try {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('source', source);
    formData.append('action', action);
    const response = await fetch('approve_evidence.php', { method: 'POST', body: formData });
    const result = await response.json();
    if (response.ok) {
      alert(result.message);
      window.location.reload();
    } else {
      alert(`Error: ${result.message || response.statusText}`);
      button.disabled = false;
    }
  } catch (error) {
    alert('Network or server error occurred.');
    button.disabled = false;
  }
}
document.querySelectorAll('.approve-btn').forEach(btn => {
  btn.addEventListener('click', () => { handleApproval(btn.dataset.id, btn.dataset.source, 'approve', btn); });
});
document.querySelectorAll('.reject-btn').forEach(btn => {
  btn.addEventListener('click', () => { handleApproval(btn.dataset.id, btn.dataset.source, 'reject', btn); });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
