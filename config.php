if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('Africa/Nairobi');
define('APP', 'HouseHunt');
define('UPLOAD_DIR', __DIR__.'/uploads');
$host=getenv('DB_HOST') ?: 'localhost';
$name=getenv('DB_NAME') ?: 'househunt';
$user=getenv('DB_USER') ?: 'root';
$pass = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') ?: '');
try {
 $db=new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4",$user,$pass,[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES=>false
 ]);
} catch(PDOException $e) { http_response_code(500); exit('Database connection failed. Check config.php and MySQL settings.'); }
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function go($p='index.php'){header('Location: '.$p);exit;}
function logged(){return isset($_SESSION['uid']);}
function need_login(){if(!logged())go('index.php?page=login');}
function need_role($r){need_login();if($_SESSION['role']!==$r){http_response_code(403);exit('Access denied');}}
function token(){if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function check_token(){if(!hash_equals(token(),$_POST['csrf']??'')){http_response_code(419);exit('Form expired. Refresh and try again.');}}
function flash($m,$type='ok'){$_SESSION['flash']=[$m,$type];}
function money($n){return 'KES '.number_format((float)$n);}
function upload_images($files){
 $saved=[]; if(empty($files['name'][0]))return $saved;
 if(!is_dir(UPLOAD_DIR))mkdir(UPLOAD_DIR,0755,true);
 $f=new finfo(FILEINFO_MIME_TYPE);$types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
 foreach($files['name'] as $i=>$n){
  if($files['error'][$i]===UPLOAD_ERR_NO_FILE)continue;
  if($files['error'][$i]!==UPLOAD_ERR_OK || $files['size'][$i]>5242880)throw new Exception('Each image must upload successfully and be 5 MB or smaller.');
  $mime=$f->file($files['tmp_name'][$i]);if(!isset($types[$mime])||!getimagesize($files['tmp_name'][$i]))throw new Exception('Only valid JPG, PNG and WEBP images are allowed.');
  $file=bin2hex(random_bytes(16)).'.'.$types[$mime];
  if(!move_uploaded_file($files['tmp_name'][$i],UPLOAD_DIR.'/'.$file))throw new Exception('Could not save an image.');
  $saved[]=$file;
 } return $saved;
}
function images_for($db,$id){$s=$db->prepare('SELECT * FROM property_images WHERE property_id=?');$s->execute([$id]);return $s->fetchAll();}
function card($db,$p){
 $imgs=images_for($db,$p['property_id']);$am=$db->prepare('SELECT a.amenity_name FROM amenities a JOIN property_amenities pa ON pa.amenity_id=a.amenity_id WHERE pa.property_id=? LIMIT 3');$am->execute([$p['property_id']]);?>
 <article class="card"><a class="photo" href="index.php?page=property&id=<?=$p['property_id']?>"><?php if($imgs):?><img src="uploads/<?=h($imgs[0]['image_url'])?>" alt="<?=h($p['property_title'])?>"><?php else:?><span>HouseHunt</span><?php endif?><b>Available</b></a><div class="cardbody"><strong class="price"><?=money($p['monthly_rent'])?><small> / month</small></strong><h3><a href="index.php?page=property&id=<?=$p['property_id']?>"><?=h($p['property_title'])?></a></h3><p class="muted">⌖ <?=h($p['neighbourhood'].', '.$p['town'])?></p><div class="meta"><?=h($p['property_type'])?> · <?=intval($p['bedrooms'])?> bed · <?=intval($p['bathrooms'])?> bath</div><p class="muted"><?=h(implode(' · ',$am->fetchAll(PDO::FETCH_COLUMN)))?></p><a class="link" href="index.php?page=property&id=<?=$p['property_id']?>">View details →</a></div></article>
<?php }
function header_html($title='Find a home'){?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> | HouseHunt</title><link rel="stylesheet" href="style.css"><script defer src="app.js"></script></head><body><header><div class="wrap nav"><a class="brand" href="index.php">⌂ House<span>Hunt</span></a><button class="toggle">☰</button><nav><a href="index.php">Home</a><a href="index.php?page=browse">Browse houses</a><?php if(logged()&&$_SESSION['role']==='landlord'):?><a href="index.php?page=dashboard">Dashboard</a><a class="cta" href="index.php?page=create">+ List property</a><?php elseif(logged()):?><a href="index.php?page=favourites">Favourites</a><a href="index.php?page=my-enquiries">My enquiries</a><?php else:?><a href="index.php?page=register&role=landlord">List your property</a><?php endif?><?php if(logged()):?><span class="muted">Hi <?=h($_SESSION['name'])?></span><a href="index.php?page=logout">Log out</a><?php else:?><a href="index.php?page=login">Log in</a><a class="cta" href="index.php?page=register">Get started</a><?php endif?></nav></div></header><?php if(!empty($_SESSION['flash'])){[$m,$t]=$_SESSION['flash'];unset($_SESSION['flash']);echo '<div class="wrap alert">'.h($m).'</div>';}?><main><?php }
function footer_html(){?></main><footer><div class="wrap foot"><div><a class="brand light" href="index.php">⌂ House<span>Hunt</span></a><p>Find a place that feels like home.</p></div><div><b>Explore</b><a href="index.php?page=browse">Browse homes</a><a href="index.php?page=register&role=landlord">List a property</a></div><div><b>Safety</b><p>View properties and verify landlords before paying deposits.</p></div></div><div class="wrap copyright">© <?=date('Y')?> HouseHunt</div></footer></body></html><?php }
