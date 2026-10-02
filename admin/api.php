<?php
declare(strict_types=1);
require __DIR__.'/../config.php';
$user=loggedUser();
if(!$user || $user['role']!=='admin') jsonResponse(false,'Admin access required.',[],403);
requirePost(); requireCsrf();
$action=trim((string)($_POST['action']??'')); $pdo=db();
function adminLog(PDO $pdo,array $u,string $action,string $type='',?int $id=null,string $details=''):void{ $s=$pdo->prepare('INSERT INTO admin_activity_log(admin_id,action,entity_type,entity_id,details,ip_address) VALUES(?,?,?,?,?,?)'); $s->execute([(int)$u['id'],$action,$type,$id,$details,clientIp()]); }
function slugify2(string $v):string{$v=strtolower(trim($v));$v=preg_replace('/[^a-z0-9]+/','-',$v);return trim($v,'-')?:'product-'.bin2hex(random_bytes(3));}
function productPayload():array{
 $keys=['name','brand','category','model_name','product_pid','barcode','item','cat_no','product_size','color_finish','net_quantity','badge','tagline','short_description','description','voltage','manufactured_on','maximum_suction','net_weight','net_contents','sku','status'];$d=[];foreach($keys as $k)$d[$k]=trim((string)($_POST[$k]??''));
 foreach(['mrp','selling_price','old_price','discount_percent','you_save','price','rating'] as $k)$d[$k]=round((float)($_POST[$k]??0),2);
 $d['stock']=max(0,(int)($_POST['stock']??0));$d['reviews']=max(0,(int)($_POST['reviews']??0));$d['featured']=!empty($_POST['featured'])?1:0;
 $d['stock_status']=in_array($_POST['stock_status']??'', ['in_stock','low_stock','out_of_stock','preorder'],true)?$_POST['stock_status']:'in_stock';
 $d['key_features']=trim((string)($_POST['key_features']??''));$d['specifications']=trim((string)($_POST['specifications']??''));$d['image_url']=trim((string)($_POST['image_url']??''));
 if($d['selling_price']<=0 && $d['price']>0)$d['selling_price']=$d['price']; if($d['price']<=0)$d['price']=$d['selling_price'];
 if($d['mrp']<=0)$d['mrp']=$d['old_price']>0?$d['old_price']:$d['selling_price'];
 if($d['discount_percent']<=0 && $d['mrp']>$d['selling_price'])$d['discount_percent']=round((($d['mrp']-$d['selling_price'])/$d['mrp'])*100,2);
 if($d['you_save']<=0 && $d['mrp']>$d['selling_price'])$d['you_save']=round($d['mrp']-$d['selling_price'],2);
 if($d['stock']<=0)$d['stock_status']='out_of_stock'; elseif($d['stock']<=5 && $d['stock_status']==='in_stock')$d['stock_status']='low_stock';
 return $d;
}
function jsonField(string $v):string{if($v==='')return json_encode(new stdClass());$a=json_decode($v,true);return json_encode($a===null?array_values(array_filter(array_map('trim',preg_split('/\r?\n|,/',$v)),fn($x)=>$x!=='')):$a,JSON_UNESCAPED_UNICODE);}

