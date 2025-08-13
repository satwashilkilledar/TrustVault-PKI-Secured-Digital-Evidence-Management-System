<?php
// Jenkins credentials and token
$jenkinsUser = "admin"; // your Jenkins username
$jenkinsToken = "11b22eafab1a661b6ca356d5a4625a024b"; // your Jenkins API token
$jobName = "evidence-deploy"; // Jenkins job name
$triggerToken = "mysecret123"; // token set in Jenkins job

// Jenkins URL
$url = "http://admin:11b22eafab1a661b6ca356d5a4625a024b@localhost:8080/job/evidence-deploy/build?token=mysecret123";

// Trigger the Jenkins job
$response = @file_get_contents($url);

if ($response === FALSE) {
    http_response_code(403);
    echo "❌ Failed to trigger CI/CD. Please check your credentials or permissions.";
} else {
    echo "✅ CI/CD triggered successfully!";
}
?>
