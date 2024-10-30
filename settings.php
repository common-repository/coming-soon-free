<div class="wrap">
<h1>Coming Soon Settings</h1>
<div class="notice notice-warning"><p>Your site is now in Coming Soon mode. To view it, load your site in a different web browser where you're not logged into WordPress. (Or just view the site in Incognito mode.) To disable Coming Soon mode and go live with your site, just <a href="<?php echo admin_url('plugins.php'); ?>">deactivate this plugin</a>.</p>
<p>Be sure to <a href="https://www.tourvista.com/plugins/coming-soon/#faqs" target="_blank">check out our FAQs</a>, especially the one about submitting your site to Google!</p></div>
<form method="post" action="options.php">
    <?php
    settings_fields( 'tv_coming_soon' );
    do_settings_sections( 'tv_coming_soon' );
    $options = get_option( 'tv_coming_soon' );
    $defaults = $this->get_default_options();

    global $wpdb;
    $sql = "SELECT email FROM {$wpdb->prefix}tv_cs_emails ORDER BY added DESC";
    $result = $wpdb->get_col( $sql );
    $sql = "SELECT * FROM {$wpdb->prefix}tv_cs_emails ORDER BY added DESC";
    $results = $wpdb->get_results( $sql );
    ?>
    <fieldset class="setting_full">
        <legend>Settings</legend>
        <table class="form-table">
        <tr valign="top">
        <th scope="row">After Login</th>
        <td>
            <?php
            $choices = array( '0' => 'Show home page', '1' => 'Show WordPress dashboard' );
            foreach ( $choices as $key => $label ) {
                printf(
                    '<label><input type="radio" name="after_login" value="%s" %s>%s</label> &nbsp; ',
                    $key, checked( $options['after_login'], $key, false ), $label
                );
            }
            ?>
        </td>
        </tr>
        </table>
    </fieldset>
    <fieldset class="setting_full">
        <legend>Background</legend>
        <table class="form-table">
        <tr valign="top">
        <th scope="row">Background Image</th>
        <td><input type="hidden" name="bg_img" value="<?php echo empty( $options['bg_img'] ) ? $defaults['bg_img'] : esc_attr( $options['bg_img'] ); ?>">
            <input id="bg_img" class="upload_image button" type="button" value="Select image">
            <div class="image_preview">
                <div class="<?php echo !empty( $options['bg_img'] ) ? 'has_image' : 'no_image'; ?>">
                    <span class="remove_image" title="Remove image"><span class="dashicons dashicons-no"></span></span>
                    <img src="<?php echo !empty( $options['bg_img'] ) ? wp_get_attachment_image_url( $options['bg_img'] ) : plugins_url( 'images/red-curtain-150x100.jpg', __FILE__ ); ?>" alt="" data-default="<?php echo plugins_url( 'images/red-curtain-150x100.jpg', __FILE__ ); ?>">
                </div>
            </div>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Background Opacity</th>
        <td><input type="number" name="bg_opacity" value="<?php echo empty( $options['bg_opacity'] ) ? $defaults['bg_opacity'] : esc_attr( $options['bg_opacity'] ); ?>" class="small-text" min="0" max="1" step="0.01"></td>
        </tr>
        </table>
    </fieldset>
    <fieldset class="setting_full">
        <legend>Content</legend>
        <table class="form-table">
        <tr valign="top">
        <th scope="row">Title</th>
        <td><input type="text" name="h1" value="<?php echo empty( $options['h1'] ) ? $defaults['h1'] : esc_attr( $options['h1'] ); ?>" class="regular-text"></td>
        </tr>
        <tr valign="top">
        <th scope="row">Subtitle</th>
        <td><input type="text" name="h2" value="<?php echo empty( $options['h2'] ) ? '' : esc_attr( $options['h2'] ); ?>" class="regular-text"></td>
        </tr>
        <tr valign="top">
        <th scope="row">Coming Soon Text</th>
        <td><input type="text" name="h3" value="<?php echo empty( $options['h3'] ) ? '' : esc_attr( $options['h3'] ); ?>" class="regular-text"></td>
        </tr>
        <tr valign="top">
        <th scope="row">Description</th>
        <td>
            <?php
            wp_editor( empty( $options['description'] ) ? '' : $options['description'], 'description', array(
                'media_buttons' => false,
                'textarea_rows' => 5,
                'tinymce' => array(
                    'toolbar1' => 'bold italic underline strikethrough bullist numlist alignleft aligncenter alignright undo redo link unlink',
                    'toolbar2' => ''
                ),
                'quicktags' => array(
                   'buttons' => 'strong,em,link,del,ul,ol,li'
                )
            ) );
            ?>
        </td>
        </tr>
        </table>
    </fieldset>
    <fieldset class="setting_full">
        <legend>Social</legend>
        <table class="form-table">
        <tr valign="top">
        <th scope="row">Facebook</th>
        <td><input type="text" name="social_facebook" value="<?php echo empty( $options['social_facebook'] ) ? $defaults['social_facebook'] : esc_attr( $options['social_facebook'] ); ?>" class="regular-text">
        <p class="description">Enter the full URL, like this: <a href="https://www.facebook.com/TourVista/" target="_blank"><code>https://www.facebook.com/TourVista/</code></a></p></td>
        </tr>
        <tr valign="top">
        <th scope="row">Twitter</th>
        <td><input type="text" name="social_twitter" value="<?php echo empty( $options['social_twitter'] ) ? $defaults['social_twitter'] : esc_attr( $options['social_twitter'] ); ?>" class="regular-text">
        <p class="description">Enter the full URL, like this: <a href="https://twitter.com/tourvista" target="_blank"><code>https://twitter.com/tourvista</code></a></p></td>
        </tr>
        <tr valign="top">
        <th scope="row">Google+</th>
        <td><input type="text" name="social_google" value="<?php echo empty( $options['social_google'] ) ? $defaults['social_google'] : esc_attr( $options['social_google'] ); ?>" class="regular-text">
        <p class="description">Enter the full URL, like this: <a href="https://plus.google.com/+TourvistaBaby" target="_blank"><code>https://plus.google.com/+TourvistaBaby</code></a></p></td>
        </tr>
        </table>
    </fieldset>
    <fieldset class="setting_full">
        <legend>Form</legend>
        <table class="form-table">
        <tr valign="top">
        <th scope="row">Show Form</th>
        <td>
            <?php
            $yes_or_no = array( '1' => 'Yes', '0' => 'No' );
            foreach ( $yes_or_no as $key => $label ) {
                printf(
                    '<label><input type="radio" name="form_show" value="%s" %s>%s</label> &nbsp; ',
                    $key, checked( $options['form_show'], $key, false ), $label
                );
            }
            ?>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Email Label</th>
        <td><input type="text" name="form_email" value="<?php echo empty( $options['form_email'] ) ? $defaults['form_email'] : esc_attr( $options['form_email'] ); ?>" class="regular-text"></td>
        </tr>
        <tr valign="top">
        <th scope="row">Button Label</th>
        <td><input type="text" name="form_button" value="<?php echo empty( $options['form_button'] ) ? $defaults['form_button'] : esc_attr( $options['form_button'] ); ?>" class="regular-text"></td>
        </tr>
        <tr class="spacer"><td colspan="2"></td></tr>
        <tr valign="top">
        <th scope="row">Send Admin Notifications</th>
        <td>
            <?php
            foreach ( $yes_or_no as $key => $label ) {
                printf(
                    '<label><input type="radio" name="form_notify" value="%s" %s>%s</label> &nbsp; ',
                    $key, checked( $options['form_notify'], $key, false ), $label
                );
            }
            ?>
            <p class="description">Send an email to notify admins each time a new lead email is added.</p></td>
        </tr>
        <tr valign="top">
        <th scope="row">Send Notifications To</th>
        <td><input type="email" name="form_to" value="<?php echo empty( $options['form_to'] ) ? $defaults['form_to'] : esc_attr( $options['form_to'] ); ?>" class="regular-text" multiple><p class="description">Separate multiple recipients with a comma, like so: <code>john@example.com, jane@example.com</code></p></td>
        </tr>
        <tr class="spacer"><td colspan="2"></td></tr>
        <tr valign="top">
        <th scope="row">Auto-Email Leads</th>
        <td>
            <?php
            foreach ( $yes_or_no as $key => $label ) {
                printf(
                    '<label><input type="radio" name="email_leads" value="%s" %s>%s</label> &nbsp; ',
                    $key, checked( $options['email_leads'], $key, false ), $label
                );
            }
            ?>
            <p class="description">Send an email to notify leads when the plugin is deactivated.<br>
            <?php
                $subs = count( $result );
                if ( $subs ) {
                    echo $subs != 1 ? 'There are currently <strong>' . $subs . ' subscribers' : 'There is currently <strong>1 subscriber';
                    echo '</strong>:</p>';
                    echo '<ul>';
                    $num_to_show = min( $subs, 5 );
                    for ( $i = 0; $i < $num_to_show; $i++ ) {
                        echo '<li>' . $results[$i]->email . ' (subscribed on ' . $this->datetime_to_html( $results[$i]->added ) . ')</li>';
                    }
                    echo '</ul>';
                    echo '<p class="description"><a href="#TB_inline?width=540&amp;height=320&amp;inlineId=current_subscribers" class="thickbox">View full list of current subscribers</a></p>';
                }
            ?>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Email Subject</th>
        <td><input type="text" name="email_subject" value="<?php echo empty( $options['email_subject'] ) ? $defaults['email_subject'] : esc_attr( $options['email_subject'] ); ?>" class="regular-text"></td>
        </tr>
        <tr valign="top">
        <th scope="row">Email Body</th>
        <td>
            <?php
            wp_editor( empty( $options['email_body'] ) ? $defaults['email_body'] : $options['email_body'], 'email_body', array(
                'media_buttons' => false,
                'textarea_rows' => 10,
                'tinymce' => array(
                    'toolbar1' => 'bold italic underline strikethrough bullist numlist alignleft aligncenter alignright undo redo link unlink',
                    'toolbar2' => ''
                ),
                'quicktags' => array(
                   'buttons' => 'strong,em,link,del,ul,ol,li'
                )
            ) );
            ?>
        </td>
        </tr>
        </table>
    </fieldset>
    <fieldset class="setting_full">
        <legend>Map</legend>
        <table class="form-table">
        <tr valign="top">
        <th scope="row">Show Map</th>
        <td>
            <?php
            foreach ( $yes_or_no as $key => $label ) {
                printf(
                    '<label><input type="radio" name="map_show" value="%s" %s>%s</label> &nbsp; ',
                    $key, checked( $options['map_show'], $key, false ), $label
                );
            }
            ?>
        </td>
        </tr>
        <?php if ( ! defined( 'TV_GMAP_KEY' ) ) : ?>
        <tr valign="top">
        <th scope="row">Google Maps API Key</th>
        <td><input type="text" name="map_key" value="<?php echo empty( $options['map_key'] ) ? $defaults['map_key'] : esc_attr( $options['map_key'] ); ?>" class="regular-text">
        <p class="description">An API key is required to display a map. Follow <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">this guide</a> from Google to get your API key, then enter it here.</p></td>
        </tr>
        <?php endif; ?>
        <tr valign="top">
        <th scope="row">Address</th>
        <td><input type="text" name="map_address" value="<?php echo empty( $options['map_address'] ) ? $defaults['map_address'] : esc_attr( $options['map_address'] ); ?>" class="regular-text"></td>
        </tr>
        </table>
    </fieldset>
    <fieldset class="setting_full">
        <legend>Footer</legend>
        <table class="form-table">
        <tr valign="top">
        <th scope="row">Show Login Link</th>
        <td>
            <?php
            foreach ( $yes_or_no as $key => $label ) {
                printf(
                    '<label><input type="radio" name="login_show" value="%s" %s>%s</label> &nbsp; ',
                    $key, checked( $options['login_show'], $key, false ), $label
                );
            }
            ?>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Login Text</th>
        <td><input type="text" name="login_text" value="<?php echo empty( $options['login_text'] ) ? $defaults['login_text'] : esc_attr( $options['login_text'] ); ?>" class="regular-text"></td>
        </tr>
        <tr valign="top">
        <th scope="row">Login URL</th>
        <td><input type="text" name="login_url" value="<?php echo empty( $options['login_url'] ) ? $defaults['login_url'] : esc_attr( $options['login_url'] ); ?>" class="regular-text"></td>
        </tr>
        <tr class="spacer"><td colspan="2"></td></tr>
        <tr valign="top">
        <th scope="row">Show Credit Link</th>
        <td>
            <?php foreach ( $yes_or_no as $key => $label ) {
                printf(
                    '<label><input type="radio" name="credit_show" value="%s" %s>%s</label> &nbsp; ',
                    $key, checked( $options['credit_show'], $key, false ), $label
                );
            }
            ?>
            <p class="description">We'd really appreciate a shout out on your coming soon page :)</p>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Credit Text</th>
        <td><input type="text" name="credit_text" value="<?php echo empty( $options['credit_text'] ) ? $defaults['credit_text'] : esc_attr( $options['credit_text'] ); ?>" class="regular-text"></td>
        </tr>
        <tr valign="top">
        <th scope="row">Credit URL</th>
        <td><input type="text" name="credit_url" value="<?php echo empty( $options['credit_url'] ) ? $defaults['credit_url'] : esc_attr( $options['credit_url'] ); ?>" class="regular-text"></td>
        </tr>
        <?php if ( class_exists( 'TourVistaAdminPanel' ) ) : ?>
        <tr valign="top">
        <th scope="row">Credit Image</th>
        <td><input type="hidden" name="credit_img" value="<?php echo empty( $options['credit_img'] ) ? $defaults['credit_img'] : esc_attr( $options['credit_img'] ); ?>">
            <input id="credit_img" class="upload_image button" type="button" value="Select image">
            <div class="image_preview">
                <div class="<?php echo !empty( $options['credit_img'] ) ? 'has_image' : 'no_image'; ?>">
                    <span class="remove_image" title="Remove image"><span class="dashicons dashicons-no"></span></span>
                    <img src="<?php echo !empty( $options['credit_img'] ) ? wp_get_attachment_image_url( $options['credit_img'] ) : plugins_url( 'images/powered-by-tourvista.png', __FILE__ ); ?>" alt="" data-default="<?php echo plugins_url( 'images/powered-by-tourvista.png', __FILE__ ); ?>">
                </div>
            </div>
        </td>
        </tr>
        <?php endif; ?>
        </table>
    </fieldset>
    <div class="submit_button">
        <?php submit_button( 'Save Changes', 'primary', 'submit', false ); ?>
        <a href="#" class="reset">Reset Settings</a>
    </div>
