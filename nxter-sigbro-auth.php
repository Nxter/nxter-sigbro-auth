<?php
/*
  Plugin Name: NXTER SIGBRO AUTH
  Plugin URI: https://www.nxter.org/sigbro
  Version: 0.2.0
  Author: scor2k 
  Description: Log in via nxt/ardor token
  License: GPLv2 or later.
  License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

?>

<?php

  function post_json($url, $params, $timeout=3) {
      $res = @file_get_contents($url , false, stream_context_create( array(
                  'http' => array(
                      'method' => 'POST',
                      'header' => 'Content-type: application/x-www-form-urlencoded',
                      'content' => http_build_query($params),
                      'timeout' => $timeout
                  )
              )));
      return $res;
  }

  function sigbro_auth__redirect_after_login($redirect_to, $request, $user) {

    $prefix = mb_substr($user->user_login, 0, 5);
    if ( $prefix == 'ARDOR' || $prefix == 'ardor' )  { 

      if ( strlen( get_the_author_meta( 'sigbro_email', $user->ID ) ) > 0 ) {
        // user already set email
        return home_url('/', 'relative');
      } else {
        return home_url('sigbro-profile', 'relative');
      }

    } else {
      return false;
    }
  }

	/* set nxter logo */
  function sigbro_auth__custom_login_logo() { ?>
      <style type="text/css">
          #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/nxter-logo.png);
            height:109px;
            width:110px;
            background-size: 320px 65px;
            background-repeat: no-repeat;
            padding-bottom: 30px;
          }
      </style>
  <?php }

  add_action( 'login_enqueue_scripts', 'sigbro_auth__custom_login_logo' );

  /* set custom url in logo */
  function sigbro_auth__custom_login_url() {
    return home_url();
  }
  add_filter( 'login_headerurl', 'sigbro_auth__custom_login_url' );


  add_action( 'login_form', 'sigbro_auth__custom_login_form' );
  add_filter( 'authenticate', 'sigbro_auth__custom_authenticate', 10 , 3); 
  add_filter( 'login_redirect', 'sigbro_auth__redirect_after_login', 10, 3);
  

  $random = rand();

  wp_register_style('nxtbridge-bootstrap', plugins_url('css/nxtbridge-bootstrap.min.css', __FILE__), '', '1.0.0', 'all');
  wp_enqueue_style('nxtbridge-bootstrap');

  wp_register_style('awesome', plugins_url('css/font-awesome.min.css', __FILE__), '', '1.0.0', 'all');
  wp_enqueue_style('awesome');

  wp_enqueue_script('nxter-qrcodegen', plugins_url('js/qrcodegen.js', __FILE__), array(), '1.0.0', true); // in footer
  wp_enqueue_script('nxter-sigbro-auth', plugins_url('js/nxter-sigbro-auth.js', __FILE__), array('jquery'), $random, true); // in footer

  function sigbro_auth__custom_login_form() {
      $uuid =   htmlspecialchars($_COOKIE["sigbro_uuid"]);
      $token =  htmlspecialchars($_COOKIE["sigbro_token"]);
  ?>
      <input type="hidden" name="sigbro_auth__uuid" id="sigbro_auth__uuid" class="input" value="<?php echo $uuid; ?>" />
      <input type="hidden" name="sigbro_auth__token" id="sigbro_auth__token" class="input" value="<?php echo $token; ?>" />

  <?php
      if ( strlen($uuid) > 20 && strlen($token) > 100 ) {
  ?>
    <script>
      function click_wp_submit() {
        var button = document.getElementById("wp-submit");
        button.click();
      }
      
      setTimeout(click_wp_submit, 100);
    </script>
  
  <?php
      }
  ?>

  <?php
  }

  function sigbro_auth__custom_authenticate ($user, $username, $password) {
    if ( is_a($user, 'WP_User') ) { return $user; }
    if ( isset( $_POST['sigbro_auth__token'] ) && isset( $_POST['sigbro_auth__uuid'] ) ) { 
      # clear cookie
      setcookie("sigbro_uuid", "", time()-3600);
      setcookie("sigbro_token", "", time()-3600);
      // get post data
      $uuid =   htmlspecialchars($_POST['sigbro_auth__uuid']);
      $token =  htmlspecialchars($_POST['sigbro_auth__token']);

      $url = "https://random.nxter.org/ardor";

      $params = array(
              'requestType' => 'decodeToken',
              'website' => $uuid,
              'token' => $token
            );
 
      $res = post_json($url, $params); // decode token

      if ( ! $res ) { 
        return new WP_Error('valid_username', 'Can not check your token. Sorry.' );
        die();
      } 
      else { 
        $res = json_decode($res, true); 
      }

      if ( isset($res['accountRS']) && isset($res['valid']) && $res['valid'] == True ) {
        $acc = $res['accountRS'];


        if ( username_exists($acc) ) {
          return get_user_by('login', $acc);

        } else {
          $password = openssl_digest($token, 'sha512');
          wp_create_user( $acc, $password, '' );
          return get_user_by('login', $check);
        }
      } 
      else { 
        return new WP_Error('valid_username', 'Token is not valid' );
      }

    } // end isset 
    return;
  }

  ////////////////////////////////// CUSTOM FIELD TO PROFILE

	add_action( 'show_user_profile', 'sigbro_auth__extra_field' );
	add_action( 'edit_user_profile', 'sigbro_auth__extra_field' );

	function sigbro_auth__extra_field( $user ) { ?>
      <?php 
      $prefix = mb_substr($user->user_login, 0, 5);
      if ( $prefix == 'ARDOR' || $prefix == 'ardor' )  { 
      } else {
        return false;
      }


      $readonly = "";
      if ( strlen( get_the_author_meta( 'sigbro_email', $user->ID ) ) > 0 ) {
        $readonly = "readonly";
      }
      ?>

			<h3><?php _e("SIGBRO Profile information", "blank"); ?></h3>

			<table class="form-table">
			<tr>
					<th><label for="SIGBRO Email"><?php _e("SIGBRO Email"); ?></label></th>
					<td>
							<input type="email" name="sigbro_auth--email" id="sigbro_auth--email" value="<?php echo esc_attr( get_the_author_meta( 'sigbro_email', $user->ID ) ); ?>" class="regular-text" <?php echo $readonly; ?> /><br />
							<span class="description"><?php _e("Please enter your email address for SIGBRO account."); ?></span>
					</td>
			</tr>
			</table>
	<?php }



	add_action( 'personal_options_update', 'sigbro_auth__save_extra_field' );
	add_action( 'edit_user_profile_update', 'sigbro_auth__save_extra_field' );

	function sigbro_auth__save_extra_field( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
      return false; 
    }
		if ( strlen( get_the_author_meta( 'sigbro_email', $user->ID ) ) > 0 ) {
      return false; 
    }
    update_user_meta( $user_id, 'sigbro_email', $_POST['sigbro_auth--email'] );
	}

?>
