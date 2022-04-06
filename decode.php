
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

</head>
<style>
input[type=text] {
  width: 50%;
  padding: 15px 22px;
  margin: 10px 5px;
  box-sizing: border-box;
  text:bold;


}
h3{
  width: 50%;
  background-color: white;
}
h1{
  width: 50%;
  background-color: #f67280;
}

body{
  background-color:white;
}

input[type=button], input[type=submit], input[type=reset] {
  background-color: #b80d57;
  border: none;
  color: white;
  padding: 16px 32px;
  text-decoration: none;
  margin: 4px 2px;
  cursor: pointer;

}

</style>
<center>
<br><br><br>
<form action="decode.php" method="post">
  <h1>LSB HIDDEN MESSAGE DECODING</h1>
  <br><br><br>
  

  </div>
<input type="text" name="output" placeholder="Enter Key Used In Encoding Process To Get Hidden Message" pattern="[A-Za-z]{1,9}" title=" only alphabets">
<input type="submit" name="out">
    </form>
    </center>
<br><br><br>
<?php

include('vigenere.php');



$INTEGER_BITS = 32;
$src = 'encoded.png';
$image = imagecreatefrompng($src);

$size = getimagesize($src);
$width = $size[0];
$height = $size[1];
if(isset($_POST['out']))
{
   $po= $_POST['output'];


// Returns the message length in bits as an integer.
function decodeMessageLength($image, $width, $height) {
    // We need to process the first 32 LSB's of the image to retrieve the int.
    $numOfBits = 32;
    $bitIndex = 0;
    $binaryMessageLength = 0;
    for($y = 0; $y < $height; $y++) {
        for($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            // We extract each component's LSB by simply ANDing with 1.
            $r = ($rgb >> 16) & 1;
            $g = ($rgb >> 8) & 1;
            $b = $rgb & 1;

            $binaryMessageLength = ($bitIndex++ < $numOfBits) ? (($binaryMessageLength << 1) | $r) : $binaryMessageLength;
            $binaryMessageLength = ($bitIndex++ < $numOfBits) ? (($binaryMessageLength << 1) | $g) : $binaryMessageLength;
            $binaryMessageLength = ($bitIndex++ < $numOfBits) ? (($binaryMessageLength << 1) | $b) : $binaryMessageLength;

            if($bitIndex >= $numOfBits) {
                return $binaryMessageLength;
            }
        }
    }
}

function decodeBinaryMessage($image, $width, $height, $offset, $messageLength) {
    $offsetRemainder = $offset % 3;
    // We get 3 bits for each pixel, so the offset needs to be divided by 3.
    $offset /= 3;
    // Instead of looping through all the pixels, an offset is used for the starting indices.
    $line = $offset / $width;
    $col = $offset % $width;
    $binaryMessage = '';
    $bitIndex = 0;
    for($y = $line; $y < $height; $y++) {
        for($x = $col; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            // We extract each component's LSB by simply ANDing with 1.
            $r = ($rgb >> 16) & 1;
            $g = ($rgb >> 8) & 1;
            $b = $rgb & 1;

            // Depending on the remainder, we will start with a different LSB.
            if($offsetRemainder == 1) {
                $binaryMessage .= $g;
                $binaryMessage .= $b;
                $offsetRemainder = 0;
                $bitIndex += 2;
            } else if($offsetRemainder == 2) {
                $binaryMessage .= $b;
                $offsetRemainder = 0;
                $bitIndex++;
            } else {
                // As long as the bit index is lower than the length of the message, concatenate each component's LSB to the message.
                $binaryMessage = ($bitIndex++ < $messageLength) ? ($binaryMessage.$r) : $binaryMessage;
                $binaryMessage = ($bitIndex++ < $messageLength) ? ($binaryMessage.$g) : $binaryMessage;
                $binaryMessage = ($bitIndex++ < $messageLength) ? ($binaryMessage.$b) : $binaryMessage;

                if($bitIndex >= $messageLength) {
                    return $binaryMessage;
                }
            }
        }
    }
}
$decodedMessageLength = decodeMessageLength($image, $width, $height);
$decodedBinaryMessage = decodeBinaryMessage($image, $width, $height, $INTEGER_BITS, $decodedMessageLength);

$decodedMessage = implode(array_map('chr', array_map('bindec', str_split($decodedBinaryMessage, 8))));

$output=decrypt($po,$decodedMessage);

echo "<center>";



echo "<h3>HIDDEN MESSAGE IS: $output</h3>";


echo "</center>";
imagedestroy($image);
}

?>
