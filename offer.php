<?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set('Europe/Istanbul');

$db = new mysqli("localhost", "***", "***", "***");

if ($db->connect_error) {
	die("Connection failed: " . $db->connect_error);
}

$db->set_charset("utf8");

$res = $db->query("SELECT no FROM orderno WHERE id = 1");
$row = $res->fetch_object();

$orderno = str_pad((string)$row->no, 6, "0", STR_PAD_LEFT);

$db->query("UPDATE orderno SET no = no + 1 WHERE id = 1");

$db->close();

$info = json_decode($_GET["info"], true);
$sum = 0;
$tax = 0;
$total = 0;

for($i = 0; $i < count($info["cart"]); $i++) {
	
	$sum += $info["cart"][$i]["price"];
	$tax += $info["cart"][$i]["price"] * $info["cart"][$i]["tax"] / 100;
	
}

$sum = round($sum,2);
$tax = round($tax,2);
$total = $sum + $tax;

function sayiyiYaziyaCevir($sayi, $kurusbasamak, $parabirimi, $parakurus, $diyez, $bb1, $bb2, $bb3) {

    $b1 = array("", "bir", "iki", "üç", "dört", "beş", "altı", "yedi", "sekiz", "dokuz");
    $b2 = array("", "on", "yirmi", "otuz", "kırk", "elli", "altmış", "yetmiş", "seksen", "doksan");
    $b3 = array("", "yüz", "bin", "milyon", "milyar", "trilyon", "katrilyon");
    
    if ($bb1 != null) { // farklı dil kullanımı yada farklı yazım biçimi için
    $b1 = $bb1;
    }
    if ($bb2 != null) { // farklı dil kullanımı
    $b2 = $bb2;
    }
    if ($bb3 != null) { // farklı dil kullanımı
    $b3 = $bb3;
    }
    
    $say1="";
    $say2 = ""; // say1 virgül öncesi, say2 kuruş bölümü
    $sonuc = "";
    
    $sayi = str_replace(",", ".",$sayi); //virgül noktaya çevrilir
    
    $nokta = strpos($sayi,"."); // nokta indeksi
    
    if ($nokta>0) { // nokta varsa (kuruş)
    
    $say1 = substr($sayi,0, $nokta); // virgül öncesi
    $say2 = substr($sayi,$nokta, strlen($sayi)); // virgül sonrası, kuruş
    
    } else {
    $say1 = $sayi; // kuruş yoksa
    }
    
    $son;
    $w = 1; // işlenen basamak
    $sonaekle = 0; // binler on binler yüzbinler vs. için sona bin (milyon,trilyon...) eklenecek mi?
    $kac = strlen($say1); // kaç rakam var?
    $sonint; // işlenen basamağın rakamsal değeri
    $uclubasamak = 0; // hangi basamakta (birler onlar yüzler gibi)
    $artan = 0; // binler milyonlar milyarlar gibi artışları yapar
    $gecici;
    
    if ($kac > 0) { // virgül öncesinde rakam var mı?
    
    for ($i = 0; $i < $kac; $i++) {
    
    $son = $say1[$kac - 1 - $i]; // son karakterden başlayarak çözümleme yapılır.
    $sonint = $son; // işlenen rakam Integer.parseInt(
    
    if ($w == 1) { // birinci basamak bulunuyor
    
    $sonuc = $b1[$sonint] . $sonuc;
    
    } else if ($w == 2) { // ikinci basamak
    
    $sonuc = $b2[$sonint] . $sonuc;
    
    } else if ($w == 3) { // 3. basamak
    
    if ($sonint == 1) {
    $sonuc = $b3[1] . $sonuc;
    } else if ($sonint > 1) {
    $sonuc = $b1[$sonint] . $b3[1] . $sonuc;
    }
    $uclubasamak++;
    }
    
    if ($w > 3) { // 3. basamaktan sonraki işlemler
    
    if ($uclubasamak == 1) {
    
    if ($sonint > 0) {
    $sonuc = $b1[$sonint] . $b3[2 + $artan] . $sonuc;
    if ($artan == 0) { // birbin yazmasını engelle
    $sonuc = str_replace($b1[1] . $b3[2], $b3[2],$sonuc);
    }
    $sonaekle = 1; // sona bin eklendi
    } else {
    $sonaekle = 0;
    }
    $uclubasamak++;
    
    } else if ($uclubasamak == 2) {
    
    if ($sonint > 0) {
    if ($sonaekle > 0) {
    $sonuc = $b2[$sonint] . $sonuc;
    $sonaekle++;
    } else {
    $sonuc = $b2[$sonint] . $b3[2 + $artan] . $sonuc;
    $sonaekle++;
    }
    }
    $uclubasamak++;
    
    } else if ($uclubasamak == 3) {
    
    if ($sonint > 0) {
    if ($sonint == 1) {
    $gecici = $b3[1];
    } else {
    $gecici = $b1[$sonint] . $b3[1];
    }
    if ($sonaekle == 0) {
    $gecici = $gecici . $b3[2 + $artan];
    }
    $sonuc = $gecici . $sonuc;
    }
    $uclubasamak = 1;
    $artan++;
    }
    
    }
    
    $w++; // işlenen basamak
    
    }
    } // if(kac>0)
    
    if ($sonuc=="") { // virgül öncesi sayı yoksa para birimi yazma
    $parabirimi = "";
    }
    
    $say2 = str_replace(".", "",$say2);
    $kurus = "";
    
    if ($say2!="") { // kuruş hanesi varsa
    
    if ($kurusbasamak > 3) { // 3 basamakla sınırlı
    $kurusbasamak = 3;
    }
    $kacc = strlen($say2);
    if ($kacc == 1) { // 2 en az
    $say2 = $say2."0"; // kuruşta tek basamak varsa sona sıfır ekler.
    $kurusbasamak = 2;
    }
    if (strlen($say2) > $kurusbasamak) { // belirlenen basamak kadar rakam yazılır
    $say2 = substr($say2,0, $kurusbasamak);
    }
    
    $kac = strlen($say2); // kaç rakam var?
    $w = 1;
    
    for ($i = 0; $i < $kac; $i++) { // kuruş hesabı
    
    $son = $say2[$kac - 1 - $i]; // son karakterden başlayarak çözümleme yapılır.
    $sonint = $son; // işlenen rakam Integer.parseInt(
    
    if ($w == 1) { // birinci basamak
    
    if ($kurusbasamak > 0) {
    $kurus = $b1[$sonint] . $kurus;
    }
    
    } else if ($w == 2) { // ikinci basamak
    if ($kurusbasamak > 1) {
    $kurus = $b2[$sonint] . $kurus;
    }
    
    } else if ($w == 3) { // 3. basamak
    if ($kurusbasamak > 2) {
    if ($sonint == 1) { // 'biryüz' ü engeller
    $kurus = $b3[1] . $kurus;
    } else if ($sonint > 1) {
    $kurus = $b1[$sonint] . $b3[1] . $kurus;
    }
    }
    }
    $w++;
    }
    if ($kurus=="") { // virgül öncesi sayı yoksa para birimi yazma
    $parakurus = "";
    } else {
    $kurus = $kurus . " ";
    }
    $kurus = $kurus . $parakurus; // kuruş hanesine 'kuruş' kelimesi ekler
    }
    
    $sonuc = $diyez . $sonuc . " " . $parabirimi . " " . $kurus . $diyez;
    return $sonuc;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deneme Teklif</title>

    <style>

        html, body {
            font-family: Arial, Helvetica, sans-serif;
			width: 21.2cm;
            height: 29.7cm;
			padding: 0;
			margin: 0;
			background: #d13131;
        }

        page {
            background: white;
            display: block;
            margin: 0 auto;
            width: 21.2cm;
            height: 29.7cm;
            box-sizing: border-box;
            padding: 1cm;
        }
       
       @media print {
            body, page {
                margin: 0;
                box-shadow: 0;
            }
        }

        header {
            display: flex;
            flex-direction: row;            
        }

        #logo,
        #heading {
            justify-content: center;
            align-items: center;   
            height: 90px;   
            display: flex;
            flex-direction: column;   
            text-align: center; 
        }

        #logo {
            flex: 1 0 0;
        }

        #heading {            
            flex: 2 0 0;
        }

        .h1 {
            font-size: 11pt;
            font-weight: bold;
        }

        .h2 {
            font-size: 8pt;
            line-height: 1.6;
        }

        .bold {
            font-weight: bold;
        }

        h1 {
            width: 100%;
            overflow: hidden;
            padding: 10px 0;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .container1 {
            width: 100%;
            overflow: hidden;
            border: 1px solid #000;
        }

        .container2 {
            width: 100%;
            overflow: hidden; 
			height: 160px;
        }

        .inner-container {
            width: 60%;
            height: 300px;
            display: block;
            float: left;
            box-sizing: border-box;
            margin: 0;
            padding: 10px;
            overflow-wrap: break-word;
        }

        .inner-container.small {
            width: 40%;
            border-left: 1px solid #000;
        }

        .container2 .inner-container.small {
            border: 0;
        }
        
        .inner-container ul {
            padding: 0;
            margin: 0;
            list-style: none;
            width: 40%;
            float: left;
            display: block;
            font-weight: bold;
            font-size: 11pt;
        }

        .inner-container ul li {
           margin-bottom: 3px;
           height: 17px;
        }

        .inner-container ul:nth-of-type(2) {
            width: 60%;
            font-weight: normal;
        }

        .inner-container:nth-of-type(1) ul:nth-of-type(1) li:nth-of-type(4) {
            height: 30px;
        }
        
        .container2 .inner-container {
            padding: 0 10px;
        }

        .container2 .inner-container:nth-of-type(1) ul:nth-of-type(1) li:nth-of-type(4) {
            height: 17px;
        }

        .inner-container.small ul {
            font-size: 9pt;
        }

        .container2 ul {
            font-size: 9pt;
        }

        .signature-heading {
            width: 150px;
            overflow: hidden;
            padding: 0;
            text-align: center;
            text-decoration: underline;
            display: block;
            font-size: 11pt;
            margin: 1px auto 20px;
        }

        .bank-heading {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 10px;
        }

        .signature {
            width: 100%;
            height: 100px;
            border-radius: 15px;
            border: 1px solid #000;
            position: relative;
        }

        .offer {
            width: 100%;
            overflow: hidden;
            padding: 3px 0;
            text-align: center;
            position: absolute;
            top: 0;
            left: 0;
            font-size: 8pt;
        }

        #table-container {
            width: 100%;
            height: 590px;
        }

        table {
            width: 100%;
            overflow: hidden;
            margin-top: 5px;
            border-collapse: collapse;            
        }

        table thead th {
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            font-size: 11pt;
            border: 1px solid #000;
            border-spacing:0; 
            border-collapse: collapse;
            padding: 5px;
        }

        table tbody td {
            border: 1px solid #000;
            border-spacing:0; 
            border-collapse: collapse; 
            padding: 5px;
            font-size: 9pt;
        }

       .noborder {
            border: 0;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .fs11 {
            font-size: 11pt;
        }

        .inwords {
            padding: 0 5px 5px;
            font-size: 10pt;
            margin-bottom: 5px;
            border-bottom: 1px solid #000;
        }

    </style>

</head>

<body>
    <page size="A4">
        
        <header>
            <div id="logo">
                <img src="***/images/logo.png" height="90"/>
            </div>

            <div id="heading">
                <span class="h1">***</span>
                <span class="h2">*** İstanbul / Bağcılar / Türkiye<br>
                Tel: 0212 *** ** ** - Fax: 0212 *** ** ** - <span class="bold">***</span></span>
            </div>
            
        </header>
        
        <h1>Teklif Formu</h1>

        <div class="container1">
            <div class="inner-container">                
                <ul>
                    <li>Cari Adı</li>
					<li></li>
                    <li>Yetkili</li>
                    <li>Telefon/Faks</li>
                    <li></li>
                    <li>Not</li>
                </ul>
                <ul>
                    <li><?=$info["firm"]?></li>
					<li></li>
                    <li><?=$info["name"]?></li>
                    <li><?=$info["phone"]?></li>
                    <li></li>
                    <li><?=$info["note"]?></li>   
                </ul>
            </div>
            <div class="inner-container small">
                <ul>
                    <li>Tarih</li>
                    <li>Teklif No</li>
                    <li>Sistem Saati</li>
                </ul>
                <ul>
                    <li><?=date("d.m.Y")?></li>
                    <li>BT-<?=$orderno?></li>                   
                    <li><?=date("H:i")?></li>
                </ul>
            </div>
            
        </div>

        <div id="table-container">
            <table>
                    <thead>
                        <tr>
                            <th>ÜRÜN KODU</th>
                            <th>ÜRÜN ADI</th>
                            <th>MİKTAR</th>
							<th>B.FİYAT</th>
                            <th>NET B.FİYAT</th>
                            <th>NET TUTAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						for($i = 0; $i < count($info["cart"]); $i++) {
							
							echo '<tr>
									<td>' . $info["cart"][$i]["sku"] . '</td>
									<td>' . $info["cart"][$i]["name"] . '</td>
									<td class="right">' . number_format($info["cart"][$i]["quantity"],2,",","") . ' KOLİ</td>
									<td class="right">' . number_format(round($info["cart"][$i]["price"] / $info["cart"][$i]["quantity"],2),2,",",".") . '</td>
									<td class="right">' . number_format(round($info["cart"][$i]["price"] / $info["cart"][$i]["quantity"],2),2,",",".") . '</td>
									<td class="right">' . number_format($info["cart"][$i]["price"],2,",",".") . '</td>
								</tr>';
							
						}
						?>
                        <tr>
                            <td class="noborder"></td>
                            <td class="noborder"></td>
                            <td class="noborder"></td>
							<td class="noborder"></td>
							<td class="noborder"></td>
                            <td class="bold">Ara Toplam</td>
                            <td class="right"><?=number_format($sum,2,",",".")?></td>
                        </tr>
						<tr>
                            <td class="noborder"></td>
                            <td class="noborder"></td>
                            <td class="noborder"></td>
							<td class="noborder"></td>
							<td class="noborder"></td>
                            <td class="bold">Net Toplam</td>
                            <td class="right"><?=number_format($sum,2,",",".")?></td>
                        </tr>
                        <tr>
                            <td class="noborder"></td>
                            <td class="noborder"></td>
                            <td class="noborder"></td>
							<td class="noborder"></td>
							<td class="noborder"></td>
                            <td class="bold">Toplam</td>
                            <td class="right"><?=number_format($total,2,",",".")?></td>
                        </tr>
                    </tbody>
                </table>

            </div>

           <!-- <div class="inwords">
                <span class="bold">Yazı ile:</span> YALNIZ <?=sayiyiYaziyaCevir($total,2,"TL","Kr","#",null,null,null)?>
            </div>

            <div class="container2">
            <div class="inner-container">
                <div class="bank-heading">Banka Bilgileri:</div>                
                <ul>
                    <li>Akbank</li>
                    <li>QNB Finansbank</li>
                    <li>Garanti Bankası</li>
                    <li>Yapı Kredi Bankası</li>
                    <li>İş Bankası</li>                    
                    <li>ING Bankası</li>
                </ul>
                <ul>
                    <li>TR** *** *** *** *** *** **</li>
                    <li>TR** *** *** *** *** *** **</li>
                    <li>TR** *** *** *** *** *** **</li>
                    <li>TR** *** *** *** *** *** **</li>
                    <li>TR** *** *** *** *** *** **</li>
                    <li>TR** *** *** *** *** *** **</li>
                </ul>
            </div>
            <div class="inner-container small">
            <span class="signature-heading">Müşteri Onay</span>
                <div class="signature">
                    <div class="offer">TEKLİF</div>
                </div>
            </div>
            
        </div>-->
        
    </page>

</body>
</html>
