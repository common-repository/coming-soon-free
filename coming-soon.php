<?php
if ( ! is_front_page() ) {
    wp_safe_redirect( '/' );
    exit;
}
$options = get_option( 'tv_coming_soon' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<title><?php echo esc_html( $options['h1'] ); ?></title>
<meta name="robots" content="index,follow">
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo esc_html( $options['h1'] ); ?>">
<meta property="og:description" content="<?php echo esc_attr( sanitize_text_field( $options['description'] ) ); ?>">
<meta property="og:url" content="<?php echo site_url( '/' ); ?>">
<meta property="og:site_name" content="<?php bloginfo( 'name' ); ?>">
<link rel="stylesheet" href="<?php echo plugins_url( 'css/style.css', __FILE__ ); ?>" type="text/css">
<style type="text/css">
.overlay {
    background-image: url(<?php echo ! empty( $options['bg_img'] ) ? wp_get_attachment_image_url( $options['bg_img'], 'large' ) : plugins_url( 'images/red-curtain.jpg', __FILE__ ); ?>);
    opacity: <?php echo $options['bg_opacity']; ?>;
}
</style>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
<link rel="canonical" href="<?php echo site_url( '/' ); ?>">
<?php if ( defined( 'TV_ANALYTICS' ) && TV_ANALYTICS ) : ?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
ga('create', '<?php echo TV_ANALYTICS; ?>', 'auto');
ga('send', 'pageview');
</script>
<?php endif; ?>
</head>
<body>
<div class="page">
    <h1><?php echo esc_html( $options['h1'] ); ?></h1>
    <?php if ( ! empty( $options['h2'] ) ) : ?>
        <div class="subtitle"><?php echo esc_html( $options['h2'] ); ?></div>
    <?php endif; ?>
    <div class="social">
        <?php
        if ( ! empty( $options['social_facebook'] ) ) {
            echo '<a href="' . esc_url( $options['social_facebook'] ) . '" target="_blank" title="Like us on Facebook"><i class="fa fa-facebook-official" aria-hidden="true"></i></a>';
        }
        if ( ! empty( $options['social_twitter'] ) ) {
            echo '<a href="' . esc_url( $options['social_twitter'] ) . '" target="_blank" title="Follow us on Twitter"><i class="fa fa-twitter" aria-hidden="true"></i></a>';
        }
        if ( ! empty( $options['social_google'] ) ) {
            echo '<a href="' . esc_url( $options['social_google'] ) . '" target="_blank" title="Join us on Google+"><i class="fa fa-google-plus-official" aria-hidden="true"></i></a>';
        }
        ?>
    </div>
    <div id="wrap">
        <div class="left<?php if ( ! $options['map_show'] ) echo ' no-map'; ?>">
            <?php if ( !empty( $options['h3'] ) ) : ?>
                <h2 id="coming-soon"><?php echo esc_html( $options['h3'] ); ?></h2>
            <?php endif; ?>
            <?php echo apply_filters( 'the_content', $options['description'] ); ?>
            <?php if ( $options['form_show'] ) : ?>
                <form action="/" method="post">
                    <div class="fright">
                        <input type="submit" value="<?php echo esc_attr( $options['form_button'] ); ?>" tabindex="2">
                        <img src="<?php echo plugins_url( 'images/spinner.svg', __FILE__ ); ?>" alt="Loading" class="spinner">
                    </div>
                    <div class="fleft">
                        <input type="email" name="email" placeholder="<?php echo esc_attr( $options['form_email'] ); ?>" tabindex="1" required>
                    </div>
                    <div class="clear name">
                        <input type="text" name="name">
                    </div>
                </form>
            <?php endif; ?>
            <?php if ( $options['login_show'] || $options['credit_show'] ) : ?>
                <div id="footer">
                    <?php if ( $options['login_show'] ) : ?>
                        <a href="<?php echo esc_url( $options['login_url'] ); ?>"><?php echo esc_html( $options['login_text'] ); ?></a>
                    <?php endif; ?>
                    <?php if ( $options['credit_show'] ) : ?>
                        <a href="<?php echo esc_url( $options['credit_url'] ); ?>"><?php echo esc_html( $options['credit_text'] ); ?></a>
                    <?php endif; ?>
                </div>
                <?php if ( $options['credit_show'] && class_exists( 'TourVistaAdminPanel' ) ) : ?>
                    <div id="logo">
                        <a href="<?php echo esc_url( $options['credit_url'] ); ?>" title="<?php echo esc_attr( $options['credit_text'] ); ?>"><?php echo ! empty( $options['credit_img'] ) ? wp_get_attachment_image( $options['credit_img'] ) : '<img src="' . plugins_url( 'images/powered-by-tourvista.png', __FILE__ ) . '" alt="TourVista" width="143" height="43" border="0">'; ?></a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if ( $options['map_show'] ) : ?>
            <div class="right">
                <iframe id="map" scrolling="no" frameborder="0" src="https://maps.google.com/maps?q=<?php echo urlencode( $options['map_address'] ); ?>&amp;key=<?php echo urlencode( $options['map_key'] ); ?>&amp;z=14&amp;output=embed"></iframe>
        	</div>
            <div class="clear"></div>
        <?php endif; ?>
    </div>
</div>
<div class="overlay"></div>

<?php if ( $options['form_show'] ) : ?>
    <?php global $wp_scripts; ?>
    <script src="<?php echo $wp_scripts->base_url . $wp_scripts->registered['jquery-core']->src; ?>"></script>
    <script src="<?php echo $wp_scripts->base_url . $wp_scripts->registered['jquery-migrate']->src; ?>"></script>
    <script>
    jQuery(document).ready(function($) {
        $('form').submit(function() {
            var $button = $('input[type=submit]'), $spinner = $('.spinner'), $form = $(this);
            $button.hide();
            $spinner.show();
            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                action: 'submit_email',
                data: $form.serialize(),
                tv: '<?php echo wp_create_nonce( 'tourvistababy' ); ?>'
            }, function(res) {
                if (res) {
                    $form.html('<p class="success">Success! You will be notified once our new website goes live.</p>');
                } else {
                    $form.find('.error').remove();
                    $form.append('<p class="error">Please enter a valid email address.</p>');
                    $button.show();
                    $spinner.hide();
                }
            });
            return false;
        });
    });
    </script>
<?php endif; ?>
</body>
</html>