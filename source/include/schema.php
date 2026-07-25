<?php
// Микроразметка schema.org. Собирается из тех же данных, что и вёрстка:
// организация с зоной обслуживания текущего города и, если блок FAQ на странице
// есть, вопросы-ответы. Плейсхолдеры {CITY...} подставятся вместе со всей страницей.
// Ждёт: $siteUrl, $phones, $email, $config; необязательно $schemaFaq (массив q/a).

// адрес одной строкой: «г. Москва, ул. ...» — город отдельно, остальное в улицу
$schemaAddr    = isset($config["address"]) ? $config["address"] : "";
$schemaParts   = explode(",", $schemaAddr, 2);
$schemaCity    = trim(preg_replace('/^\s*г\.?\s*/u', "", $schemaParts[0]));
$schemaStreet  = isset($schemaParts[1]) ? trim($schemaParts[1]) : "";

$schemaTels = array();
foreach ($phones as $p) {
	if (!empty($p["tel"])) { $schemaTels[] = $p["tel"]; }
}

$schemaBlocks = array();

$schemaBlocks[] = array(
	"@context"    => "https://schema.org",
	"@type"       => "LocalBusiness",
	"name"        => "МосКомДез",
	"description" => "Служба дезинфекции, дезинсекции и дератизации {CITY_IN}",
	"url"         => $siteUrl . "/",
	"image"       => $siteUrl . "/source/img/og-cover.jpg",
	"telephone"   => $schemaTels,
	"email"       => $email,
	"address"     => array(
		"@type"           => "PostalAddress",
		"streetAddress"   => $schemaStreet,
		"addressLocality" => $schemaCity,
		"addressCountry"  => "RU",
	),
	// зона обслуживания — город текущего поддомена, офис при этом остаётся московским
	"areaServed"  => array("@type" => "City", "name" => "{CITY}"),
	"priceRange"  => "от 1500 ₽",
);

// FAQPage: только там, где блок вопросов реально выводится
if (!empty($schemaFaq)) {
	$schemaQuestions = array();
	foreach ($schemaFaq as $item) {
		if (empty($item["q"]) || empty($item["a"])) { continue; }
		$schemaQuestions[] = array(
			"@type"          => "Question",
			"name"           => $item["q"],
			"acceptedAnswer" => array(
				"@type" => "Answer",
				// в ответах встречается <br> — в разметке нужен чистый текст
				"text"  => trim(strip_tags(str_replace(array("<br>", "<br/>", "<br />"), " ", $item["a"]))),
			),
		);
	}
	if ($schemaQuestions) {
		$schemaBlocks[] = array(
			"@context"   => "https://schema.org",
			"@type"      => "FAQPage",
			"mainEntity" => $schemaQuestions,
		);
	}
}

// JSON_HEX_TAG закрывает угловые скобки: случайный </script> в тексте не порвёт страницу
foreach ($schemaBlocks as $schemaBlock): ?>
	<script type="application/ld+json"><?= json_encode($schemaBlock, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
