<?php

class MPHB_Contract_Plugin
{

    /**
     * Filepath of main plugin file.
     *
     * @var string
     */
    public $file;
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version;
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public $plugin_url;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $includes_path;
    /**
     * Absolute path to plugin lib dir
     *
     * @var string
     */
    public $lib_path;
    /**
     * @var bool
     */
    private $_bootstrapped = false;

    public function __construct($file, $version)
    {
        $this->file = $file;
        $this->version = $version;
        $this->plugin_path   = trailingslashit( plugin_dir_path( $file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $file ) );
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
        $this->lib_path = $this->plugin_path . trailingslashit( 'lib' );
    }

    public function contract(): void
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( 'Hotel Booking PDF Contracts can only be called once');
            }
            $this->_run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_action('admin_notices', function() use($e) {
                    hotel_booking_pdf_contracts_notices($e->getMessage());
                });
            }
        }
    }

    protected function _run(): void
    {
        if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
            require_once ($this->lib_path . 'dompdf/autoload.inc.php');
        }

        require_once ($this->includes_path . 'meta-boxes/custom-meta-box.php');
        require_once ($this->includes_path . 'meta-boxes/contract-meta-box.php');
        require_once ($this->includes_path . 'pdf/pdf-helper.php');
        require_once ($this->includes_path . 'users-and-roles/capabilities.php');
        require_once ($this->includes_path . 'utils/booking-utils.php');

        add_filter( 'post_row_actions', array($this, 'mphb_contract_add_print_button'), 10, 2 );
        new MPHB\Addons\Contract\MetaBoxes\ContractMetaBox('print_contract', esc_html__('Contract', 'mphb-contracts'),
            MPHB()->postTypes()->booking()->getPostType(), 'side');

        add_action( 'admin_action_mphb-contract', array($this, 'mphb_contract_action_printpdf') );

    }

    public function log($message): void
    {
        if (is_array($message) || is_object($message))
            $message = print_r($message, true);
        $logger = new WC_Logger();
        $logger->add('mphb-contract', $message);
    }

    public function mphb_contract_add_print_button($actions, $post)
    {
        global $current_screen;

        if ( $current_screen->id !== 'edit-mphb_booking' ) {
            return $actions;
        }

        if ( ! current_user_can( \MPHB\Addons\Contract\UsersAndRoles\Capabilities::GENERATE_CONTRACTS ) ) {
            return $actions;
        }

        $booking = MPHB()->getBookingRepository()->findById( $post->ID );

        if ( $booking->isImported() ) {
            return $actions;
        }

        $nonce  = wp_create_nonce( 'mphb-contract' );
        $contract_link = admin_url( 'admin.php?post=' . $post->ID . '&action=mphb-contract&_wpnonce=' . $nonce );

        $actions['mphb-contract'] = '<a target="_blank" href="' . $contract_link
            . '" title="'
            . esc_attr( __( 'Open contract in PDF', 'mphb-contracts' ) )
            . '">' . __( 'Contract', 'mphb-contracts' ) . '</a>';

        return $actions;
    }

    public function mphb_contract_action_printpdf()
    {
        if (
            ( isset( $_GET['action'] ) && 'mphb-contract' == $_GET['action'] )
            && ! isset( $_GET['post'] )
        ) {
            wp_die( esc_html__( 'No booking found!', 'mphb-contracts' ) );
        }

        if ( ! empty( $_GET['_wpnonce'] ) &&
            wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mphb-contract' ) &&
            current_user_can( \MPHB\Addons\Contract\UsersAndRoles\Capabilities::GENERATE_CONTRACTS )
        ) {

            $id = (int) $_GET['post'];

            if ( $id ) {
                $pdf = new MPHB\Addons\Contract\PDF\PDFHelper();
                $pdf->printPdf($id);
                exit;

            } else {
                wp_die( esc_html__( 'No booking found!', 'mphb-contracts' ) );
            }
        } else {
            wp_die( esc_html__( 'You don\'t have permissions for this action!', 'mphb-contracts' ) );
        }
    }
}