<table class="form-table">
	<?php if ( ! $verified ) : ?>
	<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td><p class="description"><?php echo sprintf( __( 'You need a %s account to use this service!', 'mailster-sparkpost' ), '<a href="https://www.sparkpost.com/" class="external">SparkPost</a>' ); ?></p>
		</td>
	</tr>
	<?php endif; ?>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'SparkPost API key', 'mailster-sparkpost' ); ?></th>
		<td><input type="password" name="mailster_options[sparkpost_apikey]" value="<?php echo esc_attr( mailster_option( 'sparkpost_apikey' ) ); ?>" class="regular-text"></td>
	</tr>
	<tr valign="top">
		<th scope="row">&nbsp;</th>
		<td>
			<?php if ( $verified ) : ?>
			<span style="color:#3AB61B">&#10004;</span> <?php esc_html_e( 'Your API Key is ok!', 'mailster-sparkpost' ); ?>
			<?php else : ?>
			<span style="color:#D54E21">&#10006;</span> <?php esc_html_e( 'Your API Key is WRONG!', 'mailster-sparkpost' ); ?>
			<?php endif; ?>

			<input type="hidden" name="mailster_options[sparkpost_verified]" value="<?php echo $verified; ?>">
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Endpoint', 'mailster-sparkpost' ); ?></th>
		<td>
		<select name="mailster_options[sparkpost_endpoint]">
			<option value="0" <?php selected( mailster_option( 'sparkpost_endpoint' ), 'default' ); ?>><?php esc_html_e( 'Default', 'mailster-sparkpost' ); ?></option>
			<option value="eu" <?php selected( mailster_option( 'sparkpost_endpoint' ), 'eu' ); ?>><?php esc_html_e( 'EU', 'mailster-sparkpost' ); ?></option>
		</select>
		</td>
	</tr></table>
