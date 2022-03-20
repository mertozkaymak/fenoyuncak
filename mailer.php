<?php
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/phpmailer/PHPMailerAutoload.php';

if(!isset($_POST["info"])) {
	exit();
}

$info = json_decode($_POST["info"], true);

$email = $info["email"];
$name = $info["name"];
$firm = $info["firm"];
$phone = $info["phone"];
$pdf = __DIR__ . "/" . $info["pdf"];
$pdfname = explode("/", $info["pdf"]);
$pdfname = $pdfname[1];

$attachment = [["path" => $pdf, "name" => $pdfname, "type" => "application/pdf"]];

$body = '<img src="https://dev.digitalfikirler.com/fenoyuncak/images/logo.png" width="220"/><br><br>Sayın ' . $name . ',<br><br>Talep ettiğiniz teklif ektedir. Sizinle en kısa süre içerisinde iletişimde olacağız.<br><br>Bizi tercih ettiğiniz için teşekkür ederiz.';

$response = sendEmail($email, $name, $body, $attachment);

require_once 'excel/PHPExcel.php';
require_once 'excel/PHPExcel/IOFactory.php';

$objPHPExcel = new PHPExcel();

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);

$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->SetCellValue('A1', "Firma");
$objPHPExcel->getActiveSheet()->SetCellValue('A2', "Ad/Soyad");
$objPHPExcel->getActiveSheet()->SetCellValue('A3', "Telefon");
$objPHPExcel->getActiveSheet()->SetCellValue('A4', "E-Mail");
$objPHPExcel->getActiveSheet()->SetCellValue('B1', $info["firm"]);
$objPHPExcel->getActiveSheet()->SetCellValue('B2', $info["name"]);
$objPHPExcel->getActiveSheet()->SetCellValue('B3', $info["phone"]);
$objPHPExcel->getActiveSheet()->SetCellValue('B4', $info["email"]);

$objPHPExcel->getActiveSheet()->getStyle('A1:A4')->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->SetCellValue('A7', "STOK KODU");
$objPHPExcel->getActiveSheet()->SetCellValue('B7', "STOK ADI");
$objPHPExcel->getActiveSheet()->SetCellValue('C7', "MİKTAR");
$objPHPExcel->getActiveSheet()->SetCellValue('D7', "B. FİYAT");
$objPHPExcel->getActiveSheet()->SetCellValue('E7', "NET B.FİYAT");
$objPHPExcel->getActiveSheet()->SetCellValue('F7', "İSKONTO (%)");
$objPHPExcel->getActiveSheet()->SetCellValue('G7', "NET TUTAR");
$objPHPExcel->getActiveSheet()->SetCellValue('H7', "KDV ORANI (%)");

$objPHPExcel->getActiveSheet()->getStyle("A7:H7")->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    )
);

$objPHPExcel->getActiveSheet()->getStyle('A7:H7')->getFont()->setBold(true);

$row = 8;

for($i = 0; $i < count($info["cart"]); $i++) {

    $objPHPExcel->getActiveSheet()->getStyle("C" . $row . ":H" . $row)->getNumberFormat()->setFormatCode("#,##0.00");

	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $info["cart"][$i]["sku"]);
	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $info["cart"][$i]["name"]);
	$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $info["cart"][$i]["quantity"]);
	$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, round($info["cart"][$i]["price"] / $info["cart"][$i]["quantity"],2));
	$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, '=C'.$row."*D".$row);
	$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, 0);
    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=IF(E'.$row.'<>0,E'.$row.' - ROUND(E'.$row.'*'.'F'.$row.'/100,2),E'.$row.')');
    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $info["cart"][$i]["tax"]);
        	
	$objPHPExcel->getActiveSheet()->getStyle("A" . $row . ":H" . $row)->applyFromArray(
		array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		)
	);
	
	$row++;
	
}