if($action==='save-product'){
 $d=productPayload(); if($d['name']===''||$d['category']==='')jsonResponse(false,'Product name and category are required.',[],422);
 $id=(int)($_POST['id']??0); $slug=slugify2($d['name'].'-'.($d['product_pid']?:$d['sku']?:uniqid()));
 if(!empty($_FILES['image']['name'])){if($_FILES['image']['error']!==UPLOAD_ERR_OK||$_FILES['image']['size']>5*1024*1024)jsonResponse(false,'Image upload failed or is larger than 5 MB.',[],422);$mime=(new finfo(FILEINFO_MIME_TYPE))->file($_FILES['image']['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;if(!$ext)jsonResponse(false,'Only JPG, PNG and WEBP images are allowed.',[],422);$dir=__DIR__.'/../uploads/products';if(!is_dir($dir))mkdir($dir,0755,true);$name=bin2hex(random_bytes(10)).'.'.$ext;move_uploaded_file($_FILES['image']['tmp_name'],$dir.'/'.$name);$d['image_url']='uploads/products/'.$name;}
 $d['key_features']=jsonField($d['key_features']);$d['specifications']=jsonField($d['specifications']);
 try{$pdo->beginTransaction();
 if($id){$q=$pdo->prepare('UPDATE products SET name=?,slug=?,brand=?,category=?,model_name=?,product_pid=?,barcode=?,item=?,cat_no=?,product_size=?,color_finish=?,net_quantity=?,mrp=?,selling_price=?,old_price=?,discount_percent=?,you_save=?,price=?,stock=?,stock_status=?,rating=?,reviews=?,badge=?,tagline=?,short_description=?,description=?,key_features=?,specifications=?,voltage=?,manufactured_on=?,maximum_suction=?,net_weight=?,net_contents=?,image_url=?,sku=?,status=?,featured=? WHERE id=?');$q->execute([$d['name'],$slug,$d['brand'],$d['category'],$d['model_name'],$d['product_pid']?:null,$d['barcode']?:null,$d['item'],$d['cat_no'],$d['product_size'],$d['color_finish'],$d['net_quantity'],$d['mrp'],$d['selling_price'],$d['old_price']?:null,$d['discount_percent'],$d['you_save'],$d['price'],$d['stock'],$d['stock_status'],$d['rating'],$d['reviews'],$d['badge'],$d['tagline'],$d['short_description'],$d['description'],$d['key_features'],$d['specifications'],$d['voltage'],$d['manufactured_on']?:null,$d['maximum_suction'],$d['net_weight'],$d['net_contents'],$d['image_url'],$d['sku']?:null,$d['status'],$d['featured'],$id]);adminLog($pdo,$user,'update','product',$id,$d['name']);
 }else{$q=$pdo->prepare('INSERT INTO products(name,slug,brand,category,model_name,product_pid,barcode,item,cat_no,product_size,color_finish,net_quantity,mrp,selling_price,old_price,discount_percent,you_save,price,stock,stock_status,rating,reviews,badge,tagline,short_description,description,key_features,specifications,voltage,manufactured_on,maximum_suction,net_weight,net_contents,image_url,sku,status,featured) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');$q->execute([$d['name'],$slug,$d['brand'],$d['category'],$d['model_name'],$d['product_pid']?:null,$d['barcode']?:null,$d['item'],$d['cat_no'],$d['product_size'],$d['color_finish'],$d['net_quantity'],$d['mrp'],$d['selling_price'],$d['old_price']?:null,$d['discount_percent'],$d['you_save'],$d['price'],$d['stock'],$d['stock_status'],$d['rating'],$d['reviews'],$d['badge'],$d['tagline'],$d['short_description'],$d['description'],$d['key_features'],$d['specifications'],$d['voltage'],$d['manufactured_on']?:null,$d['maximum_suction'],$d['net_weight'],$d['net_contents'],$d['image_url'],$d['sku']?:null,$d['status'],$d['featured']]);$id=(int)$pdo->lastInsertId();adminLog($pdo,$user,'create','product',$id,$d['name']);}
 $pdo->commit();jsonResponse(true,'Product saved successfully.',['id'=>$id]);}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();jsonResponse(false,'Could not save product: '.$e->getMessage(),[],422);}
}
if($action==='delete-product'){$id=(int)($_POST['id']??0);$s=$pdo->prepare('DELETE FROM products WHERE id=?');$s->execute([$id]);adminLog($pdo,$user,'delete','product',$id);jsonResponse($s->rowCount()===1,$s->rowCount()?'Product deleted.':'Product not found.');}
if($action==='bulk-import'){
 $rows=json_decode((string)($_POST['rows']??'[]'),true);if(!is_array($rows)||!$rows)jsonResponse(false,'No rows found.',[],422);$ok=0;$skip=0;$errors=[];$pdo->beginTransaction();try{$q=$pdo->prepare('INSERT INTO products(name,slug,brand,category,model_name,product_pid,barcode,item,cat_no,product_size,color_finish,net_quantity,mrp,selling_price,old_price,discount_percent,you_save,price,stock,stock_status,rating,reviews,badge,tagline,description,key_features,specifications,voltage,manufactured_on,maximum_suction,net_weight,net_contents,image_url,sku,status,featured) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');foreach($rows as $i=>$r){$name=trim((string)($r['name']??$r['Product Name']??''));$cat=trim((string)($r['category']??$r['Category']??''));if($name===''||$cat===''){$skip++;$errors[]='Row '.($i+1).': Product Name and Category required.';continue;}$mrp=(float)($r['mrp']??$r['MRP']??0);$sp=(float)($r['selling_price']??$r['Selling Price']??$r['price']??0);$old=(float)($r['old_price']??$r['Old Price']??0);$disc=$mrp>$sp&&$mrp>0?round(($mrp-$sp)/$mrp*100,2):(float)($r['discount_percent']??0);$save=$mrp>$sp?$mrp-$sp:0;$stock=max(0,(int)($r['stock']??$r['Stock']??0));$status=$stock<=0?'out_of_stock':($stock<=5?'low_stock':'in_stock');$q->execute([$name,slugify2($name.'-'.uniqid()),trim((string)($r['brand']??$r['Brand']??'')), $cat,trim((string)($r['model_name']??$r['Model Name']??'')),trim((string)($r['product_pid']??$r['Product ID / P-ID']??''))?:null,trim((string)($r['barcode']??$r['Barcode']??''))?:null,trim((string)($r['item']??$r['Item']??'')),trim((string)($r['cat_no']??$r['CAT No.']??'')),trim((string)($r['product_size']??$r['Product Size']??'')),trim((string)($r['color_finish']??$r['Color / Finish']??'')),trim((string)($r['net_quantity']??$r['Net Quantity']??'')),$mrp,$sp,$old?:null,$disc,$save,$sp,$stock,$status,(float)($r['rating']??$r['Rating']??0),(int)($r['reviews']??$r['Reviews']??0),trim((string)($r['badge']??$r['Badge']??'')),trim((string)($r['tagline']??$r['Tagline']??'')),trim((string)($r['description']??$r['Description']??'')),jsonField((string)($r['key_features']??$r['Key Features']??'')),jsonField((string)($r['specifications']??$r['Specifications']??'')),trim((string)($r['voltage']??$r['Voltage']??'')),trim((string)($r['manufactured_on']??$r['Manufactured On']??''))?:null,trim((string)($r['maximum_suction']??$r['Maximum Suction']??'')),trim((string)($r['net_weight']??$r['Net Weight']??'')),trim((string)($r['net_contents']??$r['Net Contents']??'')),trim((string)($r['image_url']??$r['Product Image']??'')),trim((string)($r['sku']??$r['SKU']??''))?:null,'active',0]);$ok++;} $pdo->commit();adminLog($pdo,$user,'bulk-import','product',null,"Imported $ok products");jsonResponse(true,"Import complete. $ok added, $skip skipped.",['added'=>$ok,'skipped'=>$skip,'errors'=>$errors]);}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();jsonResponse(false,'Import failed: '.$e->getMessage(),[],422);}
}
if($action==='message-status'){$id=(int)$_POST['id'];$status=in_array($_POST['status']??'', ['new','read','replied','closed'],true)?$_POST['status']:'new';$q=$pdo->prepare('UPDATE contact_messages SET status=? WHERE id=?');$q->execute([$status,$id]);jsonResponse(true,'Message status updated.');}
if($action==='save-coupon'){$id=(int)($_POST['id']??0);$code=strtoupper(trim((string)($_POST['code']??'')));$title=trim((string)($_POST['title']??''));$description=trim((string)($_POST['description']??''));$type=in_array($_POST['discount_type']??'', ['percent','fixed'],true)?$_POST['discount_type']:'percent';$value=max(0,(float)($_POST['discount_value']??0));$max=$_POST['max_discount']!==''?(float)$_POST['max_discount']:null;$min=max(0,(float)($_POST['min_order_amount']??0));$starts=trim((string)($_POST['starts_at']??''));$expires=trim((string)($_POST['expires_at']??''));$limit=$_POST['usage_limit']!==''?max(1,(int)$_POST['usage_limit']):null;$per=max(1,(int)($_POST['per_user_limit']??1));$active=(int)($_POST['active']??1)?1:0;if($code==='')jsonResponse(false,'Coupon code is required.',[],422);if($type==='percent'&&$value>100)jsonResponse(false,'Percent discount cannot exceed 100.',[],422);try{if($id){$q=$pdo->prepare('UPDATE coupons SET code=?,title=?,description=?,discount_type=?,discount_value=?,max_discount=?,min_order_amount=?,starts_at=?,expires_at=?,usage_limit=?,per_user_limit=?,active=? WHERE id=?');$q->execute([$code,$title,$description,$type,$value,$max,$min,$starts?:null,$expires?:null,$limit,$per,$active,$id]);}else{$q=$pdo->prepare('INSERT INTO coupons(code,title,description,discount_type,discount_value,max_discount,min_order_amount,starts_at,expires_at,usage_limit,per_user_limit,active) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)');$q->execute([$code,$title,$description,$type,$value,$max,$min,$starts?:null,$expires?:null,$limit,$per,$active]);$id=(int)$pdo->lastInsertId();}adminLog($pdo,$user,$id?'update':'create','coupon',$id,$code);jsonResponse(true,'Coupon saved.',['id'=>$id]);}catch(Throwable $e){jsonResponse(false,'Could not save coupon: '.$e->getMessage(),[],422);}}
if($action==='create-employee'){$name=trim((string)$_POST['name']??'');$phone=trim((string)$_POST['phone']??'');$email=strtolower(trim((string)$_POST['email']??''));$pass=(string)$_POST['password']??'';if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<8)jsonResponse(false,'Name, valid email and 8+ character password are required.',[],422);$s=$pdo->prepare("INSERT INTO users(name,phone,email,password,role,status) VALUES(?,?,?,?, 'employee','active')");try{$s->execute([$name,$phone,$email,password_hash($pass,PASSWORD_DEFAULT)]);$id=(int)$pdo->lastInsertId();adminLog($pdo,$user,'create','employee',$id,$name);jsonResponse(true,'Employee account created.',['id'=>$id]);}catch(Throwable $e){jsonResponse(false,'Could not create employee: '.$e->getMessage(),[],422);}}
if($action==='update-employee'){$id=(int)$_POST['id'];$status=in_array($_POST['status']??'', ['active','inactive','blocked'],true)?$_POST['status']:'active';$name=trim((string)$_POST['name']);$phone=trim((string)$_POST['phone']);$email=strtolower(trim((string)$_POST['email']));$q=$pdo->prepare('UPDATE users SET name=?,phone=?,email=?,status=? WHERE id=? AND role="employee"');$q->execute([$name,$phone,$email,$status,$id]);if(!empty($_POST['password'])){$q=$pdo->prepare('UPDATE users SET password=? WHERE id=? AND role="employee"');$q->execute([password_hash((string)$_POST['password'],PASSWORD_DEFAULT),$id]);}adminLog($pdo,$user,'update','employee',$id,$name);jsonResponse(true,'Employee updated.');}
if($action==='delete-employee'){$id=(int)$_POST['id'];$q=$pdo->prepare('UPDATE users SET status="inactive" WHERE id=? AND role="employee"');$q->execute([$id]);adminLog($pdo,$user,'disable','employee',$id);jsonResponse(true,'Employee disabled.');}
if($action==='assign'){$eid=(int)$_POST['employee_id'];$type=in_array($_POST['assignment_type']??'', ['work','delivery'],true)?$_POST['assignment_type']:'work';$orderId=(int)($_POST['order_id']??0)?:null;$title=trim((string)$_POST['title']);$desc=trim((string)$_POST['description']);$address=trim((string)$_POST['address']);$priority=in_array($_POST['priority']??'', ['low','normal','high','urgent'],true)?$_POST['priority']:'normal';$due=trim((string)$_POST['due_at']);$q=$pdo->prepare('INSERT INTO employee_assignments(employee_id,assigned_by,order_id,assignment_type,title,description,address,priority,due_at) VALUES(?,?,?,?,?,?,?,?,?)');$q->execute([$eid,(int)$user['id'],$orderId,$type,$title,$desc,$address,$priority,$due?:null]);$id=(int)$pdo->lastInsertId();adminLog($pdo,$user,'assign','assignment',$id,$title);jsonResponse(true,'Assignment created.');}
if($action==='update-order-status'){$id=(int)$_POST['order_id'];$status=$_POST['status']??'';if(!in_array($status,['accepted','processing','packed','shipped','delivered','cancelled'],true))jsonResponse(false,'Invalid status.',[],422);$q=$pdo->prepare('UPDATE orders SET order_status=? WHERE id=?');$q->execute([$status,$id]);adminLog($pdo,$user,'update','order',$id,$status);jsonResponse(true,'Order status updated.');}
if($action==='attendance-manual'){$eid=(int)$_POST['employee_id'];$date=$_POST['attendance_date']?:date('Y-m-d');$q=$pdo->prepare("INSERT INTO attendance(employee_id,attendance_date,method,status,approved_by,approved_at,note) VALUES(?,?, 'admin_manual','present',?,NOW(),?) ON DUPLICATE KEY UPDATE status='present',approved_by=VALUES(approved_by),approved_at=NOW(),method='admin_manual',note=VALUES(note)");$q->execute([$eid,$date,(int)$user['id'],trim((string)$_POST['note'])]);jsonResponse(true,'Attendance marked present.');}
if($action==='attendance-decision'){$id=(int)$_POST['id'];$status=in_array($_POST['status']??'', ['present','rejected'],true)?$_POST['status']:'rejected';$q=$pdo->prepare('UPDATE attendance SET status=?,approved_by=?,approved_at=NOW() WHERE id=?');$q->execute([$status,(int)$user['id'],$id]);jsonResponse(true,'Attendance updated.');}
if($action==='save-office'){$lat=$_POST['latitude']!==''?(float)$_POST['latitude']:null;$lng=$_POST['longitude']!==''?(float)$_POST['longitude']:null;$radius=max(10,min(1000,(int)$_POST['radius_meters']));$q=$pdo->prepare('UPDATE office_settings SET office_name=?,latitude=?,longitude=?,radius_meters=? WHERE id=1');$q->execute([trim((string)$_POST['office_name']),$lat,$lng,$radius]);jsonResponse(true,'Attendance location saved.');}


