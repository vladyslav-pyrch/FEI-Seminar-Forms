<?php
$csv_data_dir = 'csv_data';
$user_data = 'data.csv';

if (!file_exists($csv_data_dir)) {
	mkdir($csv_data_dir, 0777, true);
}

$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$nickname = trim($_POST['nickname'] ?? '');
$email = trim($_POST['email'] ?? '');
$discord_nickname = trim($_POST['discord_nickname'] ?? '');
$school = trim($_POST['school'] ?? '');
$year_of_study = trim($_POST['year_of_study'] ?? '');

$nickname_pattern = '/^[a-zA-Z0-9._-]+$/';
$success_message = '';
$error_messages = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	goto render;
}
if (empty($firstname) || empty($lastname) || empty($nickname) || empty($email) || empty($discord_nickname) || empty($school) || empty($year_of_study)) {
	$error_messages[] = 'Všetky polia sú povinné.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$error_messages[] = 'Neplatná e-mailová adresa.';
}
if (!in_array($year_of_study, ['1', '2', '3', '4'])) {
	$error_messages[] = 'Neplatný ročník štúdia.';
}
if (preg_match('/\s/', $firstname)) {
	$error_messages[] = 'Meno nemôže obsahovať medzery.';
}
if (preg_match('/\s/', $lastname)) {
	$error_messages[] = 'Priezvisko nemôže obsahovať medzery.';
}
if (preg_match('/\s/', $discord_nickname)) {
	$error_messages[] = 'Discord nickname nemôže obsahovať medzery.';
}
if (!preg_match($nickname_pattern, $nickname)) {
	$error_messages[] = 'Prezývka môže obsahovať iba latinské písmená, číslice, podčiarkovník, bodku a spojovník.';
}
if (file_exists("$csv_data_dir/$user_data")) {
	$file = fopen("$csv_data_dir/$user_data", 'r');

	while (($data = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
		if (strcasecmp($data[2], $nickname) === 0) {
			$error_messages[] = 'Prezývka už existuje. Prosím, vyberte inú.';
		}
		if (strcasecmp($data[3], $email) === 0) {
			$error_messages[] = 'E-mail už existuje. Prosím, použite iný.';
		}
	}
	fclose($file);
}
if (!$error_messages) {
	$file = fopen("$csv_data_dir/$user_data", 'a');
	fputcsv($file, [$firstname, $lastname, $nickname, $discord_nickname, $email, $school, $year_of_study], ',', '"', '\\');
	fclose($file);

	$success_message = 'Registrácia úspešná!';
}

render:
?>
<!DOCTYPE html>
<html lang="sk">

<head>
	<meta charset="UTF-8">
	<title>FMIX - Registrácia</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="output.css" rel="stylesheet">
	<link rel="icon" href="images/FMIX2.png">
</head>

<body>
<header class="header_">
	<div class="h-full flex">
		<div class="h-full mr-2">
			<a href="./index.html"><img class="h-full object-contain" src="images/stu_fei_uim.png" alt="STU FEI logo"></a>
		</div>
	</div>

	<nav class="h-full ml-auto flex content-center overflow-y-auto text-nowrap">
		<a href="./index.html" class="link-button ">
			<span>Domov</span>
		</a>
		<a href="registration.php" class="link-button">
			<span>Registrácia</span>
		</a>
		<a href="submission.php" class="link-button">
			<span>Odovzdávanie</span>
		</a>
	</nav>
</header>

<main class="main_ ">
	<section class="section flex justify-center content-center">
		<div class="content max-md:w-full md:w-2/3 h-fit flex flex-col content-center">
			<div class="bg-purple-heart notification">
				<svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 12.32a2 2 0 000 2.83l8.47 8.47a2 2 0 002.83 0l8.47-8.47a2 2 0 000-2.83l-8.47-8.47a2 2 0 00-2.83 0z"></path>
					<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01"></path>
				</svg>
				<span>
					Zapamätajte si svoju prezývku, budete ju potrebovať, aby ste mohli odvzdať svoju prácu!
				</span>
			</div>

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

			<form method="post" action="" class="w-full form_">
				<h2 class="text-2xl font-bold mb-4 text-center">Registrácia</h2>

				<div class="form_block">
					<label for="firstname" class="form_label">Meno</label>
					<input id="firstname" name="firstname" type="text" placeholder="Meno" class="form_input"
					       value="<?= htmlspecialchars($firstname ?? '') ?>" required>
				</div>

				<div class="form_block">
					<label for="lastname" class="form_label">Priezvisko</label>
					<input id="lastname" name="lastname" type="text" placeholder="Priezvisko" class="form_input"
					       value="<?= htmlspecialchars($lastname ?? '') ?>" required>
				</div>

				<div class="form_block">
					<label for="nickname" class="form_label">Prezývka</label>
					<input id="nickname" name="nickname" type="text" placeholder="Prezývka" class="form_input"
					       value="<?= htmlspecialchars($nickname ?? '') ?>" required>
				</div>

				<div class="form_block">
					<label for="discord_nickname" class="form_label">Discord</label>
					<input id="discord_nickname" name="discord_nickname" type="text" placeholder="Discord" class="form_input"
					       value="<?= htmlspecialchars($discord_nickname ?? '') ?>" required>
				</div>

				<div class="form_block">
					<label for="email" class="form_label">Mail</label>
					<input id="email" name="email" type="text" placeholder="Mail" class="form_input"
					       value="<?= htmlspecialchars($email ?? '') ?>" required>
				</div>

				<div class="form_block">
					<label for="school" class="form_label">Škola</label>
					<input id="school" name="school" type="text" placeholder="Škola" class="form_input"
					       value="<?= htmlspecialchars($school ?? '') ?>" required>
				</div>

				<div class="form_block">
					<label for="year_of_study" class="form_label">Navštevovaný ročník</label>
					<select type="number" id="year_of_study" name="year_of_study" placeholder="Navštevovaný ročník"
					        class="form_input" min="1" max="4" value="<?= htmlspecialchars($year_of_study ?? '') ?>" required>
						<option value="">--Select--</option>
						<option value="1" <?= $year_of_study == 1 ? 'selected' : '' ?>>1</option>
						<option value="2" <?= $year_of_study == 2 ? 'selected' : '' ?>>2</option>
						<option value="3" <?= $year_of_study == 3 ? 'selected' : '' ?>>3</option>
						<option value="4" <?= $year_of_study == 4 ? 'selected' : '' ?>>4</option>
					</select>
				</div>

				<button type="submit" class="form_submit">Zaregistrovať sa</button>

			</form>

		</div>
	</section>
</main>

<footer class="footer_">
	<div class="content">
		<div class="part">
			<h1 class="text-xl">Address</h1>
			<address>
				Fakulta elektrotechniky a informatiky,<br>
				Ústav informatiky a matematiky,<br>
				Ilkovičova 3,<br>
				841 04 Bratislava,<br>
				Slovakia
			</address>
		</div>
		
		<div class="part">
			<h1 class="text-xl">Links</h1>
			<a class="flex justify-center" target="_blank" href="mailto:some@mail.com">
				<svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
					<path fill-rule="evenodd" clip-rule="evenodd"
					      d="M3.75 5.25L3 6V18L3.75 18.75H20.25L21 18V6L20.25 5.25H3.75ZM4.5 7.6955V17.25H19.5V7.69525L11.9999 14.5136L4.5 7.6955ZM18.3099 6.75H5.68986L11.9999 12.4864L18.3099 6.75Z"
					      fill="#080341"/>
				</svg>
				some@mail.com
			</a>
			<a class="flex justify-center" target="_blank" href="https://discord.gg/F2yjRggQ">
				<img class="h-6" src="images/discord_logo.png" alt="Discord"> Discord
			</a>
		</div>
		
		<div class="part">
			<h1 class="text-xl">Partners</h1>
			<img class="w-9" src="images/speai_logo.png" alt="SPEAI logo">
			<!--			<img class="w-9" src="images/tlis_logo.png" alt="TLIS logo">-->
		</div>
		
		<div class="part">
			© 2025 FMIX seminár web stránka.<br/> Všetky práva vyhradené.
		</div>
	</div>
</footer>

<script src="scripts.js"></script>
</body>

</html>