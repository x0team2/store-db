<?php
// Path to your data.json
$dataFile = 'data.json';

// Initialize variables
$appNameDefault = '';

// Load existing data
if (file_exists($dataFile)) {
    $jsonData = file_get_contents($dataFile);
    $data = json_decode($jsonData, true);
    if ($data === null) {
        // Handle JSON decode error
        $data = ['apps' => []];
    }
} else {
    // If file doesn't exist, initialize structure
    $data = ['apps' => []];
}

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

        // Append to existing apps array
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
 <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1"/>
  
<title>Submit Apps For X0 Store Client KaiOS</title>

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
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 10px;
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

/* Styles for text and URL inputs with distinct colors */
input[type="text"] {
  width: 100%;
  padding: 10px 15px;
  margin-bottom: 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s, box-shadow 0.3s;
  /* Default color */
  border-color: #ccc;
}

/* Focus state for text input */
input[type="text"]:focus {
  outline: none;
  border-color: #FF5733; /* Example: orange-red */
  box-shadow: 0 0 8px rgba(255, 87, 51, 0.6);
}

/* Hover state for text input */
input[type="text"]:hover {
  border-color: #FF8F33; /* Example: orange-yellow */
}

/* Styles for URL inputs with distinct colors */
input[type="url"] {
  width: 100%;
  padding: 10px 15px;
  margin-bottom: 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s, box-shadow 0.3s;
  /* Default color */
  border-color: #ccc;
}

/* Focus state for URL input */
input[type="url"]:focus {
  outline: none;
  border-color: #33A1FF; /* Example: blue */
  box-shadow: 0 0 8px rgba(51, 161, 255, 0.6);
}

/* Hover state for URL input */
input[type="url"]:hover {
  border-color: #66C2FF; /* Light blue */
}

/* Styles for select with distinct colors */
select {
  width: 100%;
  padding: 10px 15px;
  margin-bottom: 15px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s, box-shadow 0.3s, background-color 0.3s;
  /* Default color */
  border-color: #ccc;
}

/* Focus state for select */
select:focus {
  outline: none;
  border-color: #8E44AD; /* Example: purple */
  box-shadow: 0 0 8px rgba(142, 68, 173, 0.6);
  background-color: #f0e6ff;
}

/* Hover state for select */
select:hover {
  border-color: #BA55D3; /* Light purple */
  background-color: #f5e6ff;
}

/* Button styles with border and text effects */
button {
  width: 100%;
  padding: 12px;
  background-color: #5A9; /* Changed color */
  color: #fff;
  border: 2px solid #333; /* Added border */
  border-radius: 8px;
  font-size: 16px;
  font-weight: bold; /* Text effect: bold */
  letter-spacing: 1px; /* Text effect: spacing */
  text-shadow: 1px 1px 2px rgba(0,0,0,0.2); /* Text shadow for effect */
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s, box-shadow 0.3s, color 0.3s;
}

/* Hover state with new color and effects */
button:hover {
  background-color: #4682B4; /* Changed hover color */
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  color: #fff;
}

/* Focus state with new color and effects */
button:focus {
  background-color: #FF69B4; /* Changed focus color to pink */
  transform: translateY(-5px);
  box-shadow: 0 0 10px rgba(255, 105, 180, 0.5);
  outline: none; /* Remove default outline */
  color: #fff;
}

.message {
  margin-top: 20px;
  text-align: center;
  font-weight: bold;
  color: black;
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


<form method="post" action="">

  <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <label for="name">App Name:</label>
<input type="text" id="name" name="name" value="<?php echo htmlspecialchars($appNameDefault); ?>" required placeholder="Enter app name" />

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
<input type="text" id="description" name="description" placeholder="Enter description" />

<label for="website">Website (optional):</label>
<input type="url" id="website" name="website" placeholder="https://example.com" />

<label for="download_url">Download URL:</label>
<input type="url" id="download_url" name="download_url" onblur="fillAppName()" placeholder="https://example.com/app.zip" />

<label for="download_version">Download Version (optional):</label>
<input type="text" id="download_version" name="download_version" placeholder="e.g., 1.0.0" />

    <button type="submit">Submit App</button>
</form>


<div style="position: fixed; top: 10px; right: 10px; padding: 2px; box-shadow: 1px 1px 2px black; border-radius: 2px;"><a href="update-repo.php" style="text-shadow:  1px 1px 1px black;">🔄</a></div>


</body>
</html>