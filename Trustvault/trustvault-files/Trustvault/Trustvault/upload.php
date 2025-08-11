<?php
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo "✅ File uploaded: $target_file<br>";

    // Path to Windows Defender's MpCmdRun.exe
    $defender = "C:\\ProgramData\\Microsoft\\Windows Defender\\Platform\\4.18.23080.2006-0\\MpCmdRun.exe";

    // Defender scan command (quick scan on the uploaded file)
    $scan_command = "\"$defender\" -Scan -ScanType 3 -File \"$target_file\"";

    echo "🔍 Running scan command: $scan_command<br>";

    $output = shell_exec($scan_command);

    if ($output === null) {
        echo "❌ shell_exec failed. Check php.ini (enable exec/shell_exec) and file permissions.<br>";
    } else {
        echo "✅ Defender scan completed.<br>";
        echo "<pre>$output</pre>";

        // Check output for virus detection pattern
        if (strpos($output, "No threats") !== false || strpos($output, "found: 0") !== false) {
            echo "✅ No virus detected.";
        } else {
            echo "❌ Virus detected. File deleted.";
            unlink($target_file);
        }
    }
} else {
    echo "❌ File upload failed.";
}
?>
