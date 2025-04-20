<?php
/**
 * 生成 WooCommerce 訂單測試資料 (多進程版本)
 * 
 * 此腳本將使用多進程方式生成大量 WooCommerce 訂單資料用於測試目的
 * 生成內容包括：訂單基本資訊、訂單項目、訂單項目元數據等
 */

// 載入 WordPress 環境
require_once('wp-load.php');

// 確保 WooCommerce 已啟用
if (!function_exists('wc_get_product')) {
    exit(0); // 靜默退出
}

// 確保 pcntl 擴展已啟用
if (!function_exists('pcntl_fork')) {
    exit(0); // 靜默退出
}

// 設置執行時間和記憶體限制
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

// 禁用所有PHP錯誤輸出
error_reporting(0);
ini_set('display_errors', 0);

// 報告記憶體使用情況的助手函數
function memory_usage() {
    $mem_usage = memory_get_usage(true);
    if ($mem_usage < 1024) {
        return $mem_usage . " bytes";
    } elseif ($mem_usage < 1048576) {
        return round($mem_usage / 1024, 2) . " KB";
    } else {
        return round($mem_usage / 1048576, 2) . " MB";
    }
}

// 生成隨機字符串
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}

/**
 * 生成隨機用戶資料
 * 用於訂單的顧客資訊
 */
function generate_customer_data() {
    $first_names = ['張', '王', '李', '趙', '陳', '林', '黃', '吳', '劉', '蔡', '鄭', '楊', '許', '周', '孫'];
    $last_names = ['明', '芳', '偉', '婷', '豪', '雅', '志', '文', '嘉', '宏', '琪', '華', '建', '玲', '政'];
    $email_domains = ['gmail.com', 'yahoo.com.tw', 'hotmail.com', 'outlook.com', 'icloud.com'];
    $cities = ['台北市', '新北市', '桃園市', '台中市', '台南市', '高雄市', '基隆市', '新竹市', '嘉義市'];
    $districts = ['中正區', '信義區', '大安區', '中山區', '松山區', '萬華區', '士林區', '北投區'];
    $streets = ['仁愛路', '信義路', '忠孝東路', '南京東路', '民生東路', '復興南路', '敦化南路', '建國南路'];
    
    $first_name = $first_names[array_rand($first_names)];
    $last_name = $last_names[array_rand($last_names)];
    
    $customer = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => strtolower($first_name . $last_name . rand(100, 999) . '@' . $email_domains[array_rand($email_domains)]),
        'phone' => '09' . rand(10000000, 99999999),
        'address' => [
            'city' => $cities[array_rand($cities)],
            'district' => $districts[array_rand($districts)],
            'address_1' => $streets[array_rand($streets)] . rand(1, 5) . '段' . rand(1, 999) . '號',
            'address_2' => rand(1, 20) . '樓',
            'postcode' => rand(100, 999) . rand(10, 99)
        ]
    ];
    
    return $customer;
}

/**
 * 獲取系統中可用的商品ID列表
 * 用於生成訂單項目
 */
function get_available_product_ids($limit = 100) {
    global $wpdb;
    
    $product_ids = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts}
        WHERE post_type IN ('product', 'product_variation')
        AND post_status = 'publish'
        ORDER BY RAND()
        LIMIT {$limit}
    ");
    
    // 如果沒有商品，創建一些假的商品ID
    if (empty($product_ids)) {
        $product_ids = range(1, 50);
    }
    
    return $product_ids;
}

/**
 * 創建WooCommerce訂單
 */