$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, "ARA TOPLAM");
$objPHPExcel->getActiveSheet()->getStyle("G" . $row)->getNumberFormat()->setFormatCode("#,##0.00");
$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=SUM(E8:E' . ($row-1) . ')');
$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()->getStyle("F" . $row . ":G" . $row)->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	)
);

$row++;
$row++;

$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, "NET TOPLAM");
$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle("G" . $row)->getNumberFormat()->setFormatCode("#,##0.00");
$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=SUM(G8:G' . ($row-3) . ')');

$objPHPExcel->getActiveSheet()->getStyle("F" . $row . ":G" . $row)->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	)
);

$row--;

$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, "İSKONTO");
$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle("G" . $row)->getNumberFormat()->setFormatCode("#,##0.00");
$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=G' . ($row-1) . '-G' . ($row+1));

$objPHPExcel->getActiveSheet()->getStyle("F" . $row . ":G" . $row)->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	)
);

$row++;
$row++;

$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, "TOPLAM KDV");
$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle("G" . $row)->getNumberFormat()->setFormatCode("#,##0.00");
$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=SUMPRODUCT(G8:G' . ($row-4) . ',H8:H' . ($row-4) . ') / 100');

$objPHPExcel->getActiveSheet()->getStyle("F" . $row . ":G" . $row)->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	)
);

$row++;

$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, "TOPLAM");
$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle("G" . $row)->getNumberFormat()->setFormatCode("#,##0.00");
$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, '=SUM(G' . ($row-1) . ':G' . ($row-2) . ')');

$objPHPExcel->getActiveSheet()->getStyle("F" . $row . ":G" . $row)->applyFromArray(
	array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	)
);

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

$excel = str_replace(".pdf","",$pdfname) . '.xlsx';

$objWriter->save(__DIR__ . "/pdf/" . $excel);

array_push($attachment, ["path" => __DIR__ . "/pdf/" . $excel, "name" => $excel, "type" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]);

$body = '<img src="https://dev.digitalfikirler.com/fenoyuncak/images/logo.png" width="220"/><br><br>Sayın yetkili,<br><br>' . $name . ' bir teklif talebinde bulundu. Teklif örneği ektedir.<br><br>Müşteri Bilgileri:<br><br>Firma Adı: ' . $firm . '<br>Ad/Soyad: ' . $name . '<br>E-Mail: ' . $email . '<br>Telefon: ' . $phone . '<br><br>İyi çalışmalar dileriz.';
$response2 = sendEmail("tolga@fenoyuncak.com", "fenoyuncak.com", $body, $attachment);

if($response == 1 && $response2 == 1) {
	echo 1;
}
else {
	echo 0;
}

function sendEmail($email, $name, $body, $attachment = null) {
			
	try {
		$mail = new PHPMailer();
		$mail->IsSMTP();

		$mail->SMTPAuth = true;
		$mail->Host = 'mail.fenoyuncak.com';
		$mail->Port = 587;
		$mail->Username = 'tolga@fenoyuncak.com';
		$mail->Password = 'Fenoyuncak123456';

		$mail->SetFrom("tolga@fenoyuncak.com", 'fenoyuncak.com');
		$mail->AddAddress($email, $name);
		$mail->CharSet = 'UTF-8';
		$mail->Subject = 'Teklif - fenoyuncak.com';
		$mail->IsHTML(true);
		$mail->MsgHTML($body);

		if(is_array($attachment)) {
			
			for($i = 0; $i < count($attachment); $i++) {
				
				$mail->AddAttachment($attachment[$i]["path"], $attachment[$i]["name"], 'base64', $attachment[$i]["type"]);

			}

		}

		if($mail->Send()) {
			
			return 1;
					
		} 
		else {
					
			return 0;
					
		}
	
	}
	catch (phpmailerException $e) {
		
	  return $e->errorMessage();
	  
	} 
	catch (Exception $e) {
		
	  return $e->getMessage(); 
	  
	}
	
}

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