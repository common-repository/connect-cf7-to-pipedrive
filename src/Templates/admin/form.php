<?php if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}
$cf7_fields   = $data->form['cf7_fields'];
$fields       = $data->form['pd_fields'];
$cf7pd_fields = $data->form['cf7pd_fields'];
$_labels_list = $data->form['_labels_list'];

$data->template->set_template_data(
	array(
		'title' => esc_html( $data->form['title'] ),
	)
)->get_template_part( 'admin/header' );
$data->template->set_template_data(
	array(
		'message' => $data->form['message'] ?? false,
	)
)->get_template_part( 'admin/message' );

?>
    <form method="post">
        <div class="form-wrap">
            <div class="form-wrapper field-list">
                <div class="form-group">
					<?php
					if ( $data->form['_form'] ) {
					if ( $cf7_fields ) {
					?>
                    <table class="widefat striped">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'CF7 Form Field', 'connect-cf7-to-pipedrive' ); ?></th>
                            <th><?php esc_html_e( 'PipeDrive Field', 'connect-cf7-to-pipedrive' ); ?></th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th><?php esc_html_e( 'CF7 Form Field', 'connect-cf7-to-pipedrive' ); ?></th>
                            <th><?php esc_html_e( 'PipeDrive Field', 'connect-cf7-to-pipedrive' ); ?></th>
                        </tr>
                        </tfoot>
                        <tbody>
						<?php
						foreach ( $cf7_fields as $cf7_field_key => $cf7_field_value ) {
							?>
                            <tr>
                                <td><?php echo esc_html( $cf7_field_key ); ?></td>
                                <td>
                                    <select name="cf7pd_fields[<?php echo esc_html( $cf7_field_key ); ?>][key]">
                                        <option value=""><?php esc_html_e( 'Select a field', 'connect-cf7-to-pipedrive' ); ?></option>
										<?php
										$_type = '';
										if ( null !== $fields ) {
											foreach ( $fields as $field_key_ => $field_values ) {
												?>
                                                <optgroup label="<?php echo esc_html( $field_key_ ); ?>">
													<?php
													foreach ( $field_values as $field_key => $field_value ) {

														$selected = '';
														if ( isset( $cf7pd_fields[ $cf7_field_key ]['key'] ) && $cf7pd_fields[ $cf7_field_key ]['key'] === $field_key_ . '_' . $field_value['name'] ) {
															$selected = ' selected="selected"';
															$_type    = $field_value['type'];
														}
														?>
                                                        <option value="<?php echo esc_html( $field_key_ . '_' . $field_value['name'] ); ?>"<?php echo esc_html( $selected ); ?>><?php echo esc_html( $field_value['label'] ); ?>
                                                            (
															<?php
															echo 'Type: ' . esc_html( $field_value['type'] );
															echo $field_value['required'] ? esc_html__( ', Required', 'connect-cf7-to-pipedrive' ) : '';
															?>
                                                            )
                                                        </option>
													<?php } ?>
                                                </optgroup>
												<?php
											}
										}
										?>
                                    </select>
                                    <input type="hidden"
                                           name="cf7pd_fields[<?php echo esc_html( $cf7_field_key ); ?>][type]"
                                           value="<?php echo esc_html( $_type ); ?>"/>
                                </td>
                            </tr>
							<?php
						}
						?>
                        </tbody>
                    </table>
                </div>
                <div class="form-group inner">
                    <div class="submit">
		                <?php wp_nonce_field( 'cf7pd_submit_form' ); ?>
                        <input type='submit' class='button-primary' name="submit"
                               value="<?php esc_html_e( 'Save Changes', 'connect-cf7-to-pipedrive' ); ?>"/>
                    </div>
                    <p><?php esc_html_e( 'The plugin functions as follows: Based on the available fields, it will create
                        corresponding entities on the Pipedrive side and establish dependencies between them.
                        Additionally, it generates a new lead based on these entities. So, if you have fields like name,
                        phone, email, address, and message, the plugin will create a lead with the person specified in
                        the name field along with the corresponding phone number and email. Additionally, it will create
                        a company with the address specified in the address field. Then, it will attach the message as a
                        note to the lead.', 'connect-cf7-to-pipedrive' ); ?></p>
                </div>
            </div>
            <div class="form-wrapper postbox form-conf">
                <div class="form-group inside">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label class="form-check-label"
                                       for="cfhs"><?php esc_html_e( 'Enable send', 'connect-cf7-to-pipedrive' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" class="form-check-input" name="cf7pd_active"
                                       value="1"<?php echo '1' === $data->form['cf7pd_active'] ? ' checked' : ''; ?> />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <hr/>
                <div class="form-group inside">

                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label class="form-check-label"
                                       for="cfhs"><?php esc_html_e( 'Update person if exist', 'connect-cf7-to-pipedrive' ); ?></label>
                                <p><?php esc_html_e( 'Search person by email', 'connect-cf7-to-pipedrive' ); ?></p>
                            </th>
                            <td>
                                <input type="checkbox" class="form-check-input" name="cf7pd_update_person"
                                       value="1"<?php echo '1' === $data->form['cf7pd_update_person'] ? ' checked' : ''; ?> />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label class="form-check-label"
                                       for="cfhs"><?php esc_html_e( 'Update organization if exist', 'connect-cf7-to-pipedrive' ); ?></label>
                                <p><?php esc_html_e( 'Search org by name', 'connect-cf7-to-pipedrive' ); ?></p>
                            </th>
                            <td>
                                <input type="checkbox" class="form-check-input" name="cf7pd_update_org"
                                       value="1"<?php echo '1' === $data->form['cf7pd_update_org'] ? ' checked' : ''; ?> />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <hr/>
                <div class="form-group inside label-info">
                    <p><?php esc_html_e( 'Utilize these preconfigured CF7 tags to simplify label mapping. Simply insert this tag into the CF7 form editor and select the appropriate Pipedrive label field in our plugin.', 'connect-cf7-to-pipedrive' ); ?></p>
                    <table class="form-table">
                        <tbody>
						<?php foreach ( $_labels_list as $key => $label ) { ?>
                            <tr>
                                <td class="row <?php echo esc_html( $key ) ?>-group">
                                    <label
                                            class="form-check-label"
                                            for="cfhs"
                                    ><?php echo esc_html(ucfirst( $key ) );
										esc_html_e( ' Label Dropdown', 'connect-cf7-to-pipedrive' ) ?>:</label>
                                    <textarea
                                            style="width:100%;"
                                            onclick="this.select()"
                                            rows="6"
                                    >[select <?php echo esc_html( $key ) ?>-label <?php foreach ( $label as $item ) { ?>"<?php echo esc_html( $item->label ?? $item->name ) ?>|<?php echo esc_html( $item->id ) ?>" <?php } ?>]</textarea>
                                </td>
                            </tr>

						<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
		<?php
		}
		}
		?>
    </form>
<?php
$data->template->get_template_part( 'admin/footer' );