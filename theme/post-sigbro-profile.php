<?php 
/*
Template Name: SIGBRO Profile
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! is_user_logged_in() ) {
  $redirect_url = htmlspecialchars(get_site_url() . "/sigbro-auth/");
  wp_redirect($redirect_url);
} else {
  global $current_user;
  get_currentuserinfo();

	$prefix = mb_substr($current_user->user_login, 0, 5);
	if ( $prefix == 'ARDOR' || $prefix == 'ardor' )  {
	} else {
		$redirect_url = htmlspecialchars(get_site_url() . "/wp-admin/profile.php");
		wp_redirect($redirect_url);
	}

  $user_id = get_current_user_id();
  $user_email = get_the_author_meta('sigbro_email', $user_id);

  $display_name = $current_user->display_name;

  // check for post 
  if ( isset( $_POST['sigbro_auth_profile--displayname'] ) ) {
    $userid = wp_update_user( array( 'ID' => $user_id, 'nickname' => htmlspecialchars($_POST['sigbro_auth_profile--displayname']), 'display_name' => htmlspecialchars($_POST['sigbro_auth_profile--displayname'])  ) );
    if ( ! is_wp_error( $userid ) ) { 
      $display_name = htmlspecialchars($_POST['sigbro_auth_profile--displayname']);
    }
  }

  // check for post 
  if ( strlen($user_email) == 0 && isset( $_POST['sigbro_auth_profile--email'] ) ) {
    if ( filter_var($_POST['sigbro_auth_profile--email'], FILTER_VALIDATE_EMAIL) ) {
      update_user_meta($user_id, 'sigbro_email', $_POST['sigbro_auth_profile--email']);
      $user_email = $_POST['sigbro_auth_profile--email'];
      $_POST['sigbro_auth_profile--email'] = Null;
    } else {
      $user_email = 'Invalid email. Reload page and try again'; 
      $_POST['sigbro_auth_profile--email'] = Null;
    }
  }


}

?>



<?php get_header('sigbor'); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> > 
	<div class="inside-article">

  <input type="hidden" id="sigbro_auth--session_uuid" value="<?php echo $_SESSION['sigbro_uuid']; ?>" />

  <header class="entry-header">
    <?php the_title( '<h1 class="entry-title" itemprop="headline">', '</h1>' ); ?>
  </header><!-- .entry-header -->
  <?php
		do_action( 'generate_after_entry_header' );
  ?>
 
		<div class="entry-content" itemprop="text">
      <h3>Welcome, <?php echo $current_user->user_login ; ?> !</h3>
      <?php 
        $readonly = "";
        if ( strlen($user_email) > 0 ) {
          $readonly = "readonly";
        }

      ?>
      <form method="post">
				<div class="nb form-group">
					<label for="SigbroEmail">SIGBRO Email address</label>
					<input type="email" class="nb form-control" name="sigbro_auth_profile--email" placeholder="Email" value="<?php echo $user_email; ?>" <?php echo $readonly; ?> >
				</div>
				<div class="nb form-group">
					<label for="SigbroUsername">Username</label>
					<input type="text" class="nb form-control" name="sigbro_auth_profile--displayname" placeholder="User name" value="<?php echo $display_name; ?>">
				</div>
        <br>

				<button type="submit" class="nb btn btn-default">Update</button>
      </form>

      <br>
    </div>
		<div class="entry-content" itemprop="text">
			<?php the_content(); ?>
		</div><!-- .entry-content -->

		<?php
		do_action( 'generate_after_content' );
		?>
	</div><!-- .inside-article -->
</article><!-- #post-## -->


<?php wp_reset_postdata(); ?>

<?php 
  get_footer('sigbro'); 
?>


