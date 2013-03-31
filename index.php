<?php
/*
 * Test des librairies de traitement d'images
 */

if (!extension_loaded('imagick') OR !extension_loaded('gd')) {
	echo '<pre>Pour pouvoir générer les vignettes de cette galerie, il faut <i>Imagick</i> ou <i>GD</i> sur le serveur.<br />Contactez l\'administrateur.</pre>';
} else {


/*
 * Configuration
 */

$titre			= 'Ma super gallerie!';
$intro			= 'Blah Blah Blah Blah...';
$repertoire		= 'images';
$dir			= $repertoire . '/';
$thumb_dir		= 'thumbs/';
$thumb_prefix	= 'thumb_';


/*
 * Initialisation des variables
 */

$files			= scandir($dir);
$images			= array();
$liste_image	= '';


/*
 * Fonctions
 */

function makeThumb($dir, $thumb_dir, $thumb_prefix, $file)
{
	if (!extension_loaded('imagick')) {
		// fabrication d'une vignette avec Image Magick
		$img = new Imagick($dir.$file);
		$img->scaleImage(90,0);
		$d = $img->getImageGeometry();
		$h = $d['height'];
		if($h > 60) {
			$img->scaleImage(0,60);
		}
		$img->setImageCompression(Imagick::COMPRESSION_JPEG);
		$img->setImageCompressionQuality(70);
		// Strip out unneeded meta data
		$img->stripImage();

//[TODO] creer le repertoire des vignettes si il n'existe pas
		$img->writeImage($dir.$thumb_dir.$thumb_prefix.$file);
		$img->destroy();

	} else {

//[TODO] fabrication d'une vignette avec GD
		// Définition de la largeur et de la hauteur maximale
		$width = 90;
		$height = 90;

		// Cacul des nouvelles dimensions
		list($width_orig, $height_orig) = getimagesize($filename);

		$ratio_orig = $width_orig/$height_orig;

		if ($width/$height > $ratio_orig) {
		   $width = $height*$ratio_orig;
		} else {
		   $height = $width/$ratio_orig;
		}

		// Redimensionnement
		$image_p = imagecreatetruecolor($width, $height);
		$image = imagecreatefromjpeg($filename);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	}
}


/*
 * Script
 */

// On ne recupere que les fichier de type MIME 'image'
$finfo = finfo_open(FILEINFO_MIME_TYPE); // Retourne le type mime à la extension mimetype
foreach($files as $file) {

    if(strstr(finfo_file($finfo, $dir.$file), '/', true) == 'image') {

		$images[] = $file;

		// on verifie:
		// - que la vignette correspondante existe
		// - que la vignette est plus recente que le fichier
		if(!file_exists($dir.$thumb_dir.$thumb_prefix.$file) OR filemtime($dir.$thumb_dir.$thumb_prefix.$file) < filemtime($dir.$file)) {

			makeThumb($dir, $thumb_dir, $thumb_prefix, $file);
			echo '<pre>' . $file . ': vignette reg&eacute;n&eacute;r&eacute;e!' . '</pre>' ;
		}

	/* DEBUG
		echo $file . ': ok!' . '</pre>' ;
	} else {
		echo '<pre>' . $file . ': pas une image!' . '</pre>' ;
	//*/
	}

}
finfo_close($finfo);

/* DEBUG
echo '<pre>';
print_r($images);
echo '</pre>';
*/

// construction de la liste html des images (avec vignette)
if(count($images)<>0) {

	for($j = 0; $j < count($images); $j++) {
		$url_fichier = utf8_decode($dir . $images[$j]);
		$url_thumb = utf8_decode($dir . $thumb_dir . $thumb_prefix . $images[$j]);
		$liste_image .= '			<li>' . PHP_EOL;
		$liste_image .= '				<a href="' . $url_fichier .'">' . PHP_EOL;
		$liste_image .= '					<img src="' . $url_thumb .'" title="'. $images[$j] .'" class="image">' . PHP_EOL;
		$liste_image .= '				</a>' . PHP_EOL;
		$liste_image .= '			</li>' . PHP_EOL;
	}

}

?>

<!DOCTYPE HTML>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="lib/jquery.ad-gallery.css">
  <link rel="stylesheet" type="text/css" href="style/ad-gallery.css">

  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
  <script type="text/javascript" src="lib/jquery.ad-gallery.js"></script>
  <script type="text/javascript" src="lib/local.ad-gallery.js"></script>

  <title><?php echo $titre; ?> | <?php echo $repertoire; ?></title>
</head>
<body>
  <div id="container">
    <h1><?php echo $titre; ?></h1>
	<p><?php echo $intro; ?></p>

    <div id="gallery" class="ad-gallery">
      <div class="ad-image-wrapper">
      </div>
      <div class="ad-controls">
      </div>
      <div class="ad-nav">
        <div class="ad-thumbs">
          <ul class="ad-thumb-list">

<?php	echo $liste_image;	?>

        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
}