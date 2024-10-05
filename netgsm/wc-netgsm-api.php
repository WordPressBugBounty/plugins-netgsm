<?php


class WC_REST_Custom_Controller{
    /**
     * You can extend this class with
     * WP_REST_Controller / WC_REST_Controller / WC_REST_Products_V2_Controller / WC_REST_CRUD_Controller etc.
     * Found in packages/woocommerce-rest-api/src/Controllers/
     */
    protected $namespace = 'wc/v3';
    protected $rest_base = 'custom';

    /*
     * Arayanın son siparişini getiren method
     */
    public function get_NetgsmLastOrder($request_data) {
        $parameters = $request_data->get_params();
        if (isset($parameters['phone'])){
            $day = 14;
            if (isset($parameters['day']) && is_numeric($parameters['day'])){
                $day = $parameters['day'];
            }
            $orders =  wc_get_orders( array(
                'limit'        => 1, // Query all orders
                'orderby'      => 'ID',
                'order'        => 'DESC',
                'meta_key'     => '_billing_phone', // The postmeta key field
                'meta_value' => ltrim($parameters['phone'], '0'), // The comparison argument
                'meta_compare' => 'LIKE', // The comparison argument
                'date_query' => array(
                    'after' => date('Y-m-d', strtotime('-'.$day.' days')),
                    'before' => date('Y-m-d', strtotime('today'))
                )
            ));
            $response['error'] = false;
            if (isset($orders[0]) ){
                $response['message'] = 'Son sipariş getirme başarılı';
                $statuslabel = '';
                if (isset($orders[0]->get_data()['status'])){
                    $status = $orders[0]->get_data()['status'];
                    $statuslabel =  wc_get_order_status_name($status);
                }
                $response['data'] = $orders[0]->get_data();
                $response['data']['status_label'] = $statuslabel;
            } else {
                $response['message'] = 'Sipariş Bulunamadı';
            }
        } else {
            $response = [
                'error'=>true,
                'message'=>'phone parametresi eksik.'
            ];
        }
        return $response;
    }

    /*
     * Telefon numarasından müşteri getiren method
     */
    public function get_NetgsmCustomer($request_data) {
        $parameters = $request_data->get_params();
        if (isset($parameters['phone'])){
            $args = array (
                'order' => 'DESC',
                'orderby' => 'ID',
                'number'=>1,    //limit
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'billing_phone',
                        'value'   => ltrim($parameters['phone'], '0'),
                        'compare' => 'LIKE'
                    )
                )
            );
            $query = new WP_User_Query($args);
            $results = $query->get_results();
            $response['error'] = false;
            if (is_array($results) && $results != null && isset($results[0])) {
                $userdata = $results[0];

                $customer = $billing_phone = get_user_meta($userdata->data->ID);

                $usertemp  = [
                    'id'=>$userdata->data->ID,
                    'first_name'=>$customer['first_name'][0],
                    'last_name'=>$customer['last_name'][0],
                    'billing_first_name'=>$customer['billing_first_name'][0],
                    'billing_last_name'=>$customer['billing_last_name'][0],
                    'shipping_first_name'=>$customer['shipping_first_name'][0],
                    'shipping_last_name'=>$customer['shipping_last_name'][0]
                ];

                $response['message'] = 'Kullanıcı getirme başarılı';
                $response['data'] = $usertemp;
            } else {
                $response['message'] = 'Kullanıcı bulunamadı';
            }
        } else {
            $response = [
                'error'=>true,
                'message'=>'phone parametresi eksik.'
            ];
        }
        return $response;
    }

    /*
     * Özel alan yani meta key ile sipariş getirme fonksiyonudur.
     */
    public function get_NetgsmOrderByMeta($request_data) {
        $parameters = $request_data->get_params();
        if (isset($parameters['meta_key']) && isset($parameters['meta_value'])){
            $orders2 =  wc_get_orders( array(
                'limit'        => 1, // Query all orders
                'orderby'      => 'ID',
                'order'        => 'DESC',
                'meta_key'     => $parameters['meta_key'], // The postmeta key field
                'meta_value' => ltrim($parameters['meta_value'], '0'), // The comparison argument
                'meta_compare' => 'LIKE', // The comparison argument
            ));
            $response['error'] = false;
            if (isset($orders2[0]) ){
                $response['message'] = 'Sipariş getirme başarılı';
                if (isset($orders2[0]->get_data()['status'])){
                    $status = $orders2[0]->get_data()['status'];
                    $statuslabel =  wc_get_order_status_name($status);
                }
                $response['data'] = $orders2[0]->get_data();
                $response['data']['status_label'] = $statuslabel;
            } else {
                $response['message'] = 'Sipariş Bulunamadı';
            }
        } else {
            $response = [
                'error'=>true,
                'message'=>'id parametresi eksik.'
            ];
        }
        return $response;
    }

    public function netgsm_api_permission_callback(){
        return true;
    }


    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/netgsm/customer' ,
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'get_NetgsmCustomer' ),
                'permission_callback' => array( $this, 'netgsm_api_permission_callback' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/netgsm/order/meta' ,
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'get_NetgsmOrderByMeta' ),
                'permission_callback' => array( $this, 'netgsm_api_permission_callback' ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/netgsm/order/last' ,
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'get_NetgsmLastOrder' ),
                'permission_callback' => array( $this, 'netgsm_api_permission_callback' ),
            )
        );
    }
}
