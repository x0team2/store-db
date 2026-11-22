<?php
declare(strict_types=1);

const WEBAPPS_FILE = __DIR__ . '/webapps.json';

$message = '';
$messageType = 'error';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fail(string $message): never
{
    throw new RuntimeException($message);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim((string)($_POST['name'] ?? ''));
        $category = trim((string)($_POST['category'] ?? ''));
        $version = trim((string)($_POST['version'] ?? '1.0.0.0.0'));
        $description = trim((string)($_POST['description'] ?? ''));
        $fileUrl = trim((string)($_POST['file_url'] ?? ''));

        if ($name === '') {
            fail('Please enter an app name.');
        }
        if ($version === '') {
            fail('Please enter an app version.');
        }
        if (!filter_var($fileUrl, FILTER_VALIDATE_URL) ||
            !in_array(strtolower((string)parse_url($fileUrl, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            fail('Enter a valid HTTP or HTTPS app file link.');
        }

        $json = @file_get_contents(WEBAPPS_FILE);
        if ($json === false) {
            fail('Could not read webapps.json. Check that the file exists and is readable.');
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !isset($data['categories'], $data['apps']) ||
            !is_array($data['categories']) || !is_array($data['apps'])) {
            fail('webapps.json must contain categories and apps arrays.');
        }

        $validCategories = array_column($data['categories'], 'id');
        if (!in_array($category, $validCategories, true)) {
            fail('Please select a valid category.');
        }

        // Download the package to a temporary file so large apps need not fit in memory.
        $tempFile = tempnam(sys_get_temp_dir(), 'webapp-');
        if ($tempFile === false) {
            fail('Could not create a temporary file to calculate the checksum.');
        }

        try {
            $output = fopen($tempFile, 'wb');
            if ($output === false) {
                fail('Could not open a temporary file for the download.');
            }

            $ch = curl_init($fileUrl);
            curl_setopt_array($ch, [
                CURLOPT_FILE => $output,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_USERAGENT => 'X0-Store-KaiOS/1.0',
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            ]);

            $downloaded = curl_exec($ch);
            $curlError = curl_error($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
            fclose($output);

            if ($downloaded === false || $status < 200 || $status >= 300) {
                fail('Could not download the app file. ' .
                    ($curlError !== '' ? $curlError : "HTTP status: {$status}"));
            }

            $sha256 = hash_file('sha256', $tempFile);
            if ($sha256 === false) {
                fail('Could not calculate the file SHA-256.');
            }
        } finally {
            if (is_file($tempFile)) {
                unlink($tempFile);
            }
        }

        $folder = preg_replace('/[^A-Za-z0-9_-]+/', '-', $name);
        $folder = trim((string)$folder, '-_');
        if ($folder === '') {
            fail('The app name cannot be used to create a folder name.');
        }

        $description = $description !== ''
            ? $description
            : "{$name} - X0 Store KaiOS for KaiOS 3.0+ and 4.0+ Devices";

        $app = [
            'folder' => $folder,
            'name' => $name,
            'version' => $version,
            'category' => $category,
            'description' => $description,
            'package_url' => $fileUrl,
            'sha256' => $sha256,
        ];

        // Update an existing entry with the same folder, otherwise add a new one.
        $updated = false;
        foreach ($data['apps'] as $index => $existingApp) {
            if (is_array($existingApp) && ($existingApp['folder'] ?? '') === $folder) {
                $data['apps'][$index] = $app;
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $data['apps'][] = $app;
        }

        $encoded = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ) . PHP_EOL;

        if (file_put_contents(WEBAPPS_FILE, $encoded, LOCK_EX) === false) {
            fail('Could not save webapps.json. Check the web server write permissions.');
        }

        $message = ($updated ? 'Updated' : 'Added') .
            " “{$name}”. SHA-256: {$sha256}";
        $messageType = 'success';
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
}

$categories = [];
if (is_file(WEBAPPS_FILE)) {
    $data = json_decode((string)file_get_contents(WEBAPPS_FILE), true);
    if (is_array($data) && isset($data['categories']) && is_array($data['categories'])) {
        $categories = $data['categories'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add app · X0 Store KaiOS</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            padding: 36px 16px;
            color: #f8fafc;
            font: 16px/1.5 system-ui, sans-serif;
            background: linear-gradient(135deg, #171044, #123b63 48%, #087e83);
        }
        main {
            width: min(100%, 620px);
            margin: auto;
            padding: 30px;
            border: 1px solid #ffffff35;
            border-radius: 22px;
            background: #101426e8;
            box-shadow: 0 24px 70px #0006;
        }
        h1 { margin: 0 0 6px; }
        .hint { margin: 0 0 24px; color: #cbd5e1; }
        label { display: block; margin: 16px 0 6px; font-weight: 650; }
        input, select, textarea {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #ffffff38;
            border-radius: 10px;
            color: #fff;
            background: #ffffff10;
            font: inherit;
        }
        select option { color: #111827; }
        textarea { min-height: 105px; resize: vertical; }
        input:focus, select:focus, textarea:focus {
            outline: 2px solid #38bdf8;
            border-color: transparent;
        }
        button {
            width: 100%;
            margin-top: 22px;
            padding: 13px;
            border: 0;
            border-radius: 11px;
            color: #fff;
            background: linear-gradient(100deg, #8b5cf6, #ec4899, #f97316);
            font: inherit;
            font-weight: 750;
            cursor: pointer;
        }
        button:hover { filter: brightness(1.1); }
        .message {
            margin: 18px 0;
            padding: 12px 14px;
            border-radius: 10px;
            overflow-wrap: anywhere;
        }
        .success { background: #04785766; border: 1px solid #34d399; }
        .error { background: #b91c1c55; border: 1px solid #f87171; }
        small { color: #cbd5e1; }
    </style>
</head>
<body>
<main>
    <h1>Add an app</h1>
    <p class="hint">Add an app package to the X0 Store KaiOS catalog.</p>

    <?php if ($message !== ''): ?>
        <div class="message <?= h($messageType) ?>"><?= h($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="name">App name</label>
        <input id="name" name="name" required maxlength="120" placeholder="App Name."
               value="<?= h((string)($_POST['name'] ?? '')) ?>">

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="">Choose a category</option>
            <?php foreach ($categories as $item): ?>
                <?php if (isset($item['id'], $item['name'])): ?>
                    <option value="<?= h((string)$item['id']) ?>"
                        <?= (($_POST['category'] ?? '') === $item['id']) ? 'selected' : '' ?>>
                        <?= h((string)$item['name']) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="version">Version</label>
        <input id="version" name="version" required
               value="<?= h((string)($_POST['version'] ?? '1.0.0.0.0')) ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description"
            placeholder="Defaults to: App name - X0 Store KaiOS for KaiOS 3.0+ and 4.0+ Devices"><?= h((string)($_POST['description'] ?? '')) ?></textarea>
        <small>Leave blank to use the default description.</small>

        <label for="file_url">App ZIP file link</label>
        <input id="file_url" name="file_url" type="url" required
               placeholder="https://example.com/app.zip"
               value="<?= h((string)($_POST['file_url'] ?? '')) ?>">
        <small>The server downloads this file to calculate its SHA-256 checksum.</small>

        <button type="submit">Calculate SHA-256 &amp; Submit</button>
    </form>
</main>
</body>
</html>