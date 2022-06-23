<?php

function dbe_render_rekomendasi_list_block($attributes)
{
  extract($attributes);
  ob_start();
?>

  <div class="rekomendasi-list " id="ranked-list-<?= $blockID ?>">
    <ol class="rekomendasi-list p-0">
      <?php foreach ($lists as $index => $list) : ?>
        <li class="rekomendasi-list-card flex flex-wrap flex-col relative shadow-lg rounded-lg mb-6">
          <!-- Image -->
          <div class="relative aspect-[16/7] object-cover object-center w-full overflow-hidden rounded-t-lg">
            <figure>
              <img class="" src="<?= $list["imageurl"] ?>" />
              <?php if (isset($list['price']) && $list['price'] != "") : ?>
                <span class="absolute right-[20px] top-[20px] text-white hover:bg-red-800/90 p-2 w-auto h-auto rounded-full leading-none transition duration-200 ease-in-out text-lg font-bold px-11 py-3" style="background:rgba(225, 13, 13, 0.86);"><?= $list['pricetag'] ?> <?= $list['price'] ?></span>
              <?php endif ?>

              <div class="absolute inset-0" style="box-shadow: rgb(0 0 0 / 54%) -3px -125px 35px -14px inset;"></div>
            </figure>
            <div class="absolute left-6 bottom-3 flex flex-wrap">
              <h4 class="m-0 font-semibold text-white">
                <?= $index + 1 ?>. &nbsp;
              </h4>
              <h4 class="m-0 font-semibold text-white"><?= $list['title'] ?></h4>
              <p class="w-full m-0 text-white"><?= $list['title'] ?></p>
            </div>
          </div>
          <!-- Image END -->

          <!-- Olshop Link  -->
          <div class="p-5">
            <div class="grid grid-cols-2 gap-3">
              <?php foreach ($list["olshops"] as $index => $olshop) : ?>
                <a href={olshop.url} class="py-2 bg-[#EEEEEE] flex justify-center items-center rounded-lg font-bold text-sm">
                  <?= file_get_contents(__DIR__ . "/icons/Icon" . $olshop['name'] . ".svg") ?>
                  &nbsp; <?= $olshop["name"] ?>
                </a>
              <?php endforeach ?>
            </div>
          </div>
          <!-- Olshop Link END  -->

          <!-- Description -->
          <div class="p-5 pt-0">
            <?= htmlspecialchars_decode(stripslashes($list['description'])) ?>
          </div>
          <!-- Description END -->

          <a href="#" class="text-red-700 font-bold flex flex-wrap items-center justify-center text-base text-center w-full py-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            LIHAT LEBIH BANYAK
          </a>
        </li>
      <?php endforeach ?>
    </ol>
  </div>

<?php return ob_get_clean();
}
function dbe_register_rekomendasi_list_block()
{
  if (function_exists("register_block_type")) {
    require dirname(dirname(__DIR__)) . "/defaults.php";
    register_block_type("dbe/rekomendasi-list", [
      "attributes" => $defaultValues["dbe/rekomendasi-list"]["attributes"],
      "render_callback" => "dbe_render_rekomendasi_list_block",
    ]);
  }
}
add_action("init", "dbe_register_rekomendasi_list_block");