if($action==='save-maintenance'){
 $enabled=!empty($_POST['enabled'])?'1':'0'; $title=trim((string)($_POST['title']??'')); $message=trim((string)($_POST['message']??''));
 if($title==='')$title='We are upgrading Swarnim Groups'; if($message==='')$message='Our website is temporarily unavailable while we improve your shopping experience.';
 try{$pdo->beginTransaction();$q=$pdo->prepare("INSERT INTO site_settings(setting_key,value,updated_by) VALUES('maintenance_mode',?,?) ON DUPLICATE KEY UPDATE value=VALUES(value),updated_by=VALUES(updated_by)");$q->execute([$enabled,(int)$user['id']]);$q=$pdo->prepare("INSERT INTO site_settings(setting_key,value,updated_by) VALUES('maintenance_title',?,?) ON DUPLICATE KEY UPDATE value=VALUES(value),updated_by=VALUES(updated_by)");$q->execute([$title,(int)$user['id']]);$q=$pdo->prepare("INSERT INTO site_settings(setting_key,value,updated_by) VALUES('maintenance_message',?,?) ON DUPLICATE KEY UPDATE value=VALUES(value),updated_by=VALUES(updated_by)");$q->execute([$message,(int)$user['id']]);adminLog($pdo,$user,'update','site_settings',null,'Maintenance mode '.($enabled==='1'?'enabled':'disabled'));$pdo->commit();jsonResponse(true,'Maintenance settings saved.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();jsonResponse(false,'Could not save settings: '.$e->getMessage(),[],422);}
}

if($action==='send-broadcast'){
    $audience=$_POST['audience']??'all';
    $channel=$_POST['channel']??'email';
    $subject=trim((string)($_POST['subject']??''));
    $message=trim((string)($_POST['message']??''));
    $uid=(int)($_POST['user_id']??0);
    if(!in_array($audience,['all','individual'],true)||!in_array($channel,['email','whatsapp','both'],true)) jsonResponse(false,'Invalid messaging options.',[],422);
    if($message==='') jsonResponse(false,'Message is required.',[],422);
    if(mb_strlen($message)>5000) jsonResponse(false,'Message is too long.',[],422);
    if(in_array($channel,['email','both'],true)&&$subject==='') $subject='Swarnim Groups Message';
    if($audience==='individual'){
        $st=$pdo->prepare("SELECT id,name,email,phone FROM users WHERE id=? AND role='user' LIMIT 1");
        $st->execute([$uid]); $recipients=$st->fetchAll();
    }else{
        $recipients=$pdo->query("SELECT id,name,email,phone FROM users WHERE role='user' AND status='active' ORDER BY id")->fetchAll();
    }
    if(!$recipients) jsonResponse(false,'No matching active users found.',[],422);

    function broadcastWhatsApp(string $phone,string $message): array{
        $digits=preg_replace('/\D+/','',$phone);
        if(!$digits) return ['status'=>'failed','error'=>'Missing phone number'];
        if(strlen($digits)===10) $digits='91'.$digits;
        if(strlen($digits)<10 || strlen($digits)>15) return ['status'=>'failed','error'=>'Invalid phone number'];
        $token=envv('WHATSAPP_TOKEN'); $phoneId=envv('WHATSAPP_PHONE_NUMBER_ID');
        if(!$token || !$phoneId) return ['status'=>'link_ready','error'=>null,'url'=>'https://wa.me/'.$digits.'?text='.rawurlencode($message)];
        if(!function_exists('curl_init')) return ['status'=>'failed','error'=>'PHP cURL extension is required for WhatsApp Cloud API.'];
        $version=envv('WHATSAPP_GRAPH_VERSION','v23.0');
        $mode=strtolower(envv('WHATSAPP_MODE','text'));
        if($mode==='template'){
            $template=envv('WHATSAPP_TEMPLATE_NAME'); $lang=envv('WHATSAPP_TEMPLATE_LANGUAGE','en_US');
            if(!$template) return ['status'=>'failed','error'=>'WHATSAPP_TEMPLATE_NAME is required in template mode.'];
            $payload=['messaging_product'=>'whatsapp','to'=>$digits,'type'=>'template','template'=>['name'=>$template,'language'=>['code'=>$lang],'components'=>[['type'=>'body','parameters'=>[['type'=>'text','text'=>$message]]]]]];
        }else{
            $payload=['messaging_product'=>'whatsapp','to'=>$digits,'type'=>'text','text'=>['preview_url'=>false,'body'=>$message]];
        }
        $ch=curl_init('https://graph.facebook.com/'.rawurlencode($version).'/'.rawurlencode($phoneId).'/messages');
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$token,'Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_UNICODE),CURLOPT_TIMEOUT=>25]);
        $resp=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$curlErr=curl_error($ch);curl_close($ch);
        if($code>=200&&$code<300)return ['status'=>'sent','error'=>null];
        $detail=$curlErr?:substr((string)$resp,0,450);return ['status'=>'failed','error'=>'WhatsApp API '.$code.': '.$detail];
    }

    try{
        $pdo->beginTransaction();
        $st=$pdo->prepare('INSERT INTO message_campaigns(admin_id,channel,audience_type,subject,message) VALUES(?,?,?,?,?)');
        $st->execute([(int)$user['id'],$channel,$audience,$subject,$message]);
        $cid=(int)$pdo->lastInsertId(); $sent=0; $failed=0; $links=[]; $emailSent=0; $waSent=0;
        $d=$pdo->prepare('INSERT INTO message_deliveries(campaign_id,user_id,recipient,channel,status,error_message) VALUES(?,?,?,?,?,?)');
        foreach($recipients as $r){
            if(in_array($channel,['email','both'],true)){
                $recipient=trim((string)$r['email']);$status='queued';$err=null;
                if(!$recipient||!filter_var($recipient,FILTER_VALIDATE_EMAIL)){$status='failed';$err='Invalid or missing email';}
                else{
                    $logo=appUrl('data/Swarnim%20Logo.png');
                    $safe=nl2br(htmlspecialchars($message,ENT_QUOTES,'UTF-8'));
                    $html='<div style="font-family:Inter,Arial,sans-serif;background:#f5f7fb;padding:32px;color:#172033"><div style="max-width:620px;margin:auto;background:#fff;border:1px solid #e7eaf0;border-radius:18px;overflow:hidden"><div style="padding:22px;background:#0b1018;color:#fff"><img src="'.$logo.'" width="48" height="48" style="object-fit:contain;vertical-align:middle;margin-right:10px"><strong style="font-size:20px;vertical-align:middle">Swarnim Groups</strong></div><div style="padding:28px"><h2 style="margin-top:0">'.htmlspecialchars($subject,ENT_QUOTES,'UTF-8').'</h2><div style="font-size:15px;line-height:1.75">'.$safe.'</div></div><div style="padding:16px 28px;background:#f7f8fa;color:#7b8494;font-size:12px">This message was sent by Swarnim Groups.</div></div></div>';
                    $ok=sendSmtp($recipient,$subject,$html);$status=$ok?'sent':'failed';$err=$ok?null:'SMTP delivery failed; check SMTP configuration.';
                }
                $d->execute([$cid,(int)$r['id'],$recipient,$channel==='both'?'email':'email',$status,$err]);
                if($status==='sent'){$sent++;$emailSent++;}elseif($status==='failed')$failed++;
            }
            if(in_array($channel,['whatsapp','both'],true)){
                $recipient=preg_replace('/\D+/','',(string)$r['phone']);$wa=broadcastWhatsApp((string)$r['phone'],$message);$status=$wa['status'];$err=$wa['error']??null;
                $d->execute([$cid,(int)$r['id'],$recipient,'whatsapp',$status,$err]);
                if($status==='sent'){$sent++;$waSent++;}elseif($status==='failed')$failed++;elseif($status==='link_ready')$links[]=['name'=>$r['name'],'url'=>$wa['url']];
            }
        }
        adminLog($pdo,$user,'send','message_campaign',$cid,$channel.' '.$audience);
        $pdo->commit();
        $summary=$sent.' channel deliveries sent, '.$failed.' failed'.($links?' and '.count($links).' WhatsApp links ready':'').'.';
        jsonResponse(true,'Message campaign processed.',['summary'=>$summary,'email_sent'=>$emailSent,'whatsapp_sent'=>$waSent,'links'=>$links,'campaign_id'=>$cid]);
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();jsonResponse(false,'Message sending failed: '.$e->getMessage(),[],422);}
}

jsonResponse(false,'Unknown action.',[],400);
