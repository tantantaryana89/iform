<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>
<body>

<?php
use yii\helpers\Html;

// Pecah menjadi halaman 3x3 = 9 item
$pages = array_chunk($models, 9);
$headerColor = '#003399';
?>

<?php foreach ($pages as $page): ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">

<?php
$rows = array_chunk($page, 3);
?>

<?php foreach ($rows as $row): ?>
<tr>

    <?php foreach ($row as $m): ?>
    <td width="33%" valign="top" style="padding:3mm;">

        <!-- KARTU -->
        <table 
              width="100%" 
              cellpadding="3" 
              cellspacing="0"
              style="border:1px solid #000; font-family:Arial; font-size:10px; height:60mm;">
              <!-- HEADER -->
              <tr>
                  <td align="center" style="font-weight:bold; font-size:18px; border-bottom:1px solid #000; color:<?= $headerColor ?>;">
                      PT Chemco Harapan Nusantara<br>
                      ASSEMBLING - KRW
                  </td>
              </tr>

              <tr>
                  <td align="center" style="font-weight:bold; font-size:16px; border-bottom:1px solid #000; padding-bottom:10px; padding-top:10px; color:#000;">
                      DIGITAL FORM MACHINE
                  </td>
              </tr>

              <!-- BODY -->
              <tr>
                  <td style="padding:0;">

                      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">

                          <tr>
                              <!-- LABELS LEFT (FIX WIDTH) -->
                              <td width="22%" 
                                  style="border-right:1px solid #000; border-bottom:1px solid #000; font-weight:bold; padding:2mm; font-size:12px;">
                                  Nama<br>Mesin
                              </td>

                              <!-- VALUE NAMA MESIN -->
                              <td width="38%" 
                                  style="border-bottom:1px solid #000; padding:2mm;">
                                  <?= Html::encode($m->nama_mesin) ?>
                              </td>

                              <!-- QR -->
                              <td rowspan="2" width="40%" align="center" valign="middle" style="padding:1mm;">
                                  <img src="<?= Yii::getAlias('@webroot') . '/' . $m->qr_code_path ?>"
                                      width="36mm" height="36mm"
                                      style="border:1px solid #000;">
                              </td>
                          </tr>

                          <tr>
                              <!-- LABEL LINE -->
                              <td style="border-right:1px solid #000; font-weight:bold; padding:2mm; font-size:12px;">
                                  Line
                              </td>

                              <!-- VALUE LINE -->
                              <td style="padding:2mm;">
                                  <?= Html::encode($m->lokasi) ?>
                              </td>
                          </tr>

                      </table>
                  </td>
              </tr>

          </table>
    </td>
    <?php endforeach; ?>

    <!-- Jika kolom dalam baris kurang dari 3 -->
    <?php for ($i = count($row); $i < 3; $i++): ?>
        <td width="33%"></td>
    <?php endfor; ?>

</tr>
<?php endforeach; ?>

</table>

<!-- PAGE BREAK -->
<?php if ($page !== end($pages)) : ?>
    <pagebreak>
<?php endif; ?>

<?php endforeach; ?>

</body>
</html>
