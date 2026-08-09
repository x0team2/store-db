<?php
// Path to your data.json
$dataFile = 'data.json';

// Initialize variables
$appNameDefault = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $website = trim($_POST['website']);
    $download_url = trim($_POST['download_url']);
    $download_version = trim($_POST['download_version']);

    // Validate required fields
    if ($name && $category && $download_url) {
        // Create new app array
        $newApp = [
            "name" => $name,
            "description" => $description ?: "$name.zip | Online WebApp.",
            "icon" => "https://x0team2.github.io/default.png",
            "website" => $website ?: "https://x0.rf.gd/",
            "download" => [
                "url" => $download_url,
                "version" => $download_version ?: "1.0.0"
            ],
            "type" => "packaged",
            "license" => "Unknown",
            "author" => ["X0 TEAM"],
            "maintainer" => ["X0"],
            "has_ads" => false,
            "has_tracking" => false,
            "meta" => [
                "tags" => $category,
                "categories" => [$category]
            ],
            "slug" => strtolower(str_replace(' ', '-', $name)),
            "screenshots" => []
        ];

        // Append to apps array
        $data['apps'][] = $newApp;

        // Save back to JSON file
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $message = "App '$name' added successfully!";
    } else {
        $message = "Please fill in all required fields.";
    }
}

// If not POST or to set default app name based on URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['download_url'])) {
        $url = trim($_GET['download_url']);
        // Extract filename from URL
        $filename = basename(parse_url($url, PHP_URL_PATH));
        // Remove extension
        $appNameDefault = pathinfo($filename, PATHINFO_FILENAME);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Submit App</title>
<style>
  /* Your CSS styles as before */
  /* ... (same as previous code) ... */

/* Reset some default styles */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Arial', sans-serif;
  background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
}

h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #fff;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
  font-size: 2em;
}

form {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  max-width: 400px;
  width: 100%;
  transition: transform 0.3s, box-shadow 0.3s;
}

form:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.3);
}

label {
  display: block;
  margin-bottom: 6px;
  font-weight: bold;
  color: #333;
  font-size: 1em;
}

input[type="text"],
input[type="url"],
select {
  width: 100%;
  padding: 10px 15px;
  margin-bottom: 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s, box-shadow 0.3s;
}

input[type="text"]:focus,
input[type="url"]:focus,
select:focus {
  outline: none;
  border-color: #4A90E2;
  box-shadow: 0 0 8px rgba(74, 144, 226, 0.6);
}

input[type="text"]:hover,
input[type="url"]:hover,
select:hover {
  border-color: #4A90E2;
}

button {
  width: 100%;
  padding: 12px;
  background-color: #4A90E2;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

button:hover {
  background-color: #357ABD;
  transform: translateY(-2px);
}

.message {
  margin-top: 20px;
  text-align: center;
  font-weight: bold;
  color: #fff;
  font-size: 1.1em;
}

</style>
<script>
  // Optional: Auto-fill app name when URL is entered
  function fillAppName() {
    const urlField = document.getElementById('download_url');
    const nameField = document.getElementById('name');
    if (urlField.value) {
      const url = new URL(urlField.value);
      const filename = url.pathname.split('/').pop();
      const name = filename.substring(0, filename.lastIndexOf('.')) || filename;
      nameField.value = name;
    }
  }
</script>
</head>
<body>

<h2>Submit New App</h2>
<?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>
<form method="post" action="">
    <label for="name">App Name:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($appNameDefault); ?>" required>

    <label for="category">Category:</label>
    <select id="category" name="category" required>
      <option value="">Select Category</option>
      <option value="social">Social</option>
      <option value="education">Education</option>
      <option value="games">Games</option>
      <option value="utility">Utility</option>
      <option value="multimedia">Multimedia</option>
      <option value="ai">AI</option>
      <option value="nsfw">NSFW</option>
      <option value="root">Root</option>
    </select>

    <label for="description">Description (optional):</label>
    <input type="text" id="description" name="description">

    <label for="website">Website (optional):</label>
    <input type="url" id="website" name="website">

    <label for="download_url">Download URL:</label>
    <input type="url" id="download_url" name="download_url" onblur="fillAppName()">

    <label for="download_version">Download Version (optional):</label>
    <input type="text" id="download_version" name="download_version">

    <button type="submit">Submit App</button>
</form>

</body>
</html>