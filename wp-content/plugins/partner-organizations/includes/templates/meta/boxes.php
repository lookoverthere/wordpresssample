<table class="form-table partner-details-table">
    <tr>
        <th scope="row">
            <label for="partner_logo_id"><?php esc_html_e( 'Logo', 'partner-manager' ); ?></label>
        </th>
        <td>
            <div class="partner-logo-preview">
                <?php if ( $logo_url ) : ?>
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="" style="max-width:150px;height:auto;" />
                <?php endif; ?>
            </div>
            <input type="hidden" id="partner_logo_id" name="partner_logo_id" value="<?php echo esc_attr( $logo_id ); ?>" />
            <p>
                <button type="button" class="button partner-logo-upload">Upload Logo</button>
                <button type="button" class="button partner-logo-remove" <?php disabled( ! $logo_id ); ?>><?php esc_html_e( 'Remove', 'partner-manager' ); ?></button>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="partner_website_url"><?php esc_html_e( 'Website URL', 'partner-manager' ); ?></label>
        </th>
        <td>
            <input
                type="url"
                class="regular-text"
                id="partner_website_url"
                name="partner_website_url"
                value="<?php echo esc_attr( $website ); ?>"
                placeholder="https://example.com"
            />
        </td>
    </tr>
</table>
<p class="description">
    <?php esc_html_e( 'Use the title field for the partner name. Assign a category from the Categories box.', 'partner-manager' ); ?>
</p>