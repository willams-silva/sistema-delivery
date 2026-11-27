<?php
function bcFreteRua(PDO $pdo,int $empresa,string $bairro,string $rua,string $entrega, bool $somenteEspecifica=false): ?float {
 if ($entrega !== 'Delivery') return 0.0;
 $q=$pdo->prepare("SELECT id,valor FROM bairros WHERE empresa=? AND nome=? LIMIT 1");
 $q->execute([$empresa,trim($bairro)]); $b=$q->fetch(PDO::FETCH_ASSOC);
 if (!$b) return null;
 if (trim($rua)!=='') {
 $q=$pdo->prepare("SELECT valor FROM bc_frete_ruas WHERE empresa=? AND bairro_id=? AND rua=? LIMIT 1");
 $q->execute([$empresa,$b['id'],trim($rua)]); $v=$q->fetchColumn();
 if ($v!==false) return (float)$v;
 // A specific street fee takes precedence over a zone fee.
 $q=$pdo->prepare("SELECT z.valor FROM bc_frete_zona_ruas zr JOIN bc_frete_zonas z ON z.id=zr.zona_id AND z.empresa=zr.empresa AND z.bairro_id=zr.bairro_id WHERE zr.empresa=? AND zr.bairro_id=? AND zr.rua=? LIMIT 1");
 $q->execute([$empresa,$b['id'],trim($rua)]); $v=$q->fetchColumn();
 if ($v!==false) return (float)$v;
 }
 return $somenteEspecifica ? null : (float)$b['valor'];
}

function bcFreteHibrido(PDO $pdo,int $empresa,array $end,array $cfg): ?float {
 $entrega=(string)($end['entrega']??'');$bairro=(string)($end['bairro']??'');$rua=(string)($end['rua']??'');
 if($entrega!=='Delivery')return 0.0;
 $especifica=bcFreteRua($pdo,$empresa,$bairro,$rua,$entrega,true);
 if($especifica!==null)return $especifica;
 if(($cfg['modo']??'')!=='Sim'||empty($cfg['key']))return bcFreteRua($pdo,$empresa,$bairro,$rua,$entrega);
 if(trim($rua)===''||trim((string)($end['numero']??''))===''||trim((string)($end['cidade']??''))==='')return null;
 if(!is_numeric($cfg['lat'])||!is_numeric($cfg['lng'])||!is_numeric($cfg['limite'])||$cfg['limite']<=0)return null;
 $address=implode(', ',[$rua,$end['numero'],$bairro,$end['cidade'],$end['cep']??'','Brasil']);
 $url='https://maps.googleapis.com/maps/api/geocode/json?'.http_build_query(['address'=>$address,'key'=>$cfg['key']]);
 if(!function_exists('curl_init'))return null;
 $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>4,CURLOPT_TIMEOUT=>8]);$raw=curl_exec($ch);curl_close($ch);
 $data=json_decode((string)$raw,true);$loc=$data['results'][0]['geometry']['location']??null;
 if(($data['status']??'')!=='OK'||!isset($loc['lat'],$loc['lng'])||!empty($data['results'][0]['partial_match']))return null;
 $lat=deg2rad((float)$cfg['lat']);$lat2=deg2rad((float)$loc['lat']);$dl=deg2rad((float)$loc['lng']-(float)$cfg['lng']);
 $a=sin(($lat2-$lat)/2)**2+cos($lat)*cos($lat2)*sin($dl/2)**2;
 $dist=6371*2*asin(sqrt(min(1,max(0,$a))));
 if($dist>(float)$cfg['limite'])return null;
 $km=(float)$cfg['km'];return round(ceil($dist)*($km>0?$km:1),2);
}

function bcFreteConsultaAceita(PDO $pdo,int $empresa,array $end): ?float {
 if(($end['entrega']??'')!=='Delivery')return null;
 $token=$_SESSION['bc_consulta_token'][$empresa]??'';
 if(!$token)return null;
 $normal=[];foreach(['rua','numero','bairro','cidade','cep'] as $k){$normal[$k]=trim((string)($end[$k]??''));}
 $normal['entrega']='Delivery';
 $q=$pdo->prepare("SELECT c.valor FROM bc_frete_consultas c JOIN bc_frete_consulta_config f ON f.empresa=c.empresa AND f.ativo=1 WHERE c.empresa=? AND c.token_hash=? AND c.endereco=? AND c.status='aceito' AND c.expira_em>NOW()");
 $q->execute([$empresa,hash('sha256',$token),json_encode($normal,JSON_UNESCAPED_UNICODE)]);
 $v=$q->fetchColumn();return $v===false||$v===null?null:(float)$v;
}
