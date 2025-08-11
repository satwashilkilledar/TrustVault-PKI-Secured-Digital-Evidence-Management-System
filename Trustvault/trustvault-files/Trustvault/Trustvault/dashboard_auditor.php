<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Auditor Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Inter', sans-serif;
      background-color: white;
      color: #1e293b; /* Tailwind slate-800 for dark text */
    }
    #app {
      position: relative;
      z-index: 10;
    }
    /* Scrollbar styling */
    ::-webkit-scrollbar {
      width: 8px;
    }
    ::-webkit-scrollbar-thumb {
      background-color: #3b82f6; /* Tailwind blue-500 */
      border-radius: 8px;
    }
  </style>
</head>
<body class="">

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
          <a
            href="http://localhost:3000"
            target="_blank"
            rel="noopener noreferrer"
            class="block hover:text-blue-900 transition duration-300"
            >📊 Monitoring (Grafana)</a
          >
        </nav>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-10 overflow-y-auto max-h-screen text-slate-900">

      <!-- Floating Logout Button top-right -->
      <div class="fixed top-6 right-6 z-50">
        <a
          href="logout.php"
          class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full shadow-lg font-semibold transition duration-300"
          title="Logout"
          ><i class="fas fa-sign-out-alt"></i> Logout</a
        >
      </div>

      <!-- Evidence Table -->
      <section id="evidence" class="mb-16">
        <h2 class="text-4xl font-bold mb-6 text-blue-700 drop-shadow-sm">Submitted Evidence</h2>
        <div
          class="overflow-auto bg-white rounded-xl shadow border border-blue-300"
        >
          <table class="min-w-full divide-y divide-gray-300 text-gray-700">
            <thead class="bg-blue-100">
              <tr>
                <th class="px-6 py-3 text-left font-semibold">Filename</th>
                <th class="px-6 py-3 text-left font-semibold">Category</th>
                <th class="px-6 py-3 text-left font-semibold">Uploaded By</th>
                <th class="px-6 py-3 text-left font-semibold">Upload Time</th>
                <th class="px-6 py-3 text-left font-semibold font-mono text-xs">Hash</th>
                <th class="px-6 py-3 text-left font-semibold">Download</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
              <?php
              $result = $conn->query("SELECT * FROM evidence1 ORDER BY upload_time DESC");
              if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  $filename = htmlspecialchars($row['filename']);
                  $category = htmlspecialchars($row['category']);
                  $uploaded_by = htmlspecialchars($row['uploaded_by']);
                  $upload_time = htmlspecialchars($row['upload_time']);
                  $hash_short = substr($row['hash_value'], 0, 16) . '...';
                  $download_url = 'uploads/' . urlencode($row['filename']);
                  echo <<<ROW
                  <tr class="hover:bg-blue-50 transition duration-300 cursor-pointer">
                    <td class="px-6 py-4 max-w-xs truncate" title="$filename">$filename</td>
                    <td class="px-6 py-4">$category</td>
                    <td class="px-6 py-4">$uploaded_by</td>
                    <td class="px-6 py-4">$upload_time</td>
                    <td class="px-6 py-4 font-mono text-xs">$hash_short</td>
                    <td class="px-6 py-4">
                      <a href="$download_url" download class="text-blue-600 hover:underline">Download</a>
                    </td>
                  </tr>
                  ROW;
                }
              } else {
                echo '<tr><td colspan="6" class="text-center py-6 text-gray-400">No evidence found.</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Verify Hash -->
      <section id="verify" class="mb-16">
        <h2 class="text-4xl font-bold mb-6 text-blue-700 drop-shadow-sm">Verify File Hash</h2>
        <form
          action="verify_hash.php"
          method="POST"
          enctype="multipart/form-data"
          class="bg-white rounded-xl p-8 shadow max-w-lg"
        >
          <input
            type="file"
            name="checkFile"
            required
            class="w-full p-3 rounded-md border border-blue-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <button
            type="submit"
            class="mt-6 w-full bg-blue-600 hover:bg-blue-700 transition duration-300 text-white font-semibold rounded-md py-3 flex justify-center items-center gap-2"
          >
            <i class="fas fa-shield-alt"></i> Verify
          </button>
        </form>
      </section>

      <!-- Chain of Custody -->
      <section id="custody" class="mb-16">
        <h2 class="text-4xl font-bold mb-6 text-blue-700 drop-shadow-sm">Chain of Custody</h2>
        <div
          class="bg-white rounded-xl p-6 shadow max-w-4xl overflow-auto"
          style="max-height: 350px;"
        >
          <?php include 'custody_chain.php'; ?>
        </div>
      </section>

      <!-- Access Logs -->
      <section id="logs" class="mb-16">
        <h2 class="text-4xl font-bold mb-6 text-blue-700 drop-shadow-sm">Access Logs</h2>
        <div
          class="overflow-auto bg-white rounded-xl shadow max-w-5xl border border-blue-300"
          style="max-height: 350px;"
        >
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
        <form
          action="generate_report.php"
          method="POST"
          class="bg-white rounded-xl p-8 shadow space-y-6"
        >
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block mb-2 text-blue-700 font-semibold">From Date</label>
              <input
                type="date"
                name="from_date"
                required
                class="w-full p-3 rounded-md border border-blue-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block mb-2 text-blue-700 font-semibold">To Date</label>
              <input
                type="date"
                name="to_date"
                required
                class="w-full p-3 rounded-md border border-blue-300 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block mb-2 text-blue-700 font-semibold">User Role</label>
              <select
                name="role"
                class="w-full p-3 rounded-md border border-blue-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option>All Roles</option>
                <option>Administrator</option>
                <option>Investigator</option>
                <option>Auditor</option>
                <option>Legal</option>
              </select>
            </div>
          </div>
          <button
            type="submit"
            class="w-full bg-green-600 hover:bg-green-700 transition duration-300 text-white font-semibold rounded-md py-3 flex justify-center items-center gap-2"
          >
            <i class="fas fa-file-csv"></i> Generate Report
          </button>
        </form>
      </section>

    </main>
  </div>

  <!-- Fetch Logs Script -->
  <script>
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
</body>
</html>
