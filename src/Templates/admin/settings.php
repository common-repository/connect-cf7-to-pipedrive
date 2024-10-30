<?php if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}
$data->template->set_template_data(
	array(
		'title' => esc_html__( 'Settings', 'connect-cf7-to-pipedrive' ),
	)
)->get_template_part( 'admin/header' );

$data->template->set_template_data(
	array(
		'message' => $data->settings['message'] ?? false,
	)
)->get_template_part( 'admin/message' );
?>
    <div id="poststuff">
        <div id="post-body">
            <div class="form-wrapper postbox">
                <h3 class="hndle">
                    <label for="title">
						<?php esc_html_e( 'Access Token', 'connect-cf7-to-pipedrive' ); ?>
                    </label>
                </h3>
                <div class="form-group inside">
                    <form method="post">
                        <div class="form-inline-flex">
                            <input class="mw-400" type="text" name="cf7pd_access_token"
                                   id="cf7pd_access_token"
                                   value="<?php echo esc_html( $data->settings['access_token'] ) ?? ''; ?>"
                                   required/>

							<?php wp_nonce_field( 'cf7pd_submit_form' ); ?>
                            <input type='submit' class='button button-secondary' name="connect"
                                   value="<?php esc_html_e( 'Connect', 'connect-cf7-to-pipedrive' ); ?>"/>
                        </div>
                    </form>
                </div>
                <div class="form-group inside">
                    <p><?php esc_html_e( 'Your personal API key can be found under Settings > Personal preferences > API. user guide', 'connect-cf7-to-pipedrive' ); ?></p>
                </div>
            </div>
        </div>
        <div class="form-wrapper">
            <form method="post">
                <h3 scope="row"><label><?php esc_html_e( 'API Error Notification', 'connect-cf7-to-pipedrive' ); ?></label>
                </h3>

                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Subject', 'connect-cf7-to-pipedrive' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" name="cf7pd_notification_subject"
                                   value="<?php echo esc_html( $data->settings['notification_subject'] ) ?? ''; ?>"/>
                            <p class="description"><?php esc_html_e( 'Enter the subject.', 'connect-cf7-to-pipedrive' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e( 'Send To', 'connect-cf7-to-pipedrive' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" name="cf7pd_notification_send_to"
                                   value="<?php echo esc_html( $data->settings['notification_send_to'] ) ?? ''; ?>"/>
                            <p class="description"><?php esc_html_e( 'Enter the email address. For multiple email addresses, you can add email address by comma separated.', 'connect-cf7-to-pipedrive' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label class="form-check-label"
                                   for="gridcfhs_uninstallCheck"><?php esc_html_e( 'Delete data on uninstall?', 'connect-cf7-to-pipedrive' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" class="form-check-input" name="cf7pd_uninstall"
                                   id="cf7pd_uninstall"
                                   value="1" <?php echo esc_html( $data->settings['uninstall'] ) === "1" ? ' checked' : ''; ?> />
                        </td>
                    </tr>

                    </tbody>
                </table>
                <div class="submit">
					<?php wp_nonce_field( 'cf7pd_submit_form' ); ?>
                    <input type='submit' class='button-primary' name="submit"
                           value="<?php esc_html_e( 'Save Changes', 'connect-cf7-to-pipedrive' ); ?>"/>
                </div>
            </form>
        </div>
    </div>
    </div>
<?php
$data->template->get_template_part( 'admin/footer' );