function create_order($product_ids) {
    // 生成顧客資料
    $customer_data = generate_customer_data();
    
    // 創建訂單
    $order = wc_create_order();
    
    if (!$order) {
        return false;
    }
    
    // 設置訂單基本信息
    $billing_address = [
        'first_name' => $customer_data['first_name'],
        'last_name'  => $customer_data['last_name'],
        'company'    => '',
        'email'      => $customer_data['email'],
        'phone'      => $customer_data['phone'],
        'address_1'  => $customer_data['address']['address_1'],
        'address_2'  => $customer_data['address']['address_2'],
        'city'       => $customer_data['address']['city'],
        'state'      => '',
        'postcode'   => $customer_data['address']['postcode'],
        'country'    => 'TW'
    ];
    
    // 預設使用相同地址做為送貨地址
    $shipping_address = $billing_address;
    
    // 設置帳單地址
    $order->set_address($billing_address, 'billing');
    
    // 設置送貨地址
    $order->set_address($shipping_address, 'shipping');
    
    // 添加訂單項目 (商品)
    $order_total = 0;
    $item_count = rand(1, 5); // 每個訂單1-5件商品
    
    for ($i = 0; $i < $item_count; $i++) {
        // 隨機選擇一個商品
        $product_id = $product_ids[array_rand($product_ids)];
        
        // 獲取商品詳情
        $product = wc_get_product($product_id);
        
        // 如果商品不存在，創建假商品數據
        if (!$product) {
            $item_name = "測試商品 #" . $product_id;
            $item_price = rand(100, 2000);
            $item_qty = rand(1, 3);
            
            // 手動添加訂單項目
            $item_id = $order->add_product_line();
            wc_add_order_item_meta($item_id, '_product_id', $product_id);
            wc_add_order_item_meta($item_id, '_line_subtotal', $item_price * $item_qty);
            wc_add_order_item_meta($item_id, '_line_total', $item_price * $item_qty);
            wc_add_order_item_meta($item_id, '_qty', $item_qty);
            
            $order_total += $item_price * $item_qty;
        } else {
            // 如果商品存在，正常添加到訂單
            $item_price = $product->get_price();
            $item_qty = rand(1, 3);
            
            $item_id = $order->add_product($product, $item_qty);
            
            $order_total += $item_price * $item_qty;
        }
    }
    
    // 生成隨機運費
    $shipping_cost = rand(60, 200);
    $order_total += $shipping_cost;
    
    // 添加運費
    $shipping_item = new WC_Order_Item_Shipping();
    $shipping_item->set_method_title('標準運送');
    $shipping_item->set_total($shipping_cost);
    $order->add_item($shipping_item);
    
    // 設置付款方式
    $payment_methods = ['bacs', 'cheque', 'cod', 'paypal', 'stripe', 'credit_card'];
    $payment_method = $payment_methods[array_rand($payment_methods)];
    $payment_method_title = [
        'bacs' => '銀行轉帳',
        'cheque' => '支票付款',
        'cod' => '貨到付款',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe信用卡',
        'credit_card' => '信用卡'
    ];
    
    $order->set_payment_method($payment_method);
    $order->set_payment_method_title($payment_method_title[$payment_method]);
    
    // 設置訂單狀態
    $statuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'];
    $weights = [15, 30, 10, 35, 5, 3, 2]; // 各狀態的權重
    
    $status = random_weighted_element($statuses, $weights);
    $order->set_status($status);
    
    // 設置訂單日期
    $days_ago = rand(1, 365); // 過去一年內的訂單
    $date = date('Y-m-d H:i:s', strtotime("-{$days_ago} days") + rand(0, 86400));
    $order->set_date_created($date);
    
    // 設置訂單總金額
    $order->set_total($order_total);
    
    // 儲存訂單
    $order->save();
    
    // 添加額外的訂單元數據
    update_post_meta($order->get_id(), '_customer_user', 0); // 訪客購買
    update_post_meta($order->get_id(), '_order_currency', 'TWD');
    update_post_meta($order->get_id(), '_cart_discount', 0);
    update_post_meta($order->get_id(), '_cart_discount_tax', 0);
    update_post_meta($order->get_id(), '_customer_ip_address', rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255));
    update_post_meta($order->get_id(), '_customer_user_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36');
    
    // 如果是已完成訂單，設置完成日期
    if ($status === 'completed') {
        update_post_meta($order->get_id(), '_completed_date', date('Y-m-d H:i:s', strtotime($date) + rand(7200, 86400 * 5))); // 下單後幾小時到幾天完成
    }
    
    return $order->get_id();
}

/**
 * 基於權重隨機選擇元素
 */
function random_weighted_element($elements, $weights) {
    $total_weight = array_sum($weights);
    $rand = mt_rand(1, $total_weight);
    
    $current_weight = 0;
    foreach ($elements as $key => $element) {
        $current_weight += $weights[$key];
        if ($rand <= $current_weight) {
            return $element;
        }
    }
    
    return $elements[0]; // 預設返回第一個元素
}

