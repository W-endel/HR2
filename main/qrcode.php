<?php
   include('../phpqrcode/qrlib.php');

   $tempDir = "C:/xampp/htdocs/HR2/QR/";  // Absolute path to QR directory with trailing slash

   // Create directory if it does not exist
   if (!file_exists($tempDir)) {
       if (mkdir($tempDir, 0777, true)) {
           echo 'Directory created successfully!';
       } else {
           die('Failed to create directory.');
       }
   }

   $codeContents = 'Employee6 Try';
   $fileName = '005_file_' . md5($codeContents) . '.png';
   $pngAbsoluteFilePath = $tempDir . $fileName;
   $urlRelativeFilePath = $tempDir . $fileName;  // Relative path for display

   // Generate QR code if file does not exist
   if (!file_exists($pngAbsoluteFilePath)) {
       QRcode::png($codeContents, $pngAbsoluteFilePath);
       echo 'File generated!';
   } else {
       echo 'File already generated!';
   }

   echo 'Server PNG File: ' . $pngAbsoluteFilePath;
   echo '<img src="' . $urlRelativeFilePath . '" />';
?>
