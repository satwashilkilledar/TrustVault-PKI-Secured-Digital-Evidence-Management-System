<?php
include 'db_connect.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Investigator Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
  <style>
    /* Scrollbar styling */
    ::-webkit-scrollbar {
      width: 8px;
      background: #e5e7eb;
    }
    ::-webkit-scrollbar-thumb {
      background: #93c5fd;
      border-radius: 6px;
    }

    /* Background Image */
    body {
      background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1470&q=80');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      color: #1e293b;
    }

    /* Overlay to dim the background for readability */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background: rgba(243, 244, 246, 0.85); /* light overlay */
      z-index: -1;
    }

    /* Container styling */
    .container {
      max-width: 7xl;
      margin: 0 auto;
      padding: 2rem 1rem 4rem;
      backdrop-filter: saturate(180%) blur(12px);
      background: rgba(255 255 255 / 0.85);
      border-radius: 1rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    /* Header */
    header {
      position: relative;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 3rem;
    }

    /* Fixed logout button top-right */
    .logout-btn {
      position: fixed;
      top: 1rem;
      right: 1rem;
      background-color: #ef4444; /* red-500 */
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      box-shadow: 0 2px 8px rgb(239 68 68 / 0.5);
      transition: background-color 0.3s ease;
      z-index: 100;
    }
    .logout-btn:hover {
      background-color: #b91c1c; /* red-700 */
    }

    /* Table tweaks */
    table {
      border-collapse: separate;
      border-spacing: 0 0.5rem;
      width: 100%;
    }
    thead th {
      background: #60a5fa; /* blue-400 */
      color: white;
      padding: 0.75rem 1rem;
      text-align: left;
      border-radius: 0.5rem;
    }
    tbody tr {
      background: #f9fafb;
      transition: background-color 0.15s ease;
    }
    tbody tr:hover {
      background-color: #dbeafe; /* blue-100 */
    }
    tbody td {
      padding: 0.75rem 1rem;
      vertical-align: middle;
    }
    .truncate {
      max-width: 20rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Buttons styling */
    button,
    input[type="submit"] {
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    /* Form input file styling */
    input[type='file']::file-selector-button {
      border: none;
      padding: 0.3rem 1rem;
      background-color: #bfdbfe;
      border-radius: 0.5rem;
      color: #1e40af;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    input[type='file']::file-selector-button:hover {
      background-color: #60a5fa;
      color: white;
    }
  </style>
</head>
<body>

  <!-- Fixed logout button -->
  <a href="logout.php" class="logout-btn" title="Logout">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>

  <div class="container">

    <!-- Header -->
    <header>
      <div class="flex items-center gap-4">
        <div
          class="inline-flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 text-blue-700 font-extrabold text-3xl shadow"
          ><i class="fa-solid fa-user-secret"></i
        ></div>
        <h1 class="text-4xl font-extrabold tracking-tight text-blue-900">
          Investigator Dashboard
        </h1>
      </div>
    </header>

    <!-- Upload Form -->
    <section
      class="bg-white rounded-2xl p-6 mb-10 border border-blue-100 shadow-lg"
      aria-label="Upload New Evidence"
    >
      <h2
        class="text-2xl font-semibold mb-5 text-blue-800 flex items-center gap-3"
      >
        <i class="fas fa-cloud-upload-alt text-blue-500"></i> Upload New Evidence
      </h2>
      <form
        action="upload_evidence.php"
        method="POST"
        enctype="multipart/form-data"
        class="grid md:grid-cols-3 gap-5"
      >
        <input
          type="file"
          name="evidenceFile"
          required
          class="border border-blue-200 rounded-lg p-3 w-full file:border file:mr-3 file:bg-gray-50 file:px-3 file:rounded file:text-gray-600 file:hover:bg-blue-50"
        />
        <select
          name="category"
          class="border border-blue-200 p-3 rounded-lg w-full text-gray-700 focus:ring-2 focus:ring-blue-500"
        >
          <option value="Image">Image</option>
          <option value="Video">Video</option>
          <option value="Log">Log</option>
          <option value="Other">Other</option>
        </select>
        <textarea
          name="notes"
          placeholder="Enter notes..."
          class="border border-blue-200 p-3 rounded-lg w-full md:col-span-1 col-span-3 resize-none"
          rows="3"
        ></textarea>
        <div class="col-span-3 md:col-span-1 flex gap-4 items-center">
          <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-800 text-white px-6 py-3 rounded-lg font-bold flex items-center gap-2 w-full md:w-auto transition-shadow shadow-md hover:shadow-lg"
          >
            <i class="fas fa-upload"></i> Upload
          </button>
        </div>
      </form>
    </section>

    <!-- Evidence Table -->
    <section
      class="bg-white rounded-2xl p-6 mb-10 border border-blue-100 shadow-lg"
      aria-label="Evidence Submitted by Users"
    >
      <h2
        class="text-2xl font-semibold mb-5 text-blue-800 flex items-center gap-3"
      >
        <i class="fas fa-folder-open text-blue-500"></i> Evidence Submitted by Users
      </h2>
      <div class="overflow-x-auto rounded-lg border border-blue-200">
        <table class="min-w-full text-sm text-left">
          <thead>
            <tr>
              <th class="p-3 font-semibold">Filename</th>
              <th class="p-3 font-semibold">Category</th>
              <th class="p-3 font-semibold">Notes</th>
              <th class="p-3 font-semibold">Uploaded By</th>
              <th class="p-3 font-semibold">Time</th>
              <th class="p-3 font-semibold">Hash</th>
              <th class="p-3 font-semibold">Download</th>
              <th class="p-3 font-semibold">Comment</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $result = $conn->query("SELECT * FROM evidence ORDER BY upload_time DESC");
            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo "<tr class='border-b last:border-none hover:bg-blue-50 transition duration-100'>";
                echo "<td class='p-3'>" . htmlspecialchars($row['filename'] ?? 'N/A') . "</td>";
                echo "<td class='p-3'>" . htmlspecialchars($row['category'] ?? 'N/A') . "</td>";
                echo "<td class='p-3 truncate max-w-xs'>" . nl2br(htmlspecialchars($row['notes'] ?? '')) . "</td>";
                echo "<td class='p-3'>" . htmlspecialchars($row['uploaded_by'] ?? $row['username'] ?? 'N/A') . "</td>";
                echo "<td class='p-3'>" . htmlspecialchars($row['upload_time'] ?? '-') . "</td>";
                echo "<td class='p-3 font-mono text-xs text-blue-700'>" . (isset($row['hash_value']) ? substr($row['hash_value'], 0, 16) . '...' : '-') . "</td>";
                echo "<td class='p-3'>
                        <a href='uploads/" . urlencode($row['filename'] ?? '') . "' download class='text-blue-600 hover:underline flex items-center gap-1'>
                          <i class='fa fa-download'></i> Download
                        </a>
                      </td>";
                echo "<td class='p-3'>
                        <form action='update_notes.php' method='POST' class='flex gap-2 items-center'>
                          <input type='hidden' name='evidence_id' value='" . ($row['id'] ?? '') . "' />
                          <input type='text' name='new_notes' placeholder='Add comment' class='border border-blue-200 p-1 rounded text-sm w-28' />
                          <button type='submit' class='text-green-700 font-bold hover:underline text-sm px-2 py-1 rounded'>
                            <i class='far fa-comment-dots'></i> Save
                          </button>
                        </form>
                      </td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='8' class='text-center p-4 text-gray-500'>No evidence uploaded yet.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Hash Verification -->
    <section
      class="bg-white rounded-2xl p-6 mb-10 border border-blue-100 shadow-lg"
      aria-label="Verify Evidence Integrity"
    >
      <h2
        class="text-2xl font-semibold mb-5 text-blue-800 flex items-center gap-3"
      >
        <i class="fa-solid fa-shield-halved text-blue-500"></i> Verify Evidence Integrity
      </h2>
      <div class="grid gap-4 max-w-md">
        <input
          type="file"
          id="verifyFile"
          class="border border-blue-200 p-3 rounded-lg w-full bg-blue-50 file:bg-blue-100"
          aria-label="Select file to verify"
        />
        <div>
          <label class="font-medium">SHA-256 Hash:</label>
          <div
            id="hashOutput"
            class="mt-1 p-2 bg-gray-50 font-mono text-xs rounded border border-gray-200"
            >No file selected</div
          >
        </div>
        <div>
          <label class="font-medium">Result:</label>
          <div
            id="comparisonResult"
            class="mt-1 p-2 bg-gray-100 text-sm rounded border border-gray-200"
            >-</div
          >
        </div>
        <button
          id="verifyBtn"
          class="bg-blue-600 hover:bg-blue-800 text-white font-bold px-6 py-3 rounded-lg w-max flex items-center gap-2 transition-shadow shadow-md hover:shadow-lg"
        >
          <i class="fa-solid fa-magnifying-glass"></i> Verify
        </button>
      </div>
    </section>

    <!-- Chain of Custody -->
    <section
      class="bg-white rounded-2xl p-6 mb-6 border border-blue-100 shadow-lg"
      aria-label="Chain of Custody"
    >
      <h2
        class="text-2xl font-semibold mb-5 text-blue-800 flex items-center gap-3"
      >
        <i class="fa-solid fa-scroll text-blue-500"></i> Chain of Custody
      </h2>
      <ul class="divide-y divide-blue-100 max-w-xl">
        <?php
        $chain = $conn->query("SELECT * FROM evidence1 ORDER BY upload_time DESC LIMIT 5");
        if ($chain && $chain->num_rows > 0) {
          while ($row = $chain->fetch_assoc()) {
            echo "<li class='py-3 pl-3 border-l-4 border-blue-500 bg-blue-50 rounded mb-3'>";
            echo "<div class='text-sm font-semibold text-blue-800 flex items-center gap-2'>📁 <span>" . htmlspecialchars($row['filename']) . "</span></div>";
            echo "<div class='text-xs text-gray-600 flex flex-wrap gap-4 mt-1'>";
            echo "👤 <span>Uploaded by: " . htmlspecialchars($row['uploaded_by']) . "</span>";
            echo "🕒 <span>" . htmlspecialchars($row['upload_time']) . "</span>";
            echo "</div>";
            echo "<div class='text-xs text-gray-600 mt-1'>";
            echo "🔐 Hash: <code>" . substr($row['hash_value'], 0, 20) . "...</code>";
            echo "<span class='ml-4'>🛠️ Status: <span class='font-semibold'>" . htmlspecialchars($row['status'] ?? '-') . "</span></span>";
            echo "</div>";
            echo "</li>";
          }
        } else {
          echo "<li class='text-gray-500 p-4'>No chain of custody records found.</li>";
        }
        ?>
      </ul>
    </section>
  </div>

  <script>
    document.getElementById('verifyBtn').addEventListener('click', () => {
      const file = document.getElementById('verifyFile').files[0];
      const hashOutput = document.getElementById('hashOutput');
      const comparisonResult = document.getElementById('comparisonResult');

      if (!file) {
        alert('Please select a file to verify.');
        return;
      }

      // Simulate hash calculation (replace with proper logic for real use)
      hashOutput.textContent = generateHash();

      const match = Math.random() > 0.5;
      comparisonResult.textContent = match ? '✅ Match: Verified' : '❌ Mismatch: Possible tampering';
      comparisonResult.className = match
        ? 'bg-green-100 p-2 rounded border border-green-200'
        : 'bg-red-100 p-2 rounded border border-red-200';
    });

    function generateHash() {
      const chars = 'abcdef0123456789';
      let result = '';
      for (let i = 0; i < 64; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      return result;
    }
  </script>
</body>
</html>
