<div id="icon-plugins" class="icon32"><br></div>
<h2><?php esc_html_e( 'CM Micropayments' ) ?> - <?php esc_html_e( 'Settings', 'cmmp' ); ?></h2>

<?php include_once 'setting-tabs/tabs.phtml' ?>

<div id="tab_container">
    <!--    <h3 style="font-size: 1.8em;">Turn this site to MicroPayments wallets server</h3>-->
    <!--    <h3>Wallet status</h3>-->
    <!--    <h4>--><?php //esc_html_e( 'When you add points in main site you should press this button', 'cmmp' ); ?><!--</h4>-->
    <!--    <p>--><?php //esc_html_e( 'If you have many wallets, this may take some time, so do not close this tab until the end of the process.', 'cmmp' ); ?><!--</p>-->

    <form action="" method="POST">
        <input name="sender" id="sender" type="hidden" value="settings-external-wallet-keys-form"/>
        <table class="form-table">
            <tbody>
<!--                        <tr class="form-field">-->
<!--                            <th colspan="2" scope="row" valign="top">-->
<!--                                <h3 style="font-size: 1.8em;">Turn this site to a MicroPayments wallets server-->
<!--                                </h3>-->
<!---->
<!--                            </th>-->
<!---->
<!--                        </tr>-->
<!--                        <tr class="form-field">-->
<!--                            <th scope="row" valign="top">-->
<!--                                <label for="external-wallet-turn">--><?php //esc_html_e( 'Turn into wallet server', 'cmmp' ); ?><!--</label>-->
<!--                                <div class="field_help"-->
<!--                                     title="--><?php //esc_html_e( 'Enable  wallet server', 'cmmp' ); ?><!-- - --><?php //esc_html_e( 'How many money customer should spend to buy those points.', 'cmmp' ) ?><!--"></div>-->
<!--                            </th>-->
<!--                            <td>-->
<!--                                <input type="checkbox"-->
<!--                                       id="external-wallet-turn" name="cmmp-external-wallet-turn"-->
<!--            						--><?php //if ( isset( $enabled ) && $enabled == 'on' ) {
//										echo 'checked';
//									} ?>
<!--                                       style=""/>-->
<!---->
<!--                            </td>-->
<!--                        </tr>-->


            <tr class="form-field">
                <th colspan="2" scope="row" valign="top">
                    <h3 style="font-size: 1.8em;">Connect this site to an external MicroPayments wallet
                        server</h3>

                    <p>
                        <a target="_blank"
                           href="https://www.cminds.com/wordpress-plugins-library/micropayments-external-api-add-wordpress/">You need to have the MicroPayments External API add-on on the WordPress installation with the wallet server</a>
                    </p>
                </th>

            </tr>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="external-wallet-key"><?php esc_html_e( 'API secure key ', 'cmmp' ); ?></label>
                    <div class="field_help" title="<?php esc_html_e( 'Enter the API secure key', 'cmmp' ) ?>"></div>
                </th>
                <td>
                    <input name="cmmp-external-wallet-key" id="points_value" type="text"
                           value="<?php echo esc_html( $wallet_key ?? '' ); ?>" style="width: 300px;"/>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="external-wallet"><?php esc_html_e( 'Enable  external wallet', 'cmmp' ); ?></label>
                    <div class="field_help"
                         title="<?php esc_html_e( 'Accept connection from external wallets', 'cmmp' ); ?> - <?php esc_html_e( 'Allows this site to connect to external MicroPayments wallets', 'cmmp' ) ?>"></div>
                </th>
                <td>
                    <input type="checkbox"
                           id="external-wallet" name="cmmp-external-wallet"
						<?php if ( isset( $enabled ) && $enabled == 'on' ) {
							echo 'checked';
						} ?>
                           style=""/>

                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="cmmp-external-wallet-url"><?php esc_html_e( 'API host URL ' ); ?></label>
                    <div class="field_help"
                         title="<?php esc_html_e( 'Insert the URL of the site where the MicroPayments plugin is installed. Example: https://site.com', 'cmmp' ) ?>"></div>
                </th>
                <td>
                    <input name="cmmp-external-wallet-url" id="cmmp-external-wallet-url" type="text"
                           value="<?php echo esc_url( $URL ?? '' ) ?>" style="width: 300px;"/>
                    <button type="button" class="exw-check-status button button-primary button-large">
						<?php esc_html_e( 'Check connection to external MicroPayments wallet', 'cmmp' ); ?>
                    </button>

                    <div style="display: inline;" class="exw-statuses">
						<?php if ( $status ): ?>
                            <span class="exw-status enabled">
        <?php esc_html_e( 'Enabled', 'cmmp' ); ?></span>
						<?php else: ?>
                            <span class="exw-status disabled">
       <?php esc_html_e( 'Disabled', 'cmmp' ); ?>  </span>
						<?php endif; ?>


                    </div>
                </td>
            </tr>
            <tr class="form-field">

                <th scope="row" valign="top">
                    <label for="cmmp-external-wallet-url"><?php esc_html_e( 'Synchronize wallet every', 'cms' ); ?></label>
                    <div class="field_help"
                         title="<?php esc_html_e( 'How often the server will check for updates. Press the button if you need to synchronize the wallet right away', 'cmmp' ) ?>"></div>

                </th>
                <td>

                    <select name="cmmp-external-wallet-refresh">
                        <option <?php  selected($refresh, "automatic") ?> value="automatic"><?php esc_html_e( 'Automatic', 'cms' ); ?></option>
                        <option <?php  selected($refresh, "12_hours") ?> value="12_hours"><?php esc_html_e( '12 hours', 'cms' ); ?></option>
                        <option <?php  selected($refresh, "day") ?> value="day"><?php esc_html_e( '1 day', 'cms' ); ?></option>
                    </select>
                    <button type="button" class="exw-refresh-status button button-primary button-large">
						<?php esc_html_e( 'Sync Wallet Now', 'cmmp' ); ?>
                    </button>
                    <img class="cms-loader-ref" style="display: none" width="30px"
                         src="<?php echo plugins_url( 'cm-micropayment-platform/backend/assets/images/giphy2.gif', CMMP_PLUGIN_DIR ); ?>"/>
                </td>
                <td>


                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" value="<?php esc_html_e( 'Save', 'cmmp' ); ?>" class="button button-primary"/>
            <input type="submit" name="cancel" value="<?php esc_html_e( 'Cancel', 'cmmp' ); ?>" class="button"/>
        </p>
    </form>
</div>

