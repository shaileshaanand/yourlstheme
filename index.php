<?php
include 'header.php';

// Get recaptcha library
require('recaptcha/autoload.php');

// empty response
$response = null;

// init the recaptcha
$reCaptcha = new \ReCaptcha\ReCaptcha(secret);

// if submitted check response
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if ($_POST["g-recaptcha-response"] && $_POST["url"]) {
    $resp = $reCaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);
    if ($resp->isSuccess()) {
        $captcha = true;
    } else {
        $errors = $resp->getErrorCodes();
        if (in_array("invalid-input-secret", $errors)) {
		    $captcha_error = "please make sure your Captcha secret is correct";
		}
		else {
			$captcha_error = var_dump($errors);
		}
    }
  } else {
    $captcha_error = "please make sure URL & Captcha is filled out";
  }
 }

?>

<body>

<?php
	// Start YOURLS engine
	require_once( dirname(__FILE__).'/includes/load-yourls.php' );

	// Change this to match the URL of your public interface.
	$page = YOURLS_SITE . '/index.php' ;

	// Part to be executed if FORM has been submitted
	if ( isset( $_REQUEST['url'] ) && $_REQUEST['url'] != 'http://' ) {

		// Get parameters -- they will all be sanitized in yourls_add_new_link()
		$url     = $_REQUEST['url'];
		$keyword = isset( $_REQUEST['keyword'] ) ? $_REQUEST['keyword'] : '' ;
		$title   = isset( $_REQUEST['title'] ) ?  $_REQUEST['title'] : '' ;
		$text    = isset( $_REQUEST['text'] ) ?  $_REQUEST['text'] : '' ;

		// Create short URL, receive array $return with various information
		$return  = yourls_add_new_link( $url, $keyword, $title );
		
		$shorturl = isset( $return['shorturl'] ) ? $return['shorturl'] : '';
		$message  = isset( $return['message'] ) ? $return['message'] : '';
		$title    = isset( $return['title'] ) ? $return['title'] : '';
		$status   = isset( $return['status'] ) ? $return['status'] : '';
		
		// Stop here if bookmarklet with a JSON callback function ("instant" bookmarklets)
		if( isset( $_GET['jsonp'] ) && $_GET['jsonp'] == 'yourls' ) {
			$short = $return['shorturl'] ? $return['shorturl'] : '';
			$message = "Short URL (Ctrl+C to copy)";
			header('Content-type: application/json');
			echo yourls_apply_filter( 'bookmarklet_jsonp', "yourls_callback({'short_url':'$short','message':'$message'});" );
			die();
		}
	}
?>

<!-- YOURLS errors -->
<?php if ( isset( $_REQUEST['url'] ) && $_REQUEST['url'] != 'http://' ): ?>
   <?php  if (strpos($message,'added') === false): ?>
	    <div id="error" class="alert alert-warning error" role="alert"><h5>Oh no, <?php echo $message; ?>!</h5></div>	    
	<?php endif; ?>
<?php endif; ?>

<!-- Captcha errors -->
<?php  if (isset($captcha_error)): ?>
    <div id="error" class="alert alert-warning error" role="alert"><h5>Oh no, <?php echo $captcha_error; ?>!</h5></div>	    
<?php endif; ?>


	
<?php if( $status == 'success' && $captcha == true ):  ?>

	<?php $url = preg_replace("(^https?://)", "", $shorturl );  ?>

	<section class="success-screen">
		<div class="container verticle-center">
			<div class="main-content">
				<div class="close">
				    <a href="<?php echo siteURL ?>"><i class="fa fa-times fa-2x spin"></i></a>
				</div>
				<section class="head">
					<h2>YOUR SHORTENED LINK:</h2>
				</section>
				<section class="link-section">
					<input type="text" class="short-url" style="text-transform:none;" value="<?php echo $shorturl; ?>">
					<button class="short-url-button" data-clipboard-text="<?php echo $shorturl; ?>">Copy</button>
					<span class="info">View info &amp; stats at <a href="<?php echo $shorturl; ?>+"><?php echo $url; ?>+</a></span>
				</section>
			</div>
	</section>

    <script>
	    var clipboard = new Clipboard('.short-url-button');
	    clipboard.on('success', function(e) {
	        console.log(e);
	    });
	    clipboard.on('error', function(e) {
	        console.log(e);
	    });
    </script>

<?php else: ?>

	<?php $site = YOURLS_SITE; ?>

	<div class="container verticle-center main">
		<div class="main-content">
			<section class="head">
				<img src="<?php echo siteURL ?><?php echo logo ?>" alt="Logo" width="95px" class="logo">
				<p><?php echo description ?></p>
			</section>
			<section class="field-section">
			<form method="post" action="">
				<input type="text" name="url" class="url" placeholder="PASTE URL, SHORTEN &amp; SHARE">
				<button type="button" id="submit" class="submit">Shorten</button>
				<center>
				<div class="g-recaptcha" style="display:none;" data-sitekey="<?php echo publicKey ?>"></div>
				<input type="submit" value="Shorten" class="shorten" style="display:none;">
				</center>
			</form>
			</section>
			<section class="footer">
		<div>
			<span class="light">&copy; <?php echo date("Y"); ?> <?php echo shortTitle ?></span>
			<?php foreach ($footerLinks as $key => $val): ?>
		    	<a href="<?php echo $val ?>"><span><?php echo $key ?></span></a>
			<?php endforeach ?>
		</div>
	</section>
		</div>
	</div>
<?php endif; ?>
<?php
include 'footer.php';
?>