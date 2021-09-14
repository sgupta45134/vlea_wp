<?php
require_once CMMP_PLUGIN_DIR . '/shared/models/wallet.php';

class CMMicropaymentPlatformBackendImport {
    public static $_importResult = false;
    public function __construct() {

    }

    public function render() {
        global $wpdb;
        ?>
        <p class="clear"></p>
        <h3>Import Wallet Operations</h3>
        <p>
            This tool is used for batch adding / substraction wallet points amounts or setting total wallets points amounts.
        </p>
        <p>
            <strong>Important!!</strong> File should be UTF-8 encoded and, if you use MS Excel, please remember that by default it can't save proper CSV format (comma-delimited) - see <a href="http://support.microsoft.com/kb/291296" target="_blank" rel="nofollow">Microsoft Knowledge Base Article</a></p>

            <?php if (isset(self::$_importResult['msg'])){
                ?>
                <div id="message" class="updated below-h2">
                <?php
                if(self::$_importResult['msg'] == 'success'){
                ?>

                <?php
                    echo 'Import Successfully</br>';
                    echo 'All items: '.self::$_importResult['all'].'</br>';
                    echo 'Success items: '.self::$_importResult['success'].'</br>';
                    echo 'Failed items: '.self::$_importResult['failed'].'</br>';

                }
                if(self::$_importResult['msg'] == 'error'){
                ?>
                Import failed. Please check file format.
                <?php
                }
                ?>
                </div>
                <?php
            }
            ?>

        <form method="post" enctype="multipart/form-data">
            <input type="file" name="importCSV" /><br/>
            <p style="font-weight: bold">Please select import type</p>
            <p>
                <input type="radio" name="action-type" required="required" value="grant"><label for="action-type">Grant / charge by amount</label><br/>
                <input type="radio" name="action-type" required="required" value="set"><label for="action-type">Set wallet points to amount</label><br/>
            </p>
            <input type="submit" value="Import from CSV" name="cmmp_doImport" class="button button-primary"/>
        </form><br />
        Format example for charge/grant action:<br />
        <pre>
            email,points
            "test@testemail.com",100
            "anothertest@testemail.com",-150
            "yetanothertest@testemail.com",100
        </pre><br/>
        Format example for set wallet action:<br />
        <pre>
            email,points
            "test@testemail.com",10000
            "anothertest@testemail.com",1500
            "yetanothertest@testemail.com",50040
        </pre>
        <?php
    }

    public static function importAction() {
        $walletObject = new CMMicropaymentPlatformWallet();
        $walletTransactions = new CMMicropaymentPlatformWalletCharges();
        $file = $_FILES[ 'importCSV' ];
        $action = $_POST['action-type'];
        $filesrc = $file['tmp_name'];
        $fp = fopen($filesrc, 'r');
        $tab = array();
        $result = array('success' => 0, 'failed' => 0, 'all' => 0, 'msg' => '');
        if($fp){
            while (!feof($fp)) {
                $item = fgetcsv($fp, 0, ',', '"');
                $tab[] = $item;
            }
            $result['all'] = count($tab);

            foreach($tab AS $oneItem){
                if(is_email($oneItem[0])){
                    $user = get_user_by('email', $oneItem[0]);
                    if($user){
                        $wallet = $walletObject->getWalletByUserID($user->ID);
                        if($wallet){
                            switch ($action){
                                case 'grant': $walletObject->chargeWallet($wallet->wallet_id, $oneItem[1], false); break;
                                case 'set':
					$pointsDiff = $oneItem[1] - $wallet->points;
					$walletObject->setPoints( $wallet->wallet_name, $oneItem[1] );
					$walletTransactions->log( $pointsDiff, 0, $wallet->wallet_id, 12, 1, 'Import Operation' );
                                    break;
                            }
                            $result['success'] = $result['success'] + 1;
                        }else{
                            $result['failed'] = $result['failed'] + 1;
                        }
                    }else{
                        $result['failed'] = $result['failed'] + 1;
                    }
                }else{
                    $result['failed'] = $result['failed'] + 1;
                }
            }
            $result['msg'] = 'success';
        }else{
            $result['msg'] = 'error';
        }
        self::$_importResult = $result;
    }
}