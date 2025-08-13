<?php
include 'db_connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Helper function for status color classes
function statusColorClass($status) {
    switch (strtolower($status)) {
        case 'approved': return 'text-green-700 font-semibold';
        case 'rejected': return 'text-red-700 font-semibold';
        case 'pending': return 'text-indigo-700 font-semibold';
        default: return 'text-gray-600';
    }
}
?>
<!DOCTYPE html>
<html lang="en" >
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
      border-radius: 0.5rem;
      box-shadow: inset 0 0 0 1px #dbeafe;
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

    <!-- User Evidence from `evidence` table -->
    <section
      class="bg-white rounded-2xl p-6 mb-10 border border-blue-300 shadow-lg"
      aria-label="User Evidence"
    >
      <h2
        class="text-2xl font-semibold mb-5 text-blue-700 flex items-center gap-3"
      >
        <i class="fas fa-users text-blue-600"></i> User Uploaded Evidence
      </h2>

      <div class="overflow-x-auto rounded-lg border border-blue-200">
        <table class="min-w-full text-sm text-left rounded-lg">
          <thead>
            <tr>
              <th class="p-3 font-semibold">Filename</th>
              <th class="p-3 font-semibold">Category</th>
              <th class="p-3 font-semibold">Comments</th>
              <th class="p-3 font-semibold">Upload Time</th>
              <th class="p-3 font-semibold">Hash</th>
              <th class="p-3 font-semibold">Download</th>
              <th class="p-3 font-semibold">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $stmtUser = $conn->prepare("
                SELECT *, IFNULL(approval_status, status) AS current_status
                FROM evidence
                WHERE username = ?
                ORDER BY upload_time DESC
              ");
              $stmtUser->bind_param('s', $username);
              $stmtUser->execute();
              $resultUser = $stmtUser->get_result();
              
              if ($resultUser && $resultUser->num_rows > 0) {
                while ($row = $resultUser->fetch_assoc()) {
                  $filename = htmlspecialchars($row['filename'] ?? 'N/A');
                  $category = htmlspecialchars($row['category'] ?? 'N/A');
                  $comments = nl2br(htmlspecialchars($row['comments'] ?? ''));
                  $upload_time = htmlspecialchars($row['upload_time'] ?? '-');
                  $hash_short = isset($row['hash_value']) ? substr($row['hash_value'], 0, 16) . '...' : '-';
                  $download_url = 'uploads/' . urlencode($row['filename'] ?? '');
                  $current_status = $row['current_status'] ?? 'Pending';
                  $status_class = statusColorClass($current_status);

                  echo "<tr class='border-b last:border-none hover:bg-blue-50 transition duration-100'>";
                  echo "<td class='p-3 truncate max-w-xs' title='$filename'>$filename</td>";
                  echo "<td class='p-3'>$category</td>";
                  echo "<td class='p-3 max-w-xs truncate'>$comments</td>";
                  echo "<td class='p-3'>$upload_time</td>";
                  echo "<td class='p-3 font-mono text-xs text-blue-700'>$hash_short</td>";
                  echo "<td class='p-3'><a href='$download_url' download class='text-blue-600 hover:underline flex items-center gap-1'><i class='fa fa-download'></i> Download</a></td>";
                  echo "<td class='p-3'><span class='$status_class'>" . htmlspecialchars($current_status) . "</span></td>";
                  echo "</tr>";
                }
              } else {
                  echo '<tr><td colspan="7" class="text-center p-4 text-gray-500">No evidence uploaded yet.</td></tr>';
              }
              $stmtUser->close();
            ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Investigator Evidence from `evidence1` table -->
    <section
      class="bg-white rounded-2xl p-6 mb-10 border border-blue-300 shadow-lg"
      aria-label="Investigator Evidence"
    >
      <h2
        class="text-2xl font-semibold mb-5 text-blue-700 flex items-center gap-3"
      >
        <i class="fas fa-user-secret text-blue-600"></i> Your Investigator Evidence
      </h2>

      <div class="overflow-x-auto rounded-lg border border-blue-200">
        <table class="min-w-full text-sm text-left rounded-lg">
          <thead>
            <tr>
              <th class="p-3 font-semibold">Filename</th>
              <th class="p-3 font-semibold">Category</th>
              <th class="p-3 font-semibold">Notes</th>
              <th class="p-3 font-semibold">Upload Time</th>
              <th class="p-3 font-semibold">Hash</th>
              <th class="p-3 font-semibold">Download</th>
              <th class="p-3 font-semibold">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $stmtInv = $conn->prepare("
    SELECT *, IFNULL(approval_status, status) AS current_status
    FROM evidence1
    ORDER BY upload_time DESC
");
$stmtInv->execute();

              $resultInv = $stmtInv->get_result();
              
              if ($resultInv && $resultInv->num_rows > 0) {
                while ($row = $resultInv->fetch_assoc()) {
                  $filename = htmlspecialchars($row['filename'] ?? 'N/A');
                  $category = htmlspecialchars($row['category'] ?? 'N/A');
                  $notes = nl2br(htmlspecialchars($row['notes'] ?? ''));
                  $upload_time = htmlspecialchars($row['upload_time'] ?? '-');
                  $hash_short = isset($row['hash_value']) ? substr($row['hash_value'], 0, 16) . '...' : '-';
                  $download_url = 'uploads/' . urlencode($row['filename'] ?? '');
                  $current_status = $row['current_status'] ?? 'Pending';
                  $status_class = statusColorClass($current_status);

                  echo "<tr class='border-b last:border-none hover:bg-blue-50 transition duration-100'>";
                  echo "<td class='p-3 truncate max-w-xs' title='$filename'>$filename</td>";
                  echo "<td class='p-3'>$category</td>";
                  echo "<td class='p-3 max-w-xs truncate'>$notes</td>";
                  echo "<td class='p-3'>$upload_time</td>";
                  echo "<td class='p-3 font-mono text-xs text-blue-700'>$hash_short</td>";
                  echo "<td class='p-3'><a href='$download_url' download class='text-blue-600 hover:underline flex items-center gap-1'><i class='fa fa-download'></i> Download</a></td>";
                  echo "<td class='p-3'><span class='$status_class'>" . htmlspecialchars($current_status) . "</span></td>";
                  echo "</tr>";
                }
              } else {
                  echo '<tr><td colspan="7" class="text-center p-4 text-gray-500">No investigator evidence found.</td></tr>';
              }
              $stmtInv->close();
            ?>
          </tbody>
        </table>
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
      <ul class="divide-y divide-blue-100 max-w-xl" id="custodyChainContainer">
        <?php
          // Fetch latest 5 chain of custody records from both tables for this user
          // You may adjust queries per your schema

          // Example: only evidence1 table chain of custody for investigator uploads
          $chain_stmt = $conn->prepare("
  SELECT *, IFNULL(approval_status, status) AS current_status 
  FROM evidence1 
  ORDER BY upload_time DESC 
");

          $chain_stmt->execute();
          $chain = $chain_stmt->get_result();

          if ($chain && $chain->num_rows > 0) {
            while ($row = $chain->fetch_assoc()) {
                $status = $row['current_status'] ?? '-';
                $statusClass = statusColorClass($status);
                echo "<li class='py-3 pl-3 border-l-4 border-blue-500 bg-blue-50 rounded mb-3'>";
                echo "<div class='text-sm font-semibold text-blue-800 flex items-center gap-2'>📁 <span>" . htmlspecialchars($row['filename']) . "</span></div>";
                echo "<div class='text-xs text-gray-600 flex flex-wrap gap-4 mt-1'>";
                echo "👤 <span>Uploaded by: " . htmlspecialchars($row['uploaded_by']) . "</span>";
                echo "🕒 <span>" . htmlspecialchars($row['upload_time']) . "</span>";
                echo "</div>";
                echo "<div class='text-xs text-gray-600 mt-1'>";
                echo "🔐 Hash: <code>" . substr(htmlspecialchars($row['hash_value']), 0, 20) . "...</code>";
                echo "<span class='ml-4'>🛠️ Status: <span class='" . $statusClass . "'>" . htmlspecialchars($status) . "</span></span>";
                echo "</div>";
                echo "</li>";
            }
          } else {
            echo "<li class='text-gray-500 p-4'>No chain of custody records found.</li>";
          }
          $chain_stmt->close();
        ?>
      </ul>
    </section>

  </div>

</body>
</html>
