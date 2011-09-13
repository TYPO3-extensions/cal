<?php
ob_start();
system('locale');
$str = ob_get_contents();
ob_end_clean();
$current = explode("\n", trim($str));

ob_start();
system('locale -a');
$str = ob_get_contents();
ob_end_clean();
$locales = explode("\n", trim($str));

echo '<html><head><title>Locale Listing</title></head><body>';

echo '<h3>Default PHP Locale</h3>';
echo '<ul>';
foreach($current as $item) {
   echo '<li>'.$item.'</li>';
}
echo '</ul>';

echo '<h3>Available Locales</h3>';
echo '<ul>';
foreach($locales as $locale) {
   echo '<li>'.$locale.'</li>';
}
echo '</ul>';
echo '</body></html>';
?>