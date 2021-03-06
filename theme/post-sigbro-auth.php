<?php
/*
Template Name: SIGBRO Auth
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
?>

<?php get_header('sigbor');?>

<?php

function gen_uuid()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

//////////////// First of all we should check if POST was send
if (isset($_COOKIE['sigbro_uuid']) && isset($_COOKIE['sigbro_token'])) {
    // cookies are set, but redirect not workig
    echo "<a href='/wp-login.php' class='' target=_self>Click to login</a>";

} else {
    ////////////////////////////////// START ELSE

    if (!isset($_SESSION['sigbro_uuid']) && !isset($_SESSION['sigbro_uuid_timestamp'])) {
        $_SESSION['sigbro_uuid'] = gen_uuid();
        $_SESSION['sigbro_uuid_timestamp'] = round(microtime(true) * 1000);
    }

    $delta = round(microtime(true) * 1000) - $_SESSION['sigbro_uuid_timestamp'];

    if ( isset($_COOKIE['sigbro_token_refresh']) ) { 
      $delta = 1000000;
      unset($_COOKIE['sigbro_token_refresh']);
    }

    if ($delta > 5 * 60 * 1000) {
        $_SESSION['sigbro_uuid'] = gen_uuid();
        $_SESSION['sigbro_uuid_timestamp'] = round(microtime(true) * 1000);
    }
    ?>
<article id="post-<?php the_ID();?>" <?php post_class();?> >
	<div class="inside-article">

  <input type="hidden" id="sigbro_auth--session_uuid" value="<?php echo $_SESSION['sigbro_uuid']; ?>" />

  <header class="entry-header">
    <?php the_title('<h1 class="entry-title" itemprop="headline">', '</h1>');?>
  </header><!-- .entry-header -->
  <?php
do_action('generate_after_entry_header');
    ?>

		<div class="entry-content" itemprop="text">

    <div class="nb row" style="margin-bottom: 1rem;">
      <div class="nb col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
        <center>
        <div class="nb alert-success hidden-lg" style="padding: 10px 10px 10px 10px; margin-bottom: 10px;" id=""><center><a href="sigbro://<?php echo $_SESSION['sigbro_uuid']; ?>" class="nb btn btn-danger hidden-lg">OPEN IN SIGBRO MOBILE</a></center></div>
        <div class="nb alert-success" style="padding: 0 10px 0 10px; " id=""><center>Scan this QR code via SIGBRO Mobile App</center></div>
          <div class="nb input-group">
              <svg xmlns="http://www.w3.org/2000/svg" id="sigbro_auth--qr_code_sigbromobile" style="width:20em; height: 20em; display:none">
                          <rect width="100%" height="100%" fill="#ffffff"></rect>
                          <path d fill="#000000"></path>
              </svg>
          </div>
        </center>
        <div class="nb alert-info" style="padding: 0 10px 0 10px; " id=""><center>Or <a href="https://nxter.github.io/ARDOR-SigBro-Offline/" target=_blank style="text-decoration: underline;">create</a> token for this key: '<?php echo $_SESSION['sigbro_uuid']; ?>' by yourself.</center></div>
      </div>
    </div>

    <div class="nb row" style="margin-bottom: 2rem;">
      <div class="nb col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2"  style="margin-top: 10px;">
        <div class="nb input-group">
          <span class="nb input-group-addon" id="basic-addon1">Token:</span>
          <input type="password" class="nb form-control" id="sigbro_auth--auth_token" placeholder="Token, created for the key you can see above ^^ " aria-describedby="basic-addon1">
          <span class="input-group-btn">
            <input class="nb btn btn-default" id="sigbto_auth--auth_button" type="button" value="Login" onclick="sigbro_auth_login_click()" />
          </span>
          <span class="input-group-btn">
            <input class="nb btn btn-default" id="sigbto_auth--refresh_token" type="button" value="Refresh" onclick="sigbro_auth_refresh_click()" />
          </span>
        </div>
        <input type="hidden" id="sigbro_auth--auth_uuid" value="<?php echo $_SESSION['sigbro_uuid']; ?>" />
      </div>
    </div>

    </div>


    <script>
			function sigbro_callback() {
				var resp = this.responseText;
				var resp_j = JSON.parse(resp);

        if ( resp_j.result == 'fail' ) {
          sessionStorage.removeItem("sigbro_uuid_timestamp");
          alert (resp_j.msg);
        }

				console.log(resp_j);
			}


			function sigbro_sendJSON(url, params, timeout, callback) {
				var args = Array.prototype.slice.call(arguments, 3);
				var xhr = new XMLHttpRequest();
				xhr.ontimeout = function () {
					console.log("The POST request for " + url + " timed out.");
				};
				xhr.onload = function() {
					if (xhr.readyState === 4) {
						if (xhr.status === 200) {
							console.log('post: ' + url + ' success.');
							callback.apply(xhr, args);
						} else {
							console.log(xhr.statusText);
						}
					}
				};
				xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
				xhr.timeout = timeout;
				xhr.send(params);
			}


      function sigbro_auth_login_click() {
        var _uuid = document.getElementById("sigbro_auth--auth_uuid").value;
        var _token = document.getElementById("sigbro_auth--auth_token").value;
				var url = "https://random.nxter.org/api/auth/update";
        var params = { uuid : _uuid, token : _token };
        sigbro_sendJSON( url, JSON.stringify(params), 3000, sigbro_callback );
        sessionStorage.removeItem("sigbro_uuid_timestamp");
      }


      function sigbro_auth_refresh_click() {
        document.cookie = "sigbro_token_refresh" + "=" + "pleeease" + "; path=/";
        location.reload();
      }
    </script>

		<div class="entry-content" itemprop="text">
			<?php the_content();?>
		</div><!-- .entry-content -->

		<?php
do_action('generate_after_content');
    ?>
	</div><!-- .inside-article -->
</article><!-- #post-## -->


<?php
////////////////////////////////// END ELSE
}
?>

<?php wp_reset_postdata();?>

<?php
get_footer('sigbro');
?>


