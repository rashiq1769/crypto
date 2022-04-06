


<center>

<style>
input[type=text] {
  width: 80%;
  padding: 15px 22px;
  margin: 10px 5px;
  box-sizing: border-box;


}

h1{
  background-color: #f8615a;
}

body{
  background-color:white;
}

input[type=button], input[type=submit], input[type=reset] {
  background-color: #000000;
  border: none;
  color: white;
  padding: 16px 32px;
  text-decoration: none;
  margin: 4px 2px;
  cursor: pointer;

}
</style>

<br><br>
<table>


<hr height="3px" color="black">

<h1> STEGANOGRAPHY USING IMAGES</h1>

<hr height="3px" color="black">


<form action="encode.php" method="post" enctype="multipart/form-data">
<td>

  <h3>ENTER KEY</h3>

<input type="text" name="user" pattern="[A-Za-z]{1,9}"title=" only alphabets" >
</td>
<tr>

<td>

<h3>ENTER MESSAGE</h3>
<input type="text" name="userb"  >
<br>
</td>
</tr>
<tr>
  <td>
    <input type='file' name='file' />
  </td>
  <br><br>
  <br>
  <tr>

  </tr>
  <td>
    <center>
<br><br>
    
<input type="submit" name="submit" value="Encode Message"  style="font-size : 20px; width: 100%; height: 50px;">
</center>


</td>
<tr>

  <br><br><br>
<td>

<br><br>

</td>
</tr>

</tr>
  </form>
</table>



</center>

<?php

include('functions.php');
include('vigenere.php');

if(isset($_POST['but_upload'])){

  $name = $_FILES['file']['name'];
  $target_dir = "upload/";
  $target_file = $target_dir . basename($_FILES["file"]["name"]);

  // Select file type
  $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

  // Valid file extensions
  $extensions_arr = array("jpg","jpeg","png","gif");

  // Check extension
  if( in_array($imageFileType,$extensions_arr) ){

     // Insert record
     $query = "insert into images(name) values('".$name."')";
     mysqli_query($con,$query);

     // Upload file
     move_uploaded_file($_FILES['file']['tmp_name'],$name);

  }

}
// Number of bytes in an integer.
$INTEGER_BYTES = 4;
$BYTE_BITS = 8;

if (isset ($_POST['submit'] )){
  $code22=$_POST['user'];
  $code21=$_POST['userb'];

  	$code1 = encrypt($code22, $code21);


$message=$code1;



$binaryMessage = toBinary($message);
// The number of bits contained in the message, aka the size of the payload as an integer.
$messageLength = strlen($binaryMessage);
// Convert the length to binary as well and make sure to pad it with 32 0's.
$binaryMessageLength = str_pad(decbin($messageLength), $INTEGER_BYTES * $BYTE_BITS, "0", STR_PAD_LEFT);


// The payload will incorporate the length and the message.
$payload = $binaryMessageLength.$binaryMessage;

$src = 'wilderness.jpg';
$image = imagecreatefromjpeg($src);

$size = getimagesize($src);
$width = $size[0];
$height = $size[1];

function encodePayload(string $payload, $image, $width, $height) {
    $payloadLength = strlen($payload);
    // We are able to store 3 bits per pixel (1 LSB for each color channel) times the width, times the height.
    if($payloadLength > $width * $height * 3) {
        echo "Image not big enough to hold data.";
        return false;
    }
    $bitIndex = 0;
    for($y = 0; $y < $height; $y++) {
        for($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            // Each color channel's value is extracted from the original integer.
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            // LSB's are cleared by ANDing with 0xFE and filled by ORing with the current payload bit, as long as the payload length isn't hit.
            $r = ($bitIndex < $payloadLength) ? (($r & 0xFE) | $payload[$bitIndex++]) : $r;
            $g = ($bitIndex < $payloadLength) ? (($g & 0xFE) | $payload[$bitIndex++]) : $g;
            $b = ($bitIndex < $payloadLength) ? (($b & 0xFE) | $payload[$bitIndex++]) : $b;

            $color = imagecolorallocate($image, $r, $g, $b);
            imagesetpixel($image, $x, $y, $color);

            if($bitIndex >= $payloadLength) {
                return true;
            }
        }
    }
}

if(encodePayload($payload, $image, $width, $height)) {


} else {
    echo 'Something went wrong.'.'<br>';
}

imagepng($image, 'encoded.png');
imagedestroy($image);
echo "<center>";
echo '<hr height="2px" color="black">';
echo "<h3>ENCODED IMAGE</h3>";
echo '<hr height="2px" color="black">';
echo '<img src="encoded.png"/>';
echo "</center>";

echo '
<center>


<hr>
<a href="decode.php"><input type="submit" value="Click Here To Perfom Decode Operation" style="font-size : 30px; width: 100%; height: 100px;">
</a>
</center>';

}



?>