/**
 * 多進程生成訂單函數
 * 
 * @param array $product_ids 可用的商品ID
 * @param int $start_index 開始索引
 * @param int $count 要生成的訂單數量
 * @param int $process_id 進程ID
 * @return int 成功生成的訂單數量
 */
function process_generate_orders($product_ids, $start_index, $count, $process_id) {
    $orders_created = 0;
    
    // 處理訂單生成
    for ($i = 0; $i < $count; $i++) {
        $order_id = create_order($product_ids);
        if ($order_id) {
            $orders_created++;
        }
        
        // 每生成10個訂單清理一次緩存
        if ($orders_created % 10 == 0) {
            // 清理臨時內存
            wp_cache_flush();
        }
    }
    
    return $orders_created;
}

/**
 * 主執行函數 - 多進程版本 (靜默模式)
 * 
 * @param int $num_orders 要生成的訂單總數
 * @param int $num_processes 使用的進程數
 */
function generate_woocommerce_orders_multi($num_orders = 1000, $num_processes = 4) {
    global $wpdb;
    $start_time = microtime(true);
    
    // 確保進程數量合理
    $num_processes = max(1, min($num_processes, 32));
    
    // 獲取可用的商品ID列表
    $product_ids = get_available_product_ids(100);
    
    // 計算每個進程需要處理的訂單數量
    $orders_per_process = ceil($num_orders / $num_processes);
    
    // 存儲子進程的PID
    $child_pids = [];
    
    // 創建進程
    for ($i = 0; $i < $num_processes; $i++) {
        $start_index = $i * $orders_per_process;
        $count = min($orders_per_process, $num_orders - $start_index);
        
        if ($count <= 0) {
            break; // 沒有更多訂單需要處理
        }
        
        // 創建子進程
        $pid = pcntl_fork();
        
        if ($pid == -1) {
            // 進程創建失敗，靜默繼續
            continue;
        } elseif ($pid == 0) {
            // 子進程代碼
            $orders_created = process_generate_orders($product_ids, $start_index, $count, $i);
            
            // 子進程完成後退出
            exit($orders_created);
        } else {
            // 父進程代碼，記錄子進程的PID
            $child_pids[$i] = $pid;
        }
    }
    
    // 父進程等待所有子進程完成
    $total_orders_created = 0;
    
    foreach ($child_pids as $process_id => $pid) {
        $status = 0;
        pcntl_waitpid($pid, $status);
        
        if (pcntl_wifexited($status)) {
            $orders_created = pcntl_wexitstatus($status);
            $total_orders_created += $orders_created;
        }
    }
    
    return $total_orders_created;
}

/**
 * 直接操作數據庫生成訂單資料
 * 當需要生成非常大量訂單時，繞過 WC_Order API 以提高性能
 * 注意：此方法僅適用於測試環境，因為它直接操作數據庫
 */