<?php if ( 'sparkpost' == mailster_option( 'deliverymethod' ) ) : ?>
<div class="<?php echo ( ! $verified ) ? 'hidden' : ''; ?>">
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Sending Domain', 'mailster-sparkpost' ); ?></th>
		<td>
		<?php $domains = $verified ? $this->get_sending_domains() : array(); ?>
		<?php if ( is_wp_error( $domains ) ) : ?>
			<div class="error inline"><p><strong><?php esc_html_e( 'Not able to get Sending domains. Make sure your API Key is allowed to read them!', 'mailster-sparkpost' ); ?></strong></p></div>
		<?php else : ?>
		<p class="howto"><?php esc_html_e( 'You can send from following Domains:', 'mailster-sparkpost' ); ?></p>
			<ul>
			<?php foreach ( $domains as $domain ) : ?>
				<li><a href="https://app.sparkpost.com/account/sending-domains/edit?domain=<?php echo esc_attr_e( $domain->domain ); ?>" class="external" title="<?php echo esc_attr_e( 'Edit on SparkPost', 'mailster-sparkpost' ); ?>"><?php echo esc_html( $domain->domain ); ?></a></li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Sub Accounts', 'mailster-sparkpost' ); ?></th>
		<td>
		<?php $accounts = $verified ? $this->get_subaccounts() : array(); ?>
		<?php if ( is_wp_error( $accounts ) ) : ?>
			<div class="error inline"><p><strong><?php esc_html_e( 'Not able to get Sub Accounts. Make sure your API Key is allowed to read them! Mailster will use your Master Account.', 'mailster-sparkpost' ); ?></strong></p></div>
		<?php else : ?>
		<select name="mailster_options[sparkpost_subaccount]">
			<option value="0" <?php selected( ! mailster_option( 'sparkpost_subaccount' ) ); ?>><?php esc_html_e( 'use Master account', 'mailster-sparkpost' ); ?></option>
			<?php foreach ( $accounts as $account ) : ?>
			<option value="<?php echo esc_attr( $account->id ); ?>" <?php selected( mailster_option( 'sparkpost_subaccount' ), $account->id ); ?>><?php echo esc_html( $account->name . ' (' . $account->status . ')' ); ?></option>
		<?php endforeach; ?>
		</select> <a href="https://app.sparkpost.com/account/subaccounts" class="external"><?php esc_html_e( 'Manage your Subaccounts', 'mailster-sparkpost' ); ?></a>
		<?php endif; ?>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Send Emails with', 'mailster-sparkpost' ); ?></th>
		<td>
		<select name="mailster_options[sparkpost_api]">
			<option value="web" <?php selected( mailster_option( 'sparkpost_api' ), 'web' ); ?>>WEB API</option>
			<option value="smtp" <?php selected( mailster_option( 'sparkpost_api' ), 'smtp' ); ?>>SMTP API</option>
		</select>
		<span class="description"><?php esc_html_e( 'Use the WEB API as it\'s most likly faster.', 'mailster-sparkpost' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'SMTP Port', 'mailster-sparkpost' ); ?></th>
		<td>
		<select name="mailster_options[sparkpost_port]">
			<option value="587" <?php selected( mailster_option( 'sparkpost_port' ), '587' ); ?>>587</option>
			<option value="2525" <?php selected( mailster_option( 'sparkpost_port' ), '2525' ); ?>>2525</option>
		</select>
		<span class="description"><?php esc_html_e( 'Only in use for SMTP API', 'mailster-sparkpost' ); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Track in SparkPost', 'mailster-sparkpost' ); ?></th>
		<td>
		<select name="mailster_options[sparkpost_track]">
			<option value="0"<?php selected( mailster_option( 'sparkpost_track' ), 0 ); ?>><?php esc_html_e( 'Account defaults', 'mailster-sparkpost' ); ?></option>
			<option value="none"<?php selected( mailster_option( 'sparkpost_track' ), 'none' ); ?>><?php esc_html_e( 'none', 'mailster-sparkpost' ); ?></option>
			<option value="opens"<?php selected( mailster_option( 'sparkpost_track' ), 'opens' ); ?>><?php esc_html_e( 'opens', 'mailster-sparkpost' ); ?></option>
			<option value="clicks"<?php selected( mailster_option( 'sparkpost_track' ), 'clicks' ); ?>><?php esc_html_e( 'clicks', 'mailster-sparkpost' ); ?></option>
			<option value="opens,clicks"<?php selected( mailster_option( 'sparkpost_track' ), 'opens,clicks' ); ?>><?php esc_html_e( 'opens and clicks', 'mailster-sparkpost' ); ?></option>
		</select> <span class="description"><?php esc_html_e( 'Track opens and clicks in SparkPost as well', 'mailster-sparkpost' ); ?></span></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'IP Pool', 'mailster-sparkpost' ); ?></th>
		<td><input type="text" name="mailster_options[sparkpost_ip_pool]" value="<?php echo esc_attr( mailster_option( 'sparkpost_ip_pool' ) ); ?>" class="regular-text">
		<p class="howto"><?php esc_html_e( 'The ID of a dedicated IP pool associated with your account. If this field is not provided, the account’s default dedicated IP pool is used', 'mailster-sparkpost' ); ?></p>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Tags', 'mailster-sparkpost' ); ?></th>
		<td><input type="text" name="mailster_options[sparkpost_tags]" value="<?php echo esc_attr( mailster_option( 'sparkpost_tags' ) ); ?>" class="large-text">
		<p class="howto"><?php esc_html_e( 'Define your tags separated with commas which get send via the SparkPost API', 'mailster-sparkpost' ); ?></p>
	</tr>
</table>
</div>
<?php else : ?>
<input type="hidden" name="mailster_options[sparkpost_subaccount]" value="<?php echo esc_attr( mailster_option( 'sparkpost_subaccount' ) ); ?>">
<input type="hidden" name="mailster_options[sparkpost_api]" value="<?php echo esc_attr( mailster_option( 'sparkpost_api' ) ); ?>">
<input type="hidden" name="mailster_options[sparkpost_port]" value="<?php echo esc_attr( mailster_option( 'sparkpost_port' ) ); ?>">
<input type="hidden" name="mailster_options[sparkpost_track]" value="<?php echo esc_attr( mailster_option( 'sparkpost_track' ) ); ?>">
<input type="hidden" name="mailster_options[sparkpost_ip_pool]" value="<?php echo esc_attr( mailster_option( 'sparkpost_ip_pool' ) ); ?>">
<input type="hidden" name="mailster_options[sparkpost_tags]" value="<?php echo esc_attr( mailster_option( 'sparkpost_tags' ) ); ?>">
	<?php if ( $verified ) : ?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">&nbsp;</th>
			<td><div class="notice notice-warning inline"><p><strong><?php esc_html_e( 'Please save your settings to access further delivery options!', 'mailster-sparkpost' ); ?></strong></p></div></td>
		</tr>
	</table>
	<?php endif; ?>

<?php endif; ?>
