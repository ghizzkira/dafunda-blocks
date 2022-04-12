<?php

function generateISODurationCode($rawInput)
{
	if (is_array($rawInput)) {
		$inputArr = $rawInput;
	} else {
		$inputArr = array_fill(0, 7, 0);

		$inputArr[3] = ($rawInput - ($rawInput % 86400)) / 86400;
		$inputArr[4] = (($rawInput - ($rawInput % 3600)) / 3600) % 24;
		$inputArr[5] = (($rawInput - ($rawInput % 60)) / 60) % 60;
		$inputArr[6] = $rawInput % 60;
	}

	$tIsAbsent = true;
	$output = "P";
	$unitLetters = ["Y", "M", "W", "D", "H", "M", "S"];

	if (
		$inputArr[2] > 0 &&
		count(
			array_filter($inputArr, function ($item) {
				return $item > 0;
			})
		) > 1
	) {
		$inputArr[3] += $inputArr[2] * 7;
		$inputArr[2] = 0;
	}

	foreach ($inputArr as $i => $t) {
		if ($i > 3 && $tIsAbsent) {
			$output .= "T";
			$tIsAbsent = false;
		}
		if ($t > 0) {
			$output .= round($t) . $unitLetters[$i]; //decimal values for time aren't recognized
		}
	}
	return $output;
}

function dbe_convert_to_paragraphs($string)
{
	if ($string === "") {
		return "";
	} else {
		$string = explode("<br>", $string);
		$string = array_map(function ($p) {
			return "<p>" . $p . "</p>";
		}, $string);
		return implode("", $string);
	}
}

