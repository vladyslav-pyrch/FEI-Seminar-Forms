<?php
$csv_data_dir = 'csv_data';
$user_data = 'data.csv';
$submission_data = 'submission.csv';
$upload_dir = 'uploads';

$file_max_size = 10 * 1024 * 1024; // 10 MB
$allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

if (!file_exists($csv_data_dir)) {
	mkdir($csv_data_dir, 0777, true);
}
if (!file_exists($upload_dir)) {
	mkdir($upload_dir, 0777, true);
}

$nickname = trim($_POST['nickname'] ?? '');

$success_message = '';
$error_messages = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	goto render;
}

$file = $_FILES['file'] ?? null;
$nickname_exists = false;

if (empty($nickname)) {
	$error_messages[] = 'Prezývka je povinná.';
}
if (empty($file['name'])) {
	$error_messages[] = 'Súbor je povinný.';
	goto render;
}
if ($file['error'] !== UPLOAD_ERR_OK) {
	$error_messages[] = 'Chyba pri nahrávaní súboru.';
}
if ($file['size'] > $file_max_size) {
	$error_messages[] = 'Súbor musí mať menej ako 10MB.';
}
if (file_exists("$csv_data_dir/$user_data")) {
	$user_data_file = fopen("$csv_data_dir/$user_data", 'r');

	while (($data = fgetcsv($user_data_file, 0, ',', '"', '\\')) !== false) {
		if (strcasecmp($data[2], $nickname) === 0) {
			$nickname_exists = true;
			break;
		}
	}
	fclose($user_data_file);
}
if (!$nickname_exists) {
	$error_messages[] = 'Prezývka nie je zaregistrovaná.';
}
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($file_extension, $allowed_extensions)) {
	$error_messages[] = 'Povolené sú iba súbory jpg, jpeg, png a pdf.';
}
if ($error_messages) {
	goto render;
}
$target_path = "$upload_dir/" . uniqid() . ".$file_extension";

if (move_uploaded_file($file['tmp_name'], $target_path)) {
	$submission_data_file = fopen("$csv_data_dir/$submission_data", 'a');

	fputcsv($submission_data_file, [$nickname, $target_path, date('Y-m-d H:i:s')], ',', '"', '\\');
	fclose($submission_data_file);
	$success_message = 'Odovzdanie úlohy úspešné!';
}

render:
?>

<!DOCTYPE html>
<html lang="sk">

<head>
	<meta charset="UTF-8">
	<title>FMIX - Odovzdávanie zadania</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="output.css" rel="stylesheet">
	<link rel="icon" href="images/FMIX2.png">
</head>

<body>
<header class="header_">
	<div class="h-full flex">
		<div class="h-full mr-2">
			<a href="./index.html"><img class="h-full object-contain" src="images/UIM_CLR_horizontal.png" alt="STU FEI logo"></a>
		</div>
	</div>

	<nav class="h-full ml-auto flex content-center overflow-y-auto text-nowrap">
		<a href="./index.html" class="link-button ">
			<span>Domov</span>
		</a>
		<!--		<a href="./index.html#gallery" class="link-button" onclick="toggleOnSection('gallery_content')">-->
		<!--			<span>Galéria</span>-->
		<!--		</a>-->
		<!--		<a href="./index.html#preparing" class="link-button" onclick="toggleOnSection('preparing_content')">-->
		<!--			<span>Pripravujeme</span>-->
		<!--		</a>-->
		<a href="registration.php" class="link-button">
			<span>Registracia</span>
		</a>
		<a href="submission.php" class="link-button">
			<span>Odovzdávanie</span>
		</a>
	</nav>
</header>

<main class="main_">
	<section class="section flex justify-center content-center">
		<div class="content max-md:w-full md:w-2/3 h-fit flex flex-col content-center">

			<?php if ($success_message): ?>
				<div class="bg-cobalt notification">
					<svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
					</svg>
					<span><?= htmlspecialchars($success_message) ?></span>
				</div>
			<?php endif; ?>

			<?php if ($error_messages): ?>
				<?php foreach ($error_messages as $error_message): ?>
					<div class="bg-violet-eggplant notification">
						<svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-12.728 12.728m0-12.728l12.728 12.728"></path>
						</svg>
						<span><?= htmlspecialchars($error_message) ?></span>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<form method="post" action="" enctype="multipart/form-data" class="w-full form_">
				<h2 class="text-2xl font-bold mb-4 text-center">Odovzdávanie zadania</h2>
				<div class="form_block">
					<label for="nickname" class="form_label">Prezývka</label>
					<input id="nickname" name="nickname" type="text" placeholder="Prezývka" class="form_input"
					       value="<?= htmlspecialchars($nickname ?? '') ?>" required>
				</div>
				<div class="form_block">
					<label for="file" class="form_label">Nahrať súbor</label>
					<input id="file" name="file" type="file" placeholder="Nahrať súbor" class="form_input" required>
				</div>

				<button type="submit" class="form_submit">Odovzdať</button>
			</form>

		</div>
	</section>

</main>

	<footer class="footer_">
		<div class="content">
			<div class="w-full flex flex-row">
				<div class="md:flex-row max-md:flex-col flex">
					<div class="mr-2 ">
						<address>
							Fakulta elektrotechniky a informatiky,<br>
							Ústav informatiky a matematiky,<br>
							Ilkovičova 3,<br>
							841 04 Bratislava,<br>
							Slovakia
						</address>
					</div>
				</div>

				<div class="ml-auto flex flex-col">
					<div class="w-12 ml-auto h-full flex flex-col justify-start text-end">
						<div class="w-full">
							<img class="w-full" src="images/logoFarebne.png" alt="SPEAI logo">
						</div>
						<a target="_blank" href="https://discord.gg/F2yjRggQ">
							<img class="w-full" src="images/discord-logo.png" alt="Discord">
						</a>
					</div>

					<div>
						<a href="mailto:some@mail.com">some@mail.com</a>
					</div>
				</div>
			</div>

			<div class="flex justify-center items-center text-center">
		<span>
			© 2025 FMIX seminár web stránka. Všetky práva vyhradené.
		</span>
			</div>
		</div>
	</footer>

<script src="scripts.js"></script>
</body>

</html>