</form>
</div>

<div id="current_subscribers" class="hidden">
    <h3>These are the email addresses of all the users who have requested to be notified once your site is live.</h3>
    <p>To copy the addresses to your clipboard, simply click anywhere in the textarea.</p>
    <div>
        <textarea class="code" rows="5"><?php echo implode( ', ', $result ); ?></textarea>
        <span class="hidden">Copied!</span>
    </div><br>
    <table class="widefat fixed striped">
    <thead>
        <tr>
            <td width="20"></td>
            <th>Email Address</th>
            <th>Date Subscribed</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $results as $i => $row ) : ?>
            <tr>
                <td width="20" align="right"><?php echo $i + 1; ?>.</td>
                <td><?php echo $row->email; ?></td>
                <td><?php echo $this->datetime_to_html( $row->added ); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    // image picker
    var mediaUploaders = {};
    $('.upload_image').click(function(e) {
        var id = $(this).attr('id');
        e.preventDefault();
        if (mediaUploaders[id]) {
            mediaUploaders[id]['uploader'].open();
            var $inst, $input, $prev, $img;
            $inst = mediaUploaders[id]['instance'];
            $input = $inst.prev();
            $prev = $inst.next().children().first();
            $img = $prev.find('img');
            return;
        }
        mediaUploaders[id] = {};
        var $inst, $input, $prev, $img, selected, uploader;
        $inst = mediaUploaders[id]['instance'] = $(this);
        $input = $inst.prev();
        $prev = $inst.next().children().first();
        $img = $prev.find('img');
        mediaUploaders[id]['uploader'] = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            library: {
                type: 'image'
            },
            multiple: false
        });
        uploader = mediaUploaders[id]['uploader'];
        uploader.on('open', function() {
            selected = $input.val() != '' ? parseInt($input.val()) : 0;
            if (selected) {
                uploader.state().get('selection').add(wp.media.attachment(selected));
            }
        });
        uploader.on('select', function() {
            var attachment = uploader.state().get('selection').first().toJSON();
            if (attachment.hasOwnProperty('sizes')) {
                $input.val(attachment.id);
                if (attachment.hasOwnProperty('thumbnail')) {
                    $img.attr('src', attachment.sizes.thumbnail.url);
                } else {
                    $img.attr('src', attachment.sizes.full.url);
                }
                $prev.attr('class', 'has_image');
            }
        });
        uploader.open();
    });
    $('.remove_image').click(function() {
        $(this).next().attr('src', $(this).next().attr('data-default'));
        $(this).parents('.image_preview').prev().prev().val('');
        $(this).parent().attr('class', 'no_image');
    });

    // show/hide fields
    function showHideSiblings() {
        showHide($(this).val() == 1, $(this).parents('tr').siblings());
    }
    function showHideUntil() {
        showHide($(this).val() == 1, $(this).parents('tr').nextUntil('tr.spacer'));
    }
    $('input[name=form_show]:checked, input[name=map_show]:checked').each(showHideSiblings);
    $('input[name=map_show]').change(showHideSiblings);
    $('input[name=form_show]').change(function() {
        if ($(this).val() == 1) {
            $(this).parents('tr').siblings().not($('input[value=0]:checked').parents('tr').nextUntil('tr.spacer')).fadeIn();
        } else {
            $(this).parents('tr').siblings().fadeOut();
        }
    });
    $('input[name=form_notify]:checked, input[name=email_leads]:checked, input[name=login_show]:checked, input[name=credit_show]:checked').each(showHideUntil);
    $('input[name=form_notify], input[name=email_leads], input[name=login_show], input[name=credit_show]').change(showHideUntil);

    // copy to clipboard
    var clipboard = new Clipboard('textarea.code', {
        target: function(trigger) {
            trigger.select();
            return trigger;
        }
    });
    clipboard.on('success', function(e) {
        $(e.trigger).next().show();
        setTimeout(function() {
            $(e.trigger).next().fadeOut();
        }, 3000);
    });

    // confirm reset
    $('.reset').click(function() {
        if (confirm('Are you sure you want to reset all settings?')) {
            $.post(ajaxurl, {
                'action': 'reset_settings',
            }, function(response) {
                window.location = '<?php echo $_SERVER['REQUEST_URI']; ?>';
            });
        }
        return false;
    });
});
function showHide(condition, $el) {
    if (condition) {
        $el.fadeIn();
    } else {
        $el.fadeOut();
    }
}
</script>