function create_order_direct_db($product_ids) {
    global $wpdb;
    
    // 生成顧客資料
    $customer_data = generate_customer_data();
    
    // 訂單狀態
    $statuses = ['wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed'];
    $weights = [15, 30, 10, 35, 5, 3, 2]; // 各狀態的權重
    $status = 'wc-' . str_replace('wc-', '', random_weighted_element($statuses, $weights));
    
    // 訂單日期
    $days_ago = rand(1, 365); // 過去一年內的訂單
    $date = date('Y-m-d H:i:s', strtotime("-{$days_ago} days") + rand(0, 86400));
    
    // 1. 新增訂單到wp_posts表
    $wpdb->insert(
        $wpdb->posts,
        [
            'post_author'    => 1,
            'post_date'      => $date,
            'post_date_gmt'  => get_gmt_from_date($date),
            'post_content'   => '',
            'post_title'     => '訂單 - ' . date('F j, Y @ h:i a', strtotime($date)),
            'post_excerpt'   => '',
            'post_status'    => $status,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_password'  => generate_random_string(20),
            'post_name'      => 'order-' . date('Y-m-d-h-i-s', strtotime($date)),
            'post_type'      => 'shop_order',
        ]
    );
    
    $order_id = $wpdb->insert_id;
    
    if (!$order_id) {
        return false;
    }
    
    // 2. 添加訂單元數據
    $meta_data = [
        '_order_currency'        => 'TWD',
        '_customer_user'         => 0,
        '_billing_first_name'    => $customer_data['first_name'],
        '_billing_last_name'     => $customer_data['last_name'],
        '_billing_company'       => '',
        '_billing_address_1'     => $customer_data['address']['address_1'],
        '_billing_address_2'     => $customer_data['address']['address_2'],
        '_billing_city'          => $customer_data['address']['city'],
        '_billing_state'         => '',
        '_billing_postcode'      => $customer_data['address']['postcode'],
        '_billing_country'       => 'TW',
        '_billing_email'         => $customer_data['email'],
        '_billing_phone'         => $customer_data['phone'],
        '_shipping_first_name'   => $customer_data['first_name'],
        '_shipping_last_name'    => $customer_data['last_name'],
        '_shipping_company'      => '',
        '_shipping_address_1'    => $customer_data['address']['address_1'],
        '_shipping_address_2'    => $customer_data['address']['address_2'],
        '_shipping_city'         => $customer_data['address']['city'],
        '_shipping_state'        => '',
        '_shipping_postcode'     => $customer_data['address']['postcode'],
        '_shipping_country'      => 'TW',
        '_payment_method'        => 'cod',
        '_payment_method_title'  => '貨到付款',
        '_customer_ip_address'   => rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255),
        '_customer_user_agent'   => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
    ];
    
    $order_total = 0;
    
    foreach ($meta_data as $meta_key => $meta_value) {
        $wpdb->insert(
            $wpdb->postmeta,
            [
                'post_id'    => $order_id,
                'meta_key'   => $meta_key,
                'meta_value' => $meta_value
            ]
        );
    }
    
    // 3. 添加訂單項目 (商品)
    $item_count = rand(1, 5); // 每個訂單1-5件商品
    
    for ($i = 0; $i < $item_count; $i++) {
        // 隨機選擇一個商品
        $product_id = $product_ids[array_rand($product_ids)];
        $item_price = rand(100, 2000);
        $item_qty = rand(1, 3);
        $item_total = $item_price * $item_qty;
        $order_total += $item_total;
        
        // 插入到訂單項目表
        $wpdb->insert(
            $wpdb->prefix . 'woocommerce_order_items',
            [
                'order_id' => $order_id,
                'order_item_name' => '測試商品 #' . $product_id,
                'order_item_type' => 'line_item'
            ]
        );
        
        $order_item_id = $wpdb->insert_id;
        
        // 插入訂單項目元數據
        $item_meta_data = [
            '_product_id'    => $product_id,
            '_variation_id'  => 0,
            '_qty'           => $item_qty,
            '_tax_class'     => '',
            '_line_subtotal' => $item_total,
            '_line_total'    => $item_total,
            '_line_subtotal_tax' => 0,
            '_line_tax'      => 0,
        ];
        
        foreach ($item_meta_data as $meta_key => $meta_value) {
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_order_itemmeta',
                [
                    'order_item_id' => $order_item_id,
                    'meta_key'      => $meta_key,
                    'meta_value'    => $meta_value
                ]
            );
        }
    }
    
    // 添加運費
    $shipping_cost = rand(60, 200);
    $order_total += $shipping_cost;
    
    $wpdb->insert(
        $wpdb->prefix . 'woocommerce_order_items',
        [
            'order_id' => $order_id,
            'order_item_name' => '標準運送',
            'order_item_type' => 'shipping'
        ]
    );
    
    $shipping_item_id = $wpdb->insert_id;
    
    $wpdb->insert(
        $wpdb->prefix . 'woocommerce_order_itemmeta',
        [
            'order_item_id' => $shipping_item_id,
            'meta_key'      => 'cost',
            'meta_value'    => $shipping_cost
        ]
    );
    
    // 更新訂單總金額
    $wpdb->insert(
        $wpdb->postmeta,
        [
            'post_id'    => $order_id,
            'meta_key'   => '_order_total',
            'meta_value' => $order_total
        ]
    );
    
    $wpdb->insert(
        $wpdb->postmeta,
        [
            'post_id'    => $order_id,
            'meta_key'   => '_order_shipping',
            'meta_value' => $shipping_cost
        ]
    );
    
    // 如果是已完成訂單，設置完成日期
    if ($status === 'wc-completed') {
        $completed_date = date('Y-m-d H:i:s', strtotime($date) + rand(7200, 86400 * 5));
        $wpdb->insert(
            $wpdb->postmeta,
            [
                'post_id'    => $order_id,
                'meta_key'   => '_completed_date',
                'meta_value' => $completed_date
            ]
        );
    }
    
    return $order_id;
}

