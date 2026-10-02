<?php
declare(strict_types=1);
require __DIR__.'/config.php';
header('Content-Type: application/xml; charset=utf-8');
$base=rtrim(appUrl(''),'/');
$urls=[
 ['index.php',1.0,'daily'],
 ['product.php',0.9,'daily'],
 ['login.php',0.5,'monthly'],
 ['register.php',0.5,'monthly'],
];
$products=[];
try{$products=db()->query("SELECT slug,updated_at FROM products WHERE status='active' ORDER BY id DESC LIMIT 5000")->fetchAll();}catch(Throwable $e){}
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach($urls as [$path,$priority,$freq]){
  echo '<url><loc>'.htmlspecialchars($base.'/'.$path,ENT_XML1).'</loc><changefreq>'.$freq.'</changefreq><priority>'.$priority.'</priority></url>';
}
echo '</urlset>';