function dbe_render_how_to_block($attributes)
{
	extract($attributes);

	$header = "";

	$timeUnits = ["Detik", "Menit", "Jam", "Hari", "Minggu", "Bulan", "Tahun"];

	$suppliesCode = '"supply": [';
	if ($advancedMode && $includeSuppliesList) {
		$header .=
			"<" .
			$secondLevelTag .
			">" .
			$suppliesIntro .
			"</" .
			$secondLevelTag .
			">";
		if (isset($supplies) && count($supplies) > 0) {
			$header .= $suppliesListStyle === "ordered" ? "<ol" : "<ul";
			$header .= ' class="howto-supplies-list">';
			foreach ($supplies as $i => $s) {
				$header .=
					'<li class="ml-5">' .
					$s["name"] .
					($s["imageURL"] === ""
						? ""
						: '<br><img src="' . $s["imageURL"] . '"/>') .
					"</li>";
				if ($i > 0) {
					$suppliesCode .= ",";
				}
				$suppliesCode .=
					'{"@type": "HowToSupply", "name": "' .
					str_replace("\'", "'", wp_filter_nohtml_kses($s["name"])) .
					'"' .
					($s["imageURL"] === ""
						? ""
						: ',"image": "' . $s["imageURL"] . '"') .
					"}";
			}
			$header .= $suppliesListStyle === "ordered" ? "</ol>" : "</ul>";
		}
	}
	$suppliesCode .= "]";

	$toolsCode = '"tool": [';

	if ($advancedMode && $includeToolsList) {
		$header .=
			"<" .
			$secondLevelTag .
			">" .
			$toolsIntro .
			"</" .
			$secondLevelTag .
			">";
		if (isset($tools) && count($tools) > 0) {
			$header .= $toolsListStyle === "ordered" ? "<ol" : "<ul";
			$header .= ' class="howto-tools-list">';
			foreach ($tools as $i => $t) {
				$header .=
					"<li>" .
					$t["name"] .
					($t["imageURL"] === ""
						? ""
						: '<br><img src="' . $t["imageURL"] . '"/>') .
					"</li>";
				if ($i > 0) {
					$toolsCode .= ",";
				}
				$toolsCode .=
					'{"@type": "HowToTool", "name": "' .
					str_replace("\'", "'", wp_filter_nohtml_kses($t["name"])) .
					'"' .
					($t["imageURL"] === ""
						? ""
						: ',"image": "' . $t["imageURL"] . '"') .
					"}";
			}
			$header .= $toolsListStyle === "ordered" ? "</ol>" : "</ul>";
		}
	}
	$toolsCode .= "]";

	$costDisplay = $showUnitFirst
		? $costCurrency . " " . $cost
		: $cost . " " . $costCurrency;

	$timeDisplay = "<div>";
	// $timeDisplay = "<div>
	// <" .
	// $secondLevelTag .
	// " class=\"mb-0\">" .
	// $timeIntro .
	// "</" .
	// $secondLevelTag .
	// ">";

	$totalTimeDisplay = "";

	foreach ($totalTime as $i => $t) {
		if ($t > 0) {
			$totalTimeDisplay .=
				$t . " " . __($timeUnits[6 - $i] . ($t > 1 ? "" : "")) . " ";
		}
	}

	$timeDisplay .=
		"<p class=\"mb-0\"> Sekitar " . $totalTimeDisplay . "</div>";

	$ISOTotalTime = generateISODurationCode($totalTime);

	$stepsDisplay = "";
	$stepsCode = PHP_EOL . '"step": [';

	if ($useSections) {
		$stepsDisplay =
			($sectionListStyle === "ordered" ? "<ol" : "<ul") .
			' class="ml-4">';
		foreach ($section as $i => $s) {
			$stepsDisplay .=
				'<li class="howto-section"><' .
				$secondLevelTag .
				">" .
				$s["sectionName"] .
				"</" .
				$secondLevelTag .
				">" .
				($sectionListStyle === "ordered" ? "<ol" : "<ul") .
				' class="howto-step-display">';
			$stepsCode .=
				'{"@type": "HowToSection",' .
				PHP_EOL .
				'"name": "' .
				str_replace(
					"\'",
					"'",
					wp_filter_nohtml_kses($s["sectionName"])
				) .
				'",' .
				PHP_EOL .
				'"itemListElement": [' .
				PHP_EOL;
			//get each step inside section

			foreach ($s["steps"] as $j => $step) {
				$stepsCode .=
					'{"@type": "HowToStep",' .
					PHP_EOL .
					'"name": "' .
					str_replace(
						"\'",
						"'",
						wp_filter_nohtml_kses($step["title"])
					) .
					'",' .
					PHP_EOL .
					($advancedMode
						? '"url": "' .
							get_permalink() .
							"#" .
							$step["anchor"] .
							'",' .
							PHP_EOL .
							($step["hasVideoClip"]
								? '"video":{"@id": "' . $step["anchor"] . '"},'
								: "") .
							PHP_EOL
						: "") .
					'"image": "' .
					$step["stepPic"]["url"] .
					'",' .
					PHP_EOL .
					'"itemListElement" :[{' .
					PHP_EOL;

				$stepsDisplay .=
					'<li class="howto-step"><' .
					$thirdLevelTag .
					' id="' .
					$step["anchor"] .
					'">' .
					$step["title"] .
					"</" .
					$thirdLevelTag .
					">" .
					($step["stepPic"]["url"] !== ""
						? ($step["stepPic"]["caption"] === ""
								? ""
								: '<figure class="w-full">') .
							'<img class="howto-step-image " src="' .
							$step["stepPic"]["url"] .
							'">' .
							($step["stepPic"]["caption"] === ""
								? ""
								: "<figcaption>" .
									$step["stepPic"]["caption"] .
									"</figcaption></figure>")
						: "") .
					dbe_convert_to_paragraphs($step["direction"]) .
					PHP_EOL;

				$stepsCode .=
					'"@type": "HowToDirection",' .
					PHP_EOL .
					'"text": "' .
					($step["title"] === "" || !$advancedMode
						? ""
						: str_replace(
								"\'",
								"'",
								wp_filter_nohtml_kses($step["title"])
							) . " ") .
					str_replace(
						"\'",
						"'",
						wp_filter_nohtml_kses($step["direction"])
					) .
					'"}' .
					PHP_EOL;

				if ($step["tip"] !== "") {
					$stepsDisplay .= dbe_convert_to_paragraphs($step["tip"]);
					$stepsCode .=
						',{"@type": "HowToTip",' .
						PHP_EOL .
						'"text": "' .
						str_replace(
							"\'",
							"'",
							wp_filter_nohtml_kses($step["tip"])
						) .
						'"}' .
						PHP_EOL;
				}

				$stepsCode .= "]}" . PHP_EOL;
				$stepsDisplay .= "</li>";
				if ($j < count($s["steps"]) - 1) {
					$stepsCode .= ",";
				}
			}

			$stepsDisplay .=
				($sectionListStyle === "ordered" ? "</ol>" : "</ul>") . "</li>"; //close section div
			$stepsCode .= "]}";
			if ($i < count($section) - 1) {
				$stepsCode .= ",";
			}
		}
	} else {
		$stepsDisplay .=
			($sectionListStyle === "ordered" ? "<ol" : "<ul") .
			' class="howto-step-display">';
		if (isset($section) && count($section) > 0) {
			foreach ($section[0]["steps"] as $index => $step) {
				ob_start(); ?>
				<li class="howto-step p-3">

					<div class="flex flex-wrap">

						<div class="flex flex-wrap">
							<div class="bg-slate-200 flex flex-wrap items-center justify-center my-2 aspect-square  md:col-span-1 rounded-full w-[2.2rem] md:w-[2.2rem] flex-none font-semibold text-2xl md:text-2xl h-[fit-content]">
								<?= $index + 1 ?>
							</div>

							<div class="px-2 flex-1">
								<<?= $thirdLevelTag ?> id="<?= $step[
 	"anchor"
 ] ?>" class="howto-step__title my-2 font-normal text-[1.3rem] md:text-2xl">
									<?= $step["title"] ?>
								</<?= $thirdLevelTag ?>>
							</div>
						</div>

						<div class="col-span-12 md:col-span-11">
							<?= dbe_convert_to_paragraphs($step["direction"]) ?>
						</div>

						<div class="col-span-12 howto-step__image max-h-[1000px] md:max-h-[1600px] mx-auto">
							<?= $step["stepPic"]["url"] !== ""
       	? ($step["stepPic"]["caption"] === ""
       			? ""
       			: '<figure class="w-full">') .
       		'<img class="howto-step-image mx-auto rounded-xl overflow-hidden" src="' .
       		$step["stepPic"]["url"] .
       		'">' .
       		($step["stepPic"]["caption"] === ""
       			? ""
       			: "<figcaption>" .
       				$step["stepPic"]["caption"] .
       				"</figcaption></figure>")
       	: "" ?>
						</div>
					</div>

					<?php
     $stepsCode .=
     	'{"@type": "HowToStep",' .
     	PHP_EOL .
     	'"name": "' .
     	str_replace("\'", "'", wp_filter_nohtml_kses($step["title"])) .
     	'",' .
     	PHP_EOL .
     	($advancedMode
     		? '"url": "' .
     			get_permalink() .
     			"#" .
     			$step["anchor"] .
     			'",' .
     			PHP_EOL .
     			($step["hasVideoClip"]
     				? '"video":{"@id": "' . $step["anchor"] . '"},'
     				: "") .
     			PHP_EOL
     		: "") .
     	'"image": "' .
     	$step["stepPic"]["url"] .
     	'",' .
     	PHP_EOL .
     	'"itemListElement" :[{' .
     	PHP_EOL .
     	'"@type": "HowToDirection",' .
     	PHP_EOL .
     	'"text": "' .
     	($step["title"] === "" || !$advancedMode
     		? ""
     		: str_replace("\'", "'", wp_filter_nohtml_kses($step["title"])) .
     			" ") .
     	str_replace("\'", "'", wp_filter_nohtml_kses($step["direction"])) .
     	'"}' .
     	PHP_EOL;

     if ($step["tip"] !== "") {
     	echo dbe_convert_to_paragraphs($step["tip"]);
     	$stepsCode .=
     		',{"@type": "HowToTip",' .
     		PHP_EOL .
     		'"text": "' .
     		str_replace("\'", "'", wp_filter_nohtml_kses($step["tip"])) .
     		'"}' .
     		PHP_EOL;
     }
     ?>
				</li>

	<?php
 $stepsDisplay .= ob_get_contents();
 ob_end_clean();
 // ob_flush();

 $stepsCode .= "]}" . PHP_EOL;
 if ($index < count($section[0]["steps"]) - 1) {
 	$stepsCode .= ",";
 }

			}
		}
	}
	$stepsDisplay .= $sectionListStyle === "ordered" ? "</ol>" : "</ul>";
	$stepsCode .= "]";

	$parts = array_map(
		function ($sec) {
			return $sec["steps"];
		},
		isset($section) ? $section : []
	);
	$clips = "";

	if ($videoURL !== "") {
		if (strpos($videoURL, "https://www.youtube.com") === 0) {
			$videoClipArg = "?t=";
		}
		if (strpos($videoURL, "https://vimeo.com") === 0) {
			$videoClipArg = "#t=";
		}
		if (strpos($videoURL, "https://www.dailymotion.com") === 0) {
			$videoClipArg = "?start=";
		}
		if (strpos($videoURL, "https://videopress.com") === 0) {
			$videoClipArg = "?at=";
		}
	}

	foreach ($parts as $part) {
		foreach ($part as $step) {
			if ($step["hasVideoClip"]) {
				if ($clips !== "") {
					$clips .= ",";
				}
				$clips .=
					'{"@type": "Clip",
                            "@id": "' .
					$step["anchor"] .
					'",
                            "name": "' .
					str_replace("\'", "'", $step["title"]) .
					'",
                            "startOffset": "' .
					$step["videoClipStart"] .
					'",
                            "endOffset": "' .
					$step["videoClipEnd"] .
					'",
                            "url": "' .
					$videoURL .
					$videoClipArg .
					$step["videoClipStart"] .
					'" }';
			}
		}
	}

	$JSONLD =
		'<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "HowTo",
        "name":"' .
		str_replace("\'", "'", wp_filter_nohtml_kses($title)) .
		'",
        "description": "' .
		str_replace("\'", "'", wp_filter_nohtml_kses($introduction)) .
		'",' .
		(array_unique($totalTime) !== [0]
			? '"totalTime": "' . $ISOTotalTime . '",'
			: "") .
		($videoURL === ""
			? ""
			: '"video": {
                "@type": "VideoObject",
                "name": "' .
				str_replace("\'", "'", wp_filter_nohtml_kses($videoName)) .
				'",
                "description": "' .
				(str_replace(
					"\'",
					"'",
					wp_filter_nohtml_kses($videoDescription)
				) ?:
					__("No description provided")) .
				'",
                "duration" : "' .
				generateISODurationCode($videoDuration) .
				'",
                "thumbnailUrl": "' .
				esc_url($videoThumbnailURL) .
				'",
                "contentUrl": "' .
				esc_url($videoURL) .
				'",
                "uploadDate": "' .
				date("c", $videoUploadDate) .
				'",
                "hasPart":[' .
				$clips .
				']
            },') .
		($cost > 0
			? '"estimatedCost": {
                "@type": "MonetaryAmount",
                "currency": "' .
				str_replace("\'", "'", wp_filter_nohtml_kses($costCurrency)) .
				'",
                "value": "' .
				wp_filter_nohtml_kses($cost) .
				'"
            },'
			: "") .
		$suppliesCode .
		"," .
		$toolsCode .
		"," .
		$stepsCode .
		',"yield": "' .
		str_replace("\'", "'", wp_filter_nohtml_kses($howToYield)) .
		'",
    "image": "' .
		$finalImageURL .
		'"' .
		',"aggregateRating": {
	"@type": "AggregateRating",
	"ratingValue": "' .
		str_replace("\'", "'", wp_filter_nohtml_kses($howToRatingValue)) .
		'",
	"bestRating": "5",
	"worstRating": "1",
	"ratingCount": "' .
		str_replace("\'", "'", wp_filter_nohtml_kses($howToRatingCount)) .
		'"
	}' .
		"
	}</script>";

	ob_start();
	?>
	<div class="howto" id="howto_<?= $blockID ?>">

		<<?= $firstLevelTag ?> class="howto__title">
			<?= $title ?>
		</<?= $firstLevelTag ?>>

		<?= dbe_convert_to_paragraphs($introduction) ?>
		<?= $header ?>
		<div class="flex w-full justify-center">
			<div class="mr-5">
				<i class="fa fa-tag" aria-hidden="true"></i>
				<?= $advancedMode
    	? (($videoURL === "" ? "" : $videoEmbedCode) . $cost < 1
    		? "Gratis"
    		: "<p>" . $costDisplayText . $costDisplay . "</p>")
    	: "" ?>
			</div>
			<div class="flex items-center">
				<i class="fa fa-clock-o mr-2" aria-hidden="true"></i> <?= $timeDisplay ?>
			</div>
		</div>

		<?= $stepsDisplay ?>

		<div class="howto-yield bg-[#16A085] rounded-xl text-white p-5">
			<div class="w-100 flex flex-wrap justify-center mb-3">
				<<?= $secondLevelTag ?> class="text-white font-bold m-0">
					<?= $resultIntro ?>
				</<?= $secondLevelTag ?>>
			</div>
			<?= $finalImageURL === ""
   	? ""
   	: (!isset($finalImageCaption) || $finalImageCaption === ""
   			? ""
   			: '<figure class="howto-yield-image-container mt-2 w-full relative">') .
   		'<img class="howto-yield-image mx-auto rounded-xl overflow-hidden" src="' .
   		$finalImageURL .
   		'">' .
   		(!isset($finalImageCaption) || $finalImageCaption === ""
   			? ""
   			: "<figcaption class=\"text-white\">" .
   				$finalImageCaption .
   				"</figcaption></figure>") ?>
			<?= dbe_convert_to_paragraphs($howToYield) ?>
		</div>

		<div class="howto-review p-3 mb-3">
			<div class="howto-review__vote">
				<div class="w-full text-center mb-4">
					Apakah artikel ini membantu mu?
				</div>
				<div class="w-full flex flex-wrap justify-center items-center mb-3">
					<div class="howto-review__like mr-3">
						<button class="dashicons dashicons-thumbs-up text-emerald-600"></button>
					</div>
					<div class="howto-review__dislike">
						<button class="dashicons dashicons-thumbs-down text-rose-800"></button>
					</div>
				</div>
			</div>

			<div class="howto-review__thank w-full text-center my-4 text-lg hidden">
				<h2 class="m-0">Terimakasih sudah memberi jawaban</h1>
			</div>

		</div>

		<div class="text-white rounded-xl flex flex-wrap px-4 py-2 mb-3 howto-review-result
		<?php
  $howToReviewPercent = ($howToRatingCount / $howToRatingValue) * 100;
  $howToReviewPercentIcon = "dashicons-thumbs-up";
  if ($howToReviewPercent >= 65) {
  	echo "howto-review-result__good";
  } elseif ($howToReviewPercent >= 50) {
  	$howToReviewPercentIcon = "dashicons-thumbs-down";
  	echo "howto-review-result__medium";
  } else {
  	$howToReviewPercentIcon = "dashicons-thumbs-down";
  	echo "howto-review-result__bad";
  }
  ?> ">
			<h3 class="my-auto ml-0 mr-[10px] md:ml-3 md:mr-8  text-amber-300 text-4xl font-bold leading-none"><?= $howToReviewPercent ?>%</h3>
			<div>
				<p class="m-0">Orang menganggap tutorial ini</p>
				<p class="m-0 howto-review-result__membantu">
					<?php if ($howToReviewPercent >= 65) {
     	echo "sangat ";
     } ?>
					membantu
				</p>
			</div>
			<i class="howto-review-result__icon dashicons <?= $howToReviewPercentIcon ?>"></i>
		</div>

		<script>
			document.querySelectorAll(".howto-review .dashicons-thumbs-up, .howto-review .dashicons-thumbs-down").forEach((el, i) => {
				el.addEventListener("click", (e) => {
					e.target.closest(".howto-review__vote").classList.add("hidden")
					e.target.closest(".howto-review").querySelector(".howto-review__thank").classList.remove("hidden")
				})
			})
		</script>

	</div>
	<?= $JSONLD ?>
<?php return ob_get_clean();
}
function dbe_register_how_to_block()
{
	if (function_exists("register_block_type")) {
		require dirname(dirname(__DIR__)) . "/defaults.php";
		register_block_type("dbe/how-to", [
			"attributes" => $defaultValues["dbe/how-to"]["attributes"],
			"render_callback" => "dbe_render_how_to_block",
		]);
	}
}
add_action("init", "dbe_register_how_to_block");
