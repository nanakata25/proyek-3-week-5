<?php
$port=getenv('DB_PORT')?:3307;
$pdo=new PDO("mysql:host=127.0.0.1;port=$port;charset=utf8mb4",'root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$queries=[
    'users'=>'SELECT id, username, nama_lengkap, password FROM db_tugas.users ORDER BY id',
    'products'=>'SELECT id_barang,nama_barang,harga,stok FROM db_toko_online.products ORDER BY id_barang',
    'orders'=>'SELECT id_order,id_user,tanggal_order,total_harga,alamat_pengiriman FROM db_toko_online.orders ORDER BY tanggal_order',
    'details'=>'SELECT * FROM db_toko_online.order_details ORDER BY id_order,id_barang',
    'cart'=>'SELECT * FROM db_toko_online.cart_items',
];
$data=['generated_at'=>date(DATE_ATOM),'database_version'=>$pdo->query('SELECT VERSION()')->fetchColumn(),'queries'=>[]];
foreach($queries as $key=>$sql)$data['queries'][$key]=['sql'=>$sql,'rows'=>$pdo->query($sql)->fetchAll()];
$dest=__DIR__.'/../evidence';if(!is_dir($dest))mkdir($dest,0777,true);
file_put_contents($dest.'/database-snapshot.json',json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
$html='<!doctype html><html lang="id"><meta charset="utf-8"><title>Bukti Query Database Modul 4</title><style>body{font:16px Arial;background:#f5f3ee;color:#173342;margin:45px}h1{font-size:32px}h2{color:#087e82}table{border-collapse:collapse;width:100%;background:white;font-size:14px}td,th{border:1px solid #cbd5d9;padding:12px;text-align:left}th{background:#173342;color:white}section{margin:40px 0}code{display:block;padding:14px;background:#e5efeb;font-size:14px} .meta{color:#526872} .hash{font:12px monospace;word-break:break-all}</style><h1>Bukti hasil query database</h1><p class="meta">Ekspor langsung melalui PDO MySQL | '.htmlspecialchars($data['database_version']).' | '.htmlspecialchars($data['generated_at']).'</p>';
foreach($data['queries'] as $key=>$entry){$html.='<section id="'.$key.'"><h2>'.htmlspecialchars($key).'</h2><code>'.htmlspecialchars($entry['sql']).'</code>';if(!$entry['rows']){$html.='<p>0 baris (kosong)</p>';}else{$html.='<table><thead><tr>';foreach(array_keys($entry['rows'][0])as $col)$html.='<th>'.htmlspecialchars($col).'</th>';$html.='</tr></thead><tbody>';foreach($entry['rows']as $row){$html.='<tr>';foreach($row as $k=>$v)$html.='<td class="'.($k==='password'?'hash':'').'">'.htmlspecialchars((string)$v).'</td>';$html.='</tr>';}$html.='</tbody></table>';}$html.='</section>';}
file_put_contents($dest.'/database-snapshot.html',$html.'</html>');echo "Database evidence exported.\n";