/**
 * 多進程直接操作數據庫生成訂單
 * 
 * @param array $product_ids 可用的商品ID
 * @param int $start_index 開始索引
 * @param int $count 要生成的訂單數量
 * @param int $process_id 進程ID
 * @return int 成功生成的訂單數量
 */
function process_generate_orders_direct_db($product_ids, $start_index, $count, $process_id) {
    $orders_created = 0;
    
    for ($i = 0; $i < $count; $i++) {
        $order_id = create_order_direct_db($product_ids);
        if ($order_id) {
            $orders_created++;
        }
    }
    
    return $orders_created;
}

/**
 * 主執行函數 - 多進程版本 (直接數據庫操作)
 * 
 * @param int $num_orders 要生成的訂單總數
 * @param int $num_processes 使用的進程數
 * @param bool $use_direct_db 是否使用直接數據庫操作模式 (適用於大量訂單)
 */
function generate_woocommerce_orders_multi_db($num_orders = 1000, $num_processes = 4, $use_direct_db = true) {
    global $wpdb;
    $start_time = microtime(true);
    
    // 確保進程數量合理
    $num_processes = max(1, min($num_processes, 32));
    
    // 獲取可用的商品ID列表
    $product_ids = get_available_product_ids(100);
    
    // 計算每個進程需要處理的訂單數量
    $orders_per_process = ceil($num_orders / $num_processes);
    
    // 存儲子進程的PID
    $child_pids = [];
    
    // 創建進程
    for ($i = 0; $i < $num_processes; $i++) {
        $start_index = $i * $orders_per_process;
        $count = min($orders_per_process, $num_orders - $start_index);
        
        if ($count <= 0) {
            break; // 沒有更多訂單需要處理
        }
        
        // 創建子進程
        $pid = pcntl_fork();
        
        if ($pid == -1) {
            // 進程創建失敗，靜默繼續
            continue;
        } elseif ($pid == 0) {
            // 子進程代碼
            if ($use_direct_db) {
                $orders_created = process_generate_orders_direct_db($product_ids, $start_index, $count, $i);
            } else {
                $orders_created = process_generate_orders($product_ids, $start_index, $count, $i);
            }
            
            // 子進程完成後退出
            exit($orders_created);
        } else {
            // 父進程代碼，記錄子進程的PID
            $child_pids[$i] = $pid;
        }
    }
    
    // 父進程等待所有子進程完成
    $total_orders_created = 0;
    
    foreach ($child_pids as $process_id => $pid) {
        $status = 0;
        pcntl_waitpid($pid, $status);
        
        if (pcntl_wifexited($status)) {
            $orders_created = pcntl_wexitstatus($status);
            $total_orders_created += $orders_created;
        }
    }
    
    return $total_orders_created;
}

// 解析命令行參數 (如果有)
$num_orders = 1000;     // 預設生成1000個訂單
$num_processes = 4;     // 預設使用4個進程
$use_direct_db = true;  // 預設使用直接數據庫操作 (更高效)

if (isset($argv) && count($argv) > 1) {
    // 如果指定了訂單數量
    if (isset($argv[1]) && is_numeric($argv[1])) {
        $num_orders = (int)$argv[1];
    }
    
    // 如果指定了進程數量
    if (isset($argv[2]) && is_numeric($argv[2])) {
        $num_processes = (int)$argv[2];
    }
    
    // 如果指定了操作模式
    if (isset($argv[3]) && $argv[3] === 'api') {
        $use_direct_db = false;
    }
}

// 執行多進程生成程序 (靜默模式)
if ($use_direct_db) {
    generate_woocommerce_orders_multi_db($num_orders, $num_processes, true);
} else {
    generate_woocommerce_orders_multi($num_orders, $num_processes);
} 