<?php
/**
 * 生成 WooCommerce 商品測試資料 (多進程版本)
 * 
 * 此腳本將使用多進程方式生成大量 WooCommerce 商品資料用於測試目的
 * 生成內容包括：商品基本資訊、商品分類、商品屬性、商品價格、庫存等
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

// 生成商品名稱
function generate_product_name() {
    $adjectives = ['優質', '高級', '豪華', '經典', '時尚', '專業', '舒適', '實用', '創新', '耐用', '精美', '限量版', '全新', '熱銷', '獨特'];
    $product_types = ['手機', '電腦', '平板', '耳機', '手錶', '相機', '電視', '音響', '冰箱', '洗衣機', '微波爐', '空調', '咖啡機', '吸塵器', '電風扇'];
    $brands = ['蘋果', '三星', '華為', '小米', '索尼', '戴爾', '惠普', '聯想', '華碩', '飛利浦', '松下', '海爾', '美的', '博世', 'LG'];
    
    $name = $adjectives[array_rand($adjectives)] . ' ' . 
            $brands[array_rand($brands)] . ' ' . 
            $product_types[array_rand($product_types)] . ' ' . 
            generate_random_string(4);
    
    return $name;
}

// 生成商品描述
function generate_product_description() {
    $paragraphs = [
        '這款產品採用優質材料製造，堅固耐用，能夠承受日常使用的磨損。',
        '產品設計精巧，符合人體工學，使用舒適，操作便捷。',
        '獨特的設計風格，展現現代簡約美學，適合各種環境使用。',
        '高性能配置，運行流暢，滿足各種使用需求，提供卓越的用戶體驗。',
        '能效比高，省電環保，為您節省能源開支。',
        '多功能設計，滿足不同場景的使用需求，一機多用。',
        '操作簡單直觀，即使是初次使用也能快速上手。',
        '智能控制系統，可通過手機APP遠程操作，便捷又智能。',
        '採用最新技術，性能領先於同類產品，提供卓越體驗。',
        '完善的售後服務，購買後無後顧之憂，使用更放心。'
    ];
    
    // 隨機選擇3-5段落
    $num_paragraphs = rand(3, 5);
    $selected = array_rand($paragraphs, $num_paragraphs);
    
    $description = '';
    if (is_array($selected)) {
        foreach ($selected as $key) {
            $description .= $paragraphs[$key] . "\n\n";
        }
    } else {
        $description = $paragraphs[$selected] . "\n\n";
    }
    
    return $description;
}

// 生成商品短描述
function generate_short_description() {
    $descriptions = [
        '高品質產品，性價比極高，滿足您的日常需求。',
        '創新設計，卓越性能，提升您的使用體驗。',
        '精工打造，品質保證，是您理想的選擇。',
        '功能齊全，操作簡便，適合各年齡段用戶使用。',
        '時尚外觀，實用功能，為您的生活增添便利。',
        '優質選材，精心製作，帶來持久耐用的產品體驗。',
        '智能科技，人性化設計，讓生活更加便捷。',
        '強大性能，穩定可靠，滿足您的各種使用場景。',
        '經典款式，永不過時，值得您長期使用。',
        '超高性價比，同樣品質，更優惠的價格。'
    ];
    
    return $descriptions[array_rand($descriptions)];
}

// 生成商品SKU
function generate_sku() {
    $prefix = ['WP', 'SK', 'PD', 'IT', 'GD'];
    return $prefix[array_rand($prefix)] . '-' . strtoupper(generate_random_string(6));
}

// 生成商品分類
function ensure_product_categories($num_categories = 10) {
    $categories = [
        '電子產品' => ['手機配件', '電腦周邊', '智能家居'],
        '家居用品' => ['廚房用具', '衛浴用品', '臥室傢俱'],
        '服裝鞋帽' => ['男士服裝', '女士服裝', '兒童服裝'],
        '食品飲料' => ['零食', '飲料', '生鮮食品'],
        '美妝護膚' => ['面部護理', '彩妝', '香水'],
        '母嬰用品' => ['嬰兒食品', '嬰兒服裝', '玩具'],
        '體育戶外' => ['健身器材', '戶外裝備', '運動服飾'],
        '書籍音像' => ['文學小說', '教育教材', '音樂影視'],
        '汽車用品' => ['汽車配件', '汽車裝飾', '汽車保養'],
        '辦公用品' => ['文具', '辦公設備', '辦公傢俱']
    ];
    
    $category_ids = [];
    
    // 確保主分類存在
    foreach ($categories as $parent => $children) {
        $parent_term = term_exists($parent, 'product_cat');
        if (!$parent_term) {
            $parent_term = wp_insert_term($parent, 'product_cat');
        }
        
        if (!is_wp_error($parent_term)) {
            $parent_id = $parent_term['term_id'];
            $category_ids[] = $parent_id;
            
            // 確保子分類存在
            foreach ($children as $child) {
                $child_term = term_exists($child, 'product_cat', $parent_id);
                if (!$child_term) {
                    $child_term = wp_insert_term($child, 'product_cat', ['parent' => $parent_id]);
                }
                
                if (!is_wp_error($child_term)) {
                    $category_ids[] = $child_term['term_id'];
                }
            }
        }
    }
    
    return $category_ids;
}

// 生成商品標籤
function ensure_product_tags($num_tags = 20) {
    $tags = [
        '熱銷', '促銷', '新品', '推薦', '限時特價', 
        '品質保證', '超值', '限量', '經典', '暢銷', 
        '進口', '國產', '高端', '實惠', '環保', 
        '創新', '時尚', '簡約', '豪華', '實用'
    ];
    
    $tag_ids = [];
    
    foreach ($tags as $tag) {
        $tag_term = term_exists($tag, 'product_tag');
        if (!$tag_term) {
            $tag_term = wp_insert_term($tag, 'product_tag');
        }
        
        if (!is_wp_error($tag_term)) {
            $tag_ids[] = $tag_term['term_id'];
        }
    }
    
    return $tag_ids;
}

// 創建一個商品
function create_product($category_ids, $tag_ids, $product_type = 'simple') {
    // 創建商品基本資訊
    $product_name = generate_product_name();
    $product_data = [
        'post_title'    => $product_name,
        'post_content'  => generate_product_description(),
        'post_status'   => 'publish',
        'post_type'     => 'product',
        'post_excerpt'  => generate_short_description(),
    ];
    
    // 創建商品文章
    $product_id = wp_insert_post($product_data);
    
    if (!$product_id) {
        return false;
    }
    
    // 設置商品類型
    wp_set_object_terms($product_id, $product_type, 'product_type');
    
    // 隨機分配商品分類 (1-3個)
    $num_cats = rand(1, 3);
    $selected_cats = array_rand($category_ids, $num_cats);
    
    if (!is_array($selected_cats)) {
        $selected_cats = [$selected_cats];
    }
    
    $cats_to_add = [];
    foreach ($selected_cats as $key) {
        $cats_to_add[] = $category_ids[$key];
    }
    
    wp_set_object_terms($product_id, $cats_to_add, 'product_cat');
    
    // 隨機分配商品標籤 (0-5個)
    $num_tags = rand(0, 5);
    if ($num_tags > 0) {
        $selected_tags = array_rand($tag_ids, $num_tags);
        
        if (!is_array($selected_tags)) {
            $selected_tags = [$selected_tags];
        }
        
        $tags_to_add = [];
        foreach ($selected_tags as $key) {
            $tags_to_add[] = $tag_ids[$key];
        }
        
        wp_set_object_terms($product_id, $tags_to_add, 'product_tag');
    }
    
    // 設置商品價格
    $regular_price = rand(50, 1000) + 0.99;
    update_post_meta($product_id, '_regular_price', $regular_price);
    
    // 有30%的機會設置特價
    if (rand(1, 100) <= 30) {
        $sale_price = round($regular_price * (rand(70, 95) / 100), 2);
        update_post_meta($product_id, '_sale_price', $sale_price);
        update_post_meta($product_id, '_price', $sale_price);
    } else {
        update_post_meta($product_id, '_price', $regular_price);
    }
    
    // 設置SKU
    $sku = generate_sku();
    update_post_meta($product_id, '_sku', $sku);
    
    // 設置庫存
    update_post_meta($product_id, '_manage_stock', 'yes');
    update_post_meta($product_id, '_stock', rand(0, 100));
    update_post_meta($product_id, '_stock_status', rand(0, 20) == 0 ? 'outofstock' : 'instock');
    
    // 設置商品尺寸和重量
    update_post_meta($product_id, '_weight', rand(1, 50) / 10);
    update_post_meta($product_id, '_length', rand(10, 100));
    update_post_meta($product_id, '_width', rand(10, 100));
    update_post_meta($product_id, '_height', rand(10, 100));
    
    // 設置其他商品元數據
    update_post_meta($product_id, '_virtual', 'no');
    update_post_meta($product_id, '_downloadable', 'no');
    update_post_meta($product_id, '_sold_individually', rand(0, 10) == 0 ? 'yes' : 'no');
    update_post_meta($product_id, '_featured', rand(0, 10) == 0 ? 'yes' : 'no');
    
    return $product_id;
}

// 創建一個變體商品
function create_variable_product($category_ids, $tag_ids) {
    // 創建商品基本資訊
    $product_name = generate_product_name();
    $product_data = [
        'post_title'    => $product_name,
        'post_content'  => generate_product_description(),
        'post_status'   => 'publish',
        'post_type'     => 'product',
        'post_excerpt'  => generate_short_description(),
    ];
    
    // 創建商品文章
    $product_id = wp_insert_post($product_data);
    
    if (!$product_id) {
        return false;
    }
    
    // 設置商品類型為變體商品
    wp_set_object_terms($product_id, 'variable', 'product_type');
    
    // 隨機分配商品分類 (1-3個)
    $num_cats = rand(1, 3);
    $selected_cats = array_rand($category_ids, $num_cats);
    
    if (!is_array($selected_cats)) {
        $selected_cats = [$selected_cats];
    }
    
    $cats_to_add = [];
    foreach ($selected_cats as $key) {
        $cats_to_add[] = $category_ids[$key];
    }
    
    wp_set_object_terms($product_id, $cats_to_add, 'product_cat');
    
    // 隨機分配商品標籤 (0-5個)
    $num_tags = rand(0, 5);
    if ($num_tags > 0) {
        $selected_tags = array_rand($tag_ids, $num_tags);
        
        if (!is_array($selected_tags)) {
            $selected_tags = [$selected_tags];
        }
        
        $tags_to_add = [];
        foreach ($selected_tags as $key) {
            $tags_to_add[] = $tag_ids[$key];
        }
        
        wp_set_object_terms($product_id, $tags_to_add, 'product_tag');
    }
    
    // 創建屬性
    $attributes = [];
    
    // 顏色屬性
    $colors = ['紅色', '藍色', '綠色', '黑色', '白色', '黃色', '紫色', '灰色'];
    $color_terms = [];
    
    foreach ($colors as $color) {
        $term = term_exists($color, 'pa_color');
        if (!$term) {
            $term = wp_insert_term($color, 'pa_color');
        }
        if (!is_wp_error($term)) {
            $color_terms[] = $term['term_id'];
        }
    }
    
    // 尺寸屬性
    $sizes = ['小', '中', '大', 'XL', 'XXL'];
    $size_terms = [];
    
    foreach ($sizes as $size) {
        $term = term_exists($size, 'pa_size');
        if (!$term) {
            $term = wp_insert_term($size, 'pa_size');
        }
        if (!is_wp_error($term)) {
            $size_terms[] = $term['term_id'];
        }
    }
    
    // 隨機選擇要使用的屬性 (顏色 + 尺寸 或者 只有顏色)
    $use_both_attributes = rand(0, 1) == 1;
    
    // 設置顏色屬性
    $selected_colors = array_rand($colors, rand(2, count($colors)));
    if (!is_array($selected_colors)) {
        $selected_colors = [$selected_colors];
    }
    
    $color_values = [];
    foreach ($selected_colors as $key) {
        $color_values[] = $colors[$key];
    }
    
    $attributes['pa_color'] = [
        'name' => 'pa_color',
        'value' => '',
        'position' => 0,
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 1
    ];
    
    wp_set_object_terms($product_id, $color_values, 'pa_color');
    
    // 如果使用兩種屬性，設置尺寸屬性
    if ($use_both_attributes) {
        $selected_sizes = array_rand($sizes, rand(2, count($sizes)));
        if (!is_array($selected_sizes)) {
            $selected_sizes = [$selected_sizes];
        }
        
        $size_values = [];
        foreach ($selected_sizes as $key) {
            $size_values[] = $sizes[$key];
        }
        
        $attributes['pa_size'] = [
            'name' => 'pa_size',
            'value' => '',
            'position' => 1,
            'is_visible' => 1,
            'is_variation' => 1,
            'is_taxonomy' => 1
        ];
        
        wp_set_object_terms($product_id, $size_values, 'pa_size');
    }
    
    // 保存屬性到商品
    update_post_meta($product_id, '_product_attributes', $attributes);
    
    // 創建變體
    if ($use_both_attributes) {
        // 有顏色和尺寸兩種屬性
        foreach ($color_values as $color) {
            foreach ($size_values as $size) {
                create_product_variation($product_id, [
                    'pa_color' => $color,
                    'pa_size' => $size
                ]);
            }
        }
    } else {
        // 只有顏色屬性
        foreach ($color_values as $color) {
            create_product_variation($product_id, [
                'pa_color' => $color
            ]);
        }
    }
    
    // 設置商品元數據
    update_post_meta($product_id, '_sku', generate_sku());
    update_post_meta($product_id, '_virtual', 'no');
    update_post_meta($product_id, '_downloadable', 'no');
    update_post_meta($product_id, '_featured', rand(0, 10) == 0 ? 'yes' : 'no');
    
    return $product_id;
}

// 創建商品變體
function create_product_variation($product_id, $attributes) {
    // 創建變體文章
    $variation = [
        'post_title'  => 'Variation for product #' . $product_id,
        'post_name'   => 'product-' . $product_id . '-variation',
        'post_status' => 'publish',
        'post_parent' => $product_id,
        'post_type'   => 'product_variation',
        'guid'        => home_url() . '/?product_variation=product-' . $product_id . '-variation'
    ];
    
    $variation_id = wp_insert_post($variation);
    
    if (!$variation_id) {
        return false;
    }
    
    // 設置變體屬性
    foreach ($attributes as $taxonomy => $term) {
        $taxonomy = sanitize_title($taxonomy);
        $attribute_key = 'attribute_' . $taxonomy;
        update_post_meta($variation_id, $attribute_key, $term);
    }
    
    // 設置變體價格
    $regular_price = rand(50, 1000) + 0.99;
    update_post_meta($variation_id, '_regular_price', $regular_price);
    
    // 有30%的機會設置特價
    if (rand(1, 100) <= 30) {
        $sale_price = round($regular_price * (rand(70, 95) / 100), 2);
        update_post_meta($variation_id, '_sale_price', $sale_price);
        update_post_meta($variation_id, '_price', $sale_price);
    } else {
        update_post_meta($variation_id, '_price', $regular_price);
    }
    
    // 設置SKU
    $sku_parts = [];
    foreach ($attributes as $key => $value) {
        $sku_parts[] = substr(str_replace('pa_', '', $key), 0, 1) . '-' . substr(sanitize_title($value), 0, 3);
    }
    $sku = generate_sku() . '-' . implode('-', $sku_parts);
    update_post_meta($variation_id, '_sku', $sku);
    
    // 設置庫存
    update_post_meta($variation_id, '_manage_stock', 'yes');
    update_post_meta($variation_id, '_stock', rand(0, 100));
    update_post_meta($variation_id, '_stock_status', rand(0, 20) == 0 ? 'outofstock' : 'instock');
    
    // 設置商品尺寸和重量
    update_post_meta($variation_id, '_weight', rand(1, 50) / 10);
    update_post_meta($variation_id, '_length', rand(10, 100));
    update_post_meta($variation_id, '_width', rand(10, 100));
    update_post_meta($variation_id, '_height', rand(10, 100));
    
    // 設置其他商品元數據
    update_post_meta($variation_id, '_virtual', 'no');
    update_post_meta($variation_id, '_downloadable', 'no');
    
    return $variation_id;
}

/**
 * 多進程生成商品函數
 * 
 * @param array $category_ids 商品分類ID陣列
 * @param array $tag_ids 商品標籤ID陣列
 * @param int $start_index 開始索引
 * @param int $count 要生成的商品數量
 * @param int $process_id 進程ID (用於日誌)
 * @return int 成功生成的商品數量
 */
function process_generate_products($category_ids, $tag_ids, $start_index, $count, $process_id) {
    $products_created = 0;
    // 禁用進程日誌
    // $log_file = sys_get_temp_dir() . "/wc_product_gen_process_{$process_id}.log";
    
    // 處理商品生成
    for ($i = 0; $i < $count; $i++) {
        // 有20%的機會生成變體商品
        if (rand(1, 100) <= 20) {
            $product_id = create_variable_product($category_ids, $tag_ids);
            if ($product_id) {
                $products_created++;
            }
        } else {
            $product_id = create_product($category_ids, $tag_ids);
            if ($product_id) {
                $products_created++;
            }
        }
        
        // 每生成10個商品清理一次緩存
        if ($products_created % 10 == 0) {
            // 清理臨時內存
            wp_cache_flush();
            if (function_exists('wc_delete_product_transients')) {
                wc_delete_product_transients();
            }
        }
    }
    
    return $products_created;
}

/**
 * 主執行函數 - 多進程版本 (靜默模式)
 * 
 * @param int $num_products 要生成的商品總數
 * @param int $num_processes 使用的進程數
 */
function generate_woocommerce_products_multi($num_products = 1000, $num_processes = 4) {
    global $wpdb;
    $start_time = microtime(true);
    
    // 確保進程數量合理
    $num_processes = max(1, min($num_processes, 32));
    
    // 創建商品分類和標籤
    $category_ids = ensure_product_categories();
    $tag_ids = ensure_product_tags();
    
    // 計算每個進程需要處理的商品數量
    $products_per_process = ceil($num_products / $num_processes);
    
    // 存儲子進程的PID
    $child_pids = [];
    
    // 創建進程
    for ($i = 0; $i < $num_processes; $i++) {
        $start_index = $i * $products_per_process;
        $count = min($products_per_process, $num_products - $start_index);
        
        if ($count <= 0) {
            break; // 沒有更多商品需要處理
        }
        
        // 創建子進程
        $pid = pcntl_fork();
        
        if ($pid == -1) {
            // 進程創建失敗，靜默繼續
            continue;
        } elseif ($pid == 0) {
            // 子進程代碼
            $products_created = process_generate_products($category_ids, $tag_ids, $start_index, $count, $i);
            
            // 子進程完成後退出
            exit($products_created);
        } else {
            // 父進程代碼，記錄子進程的PID
            $child_pids[$i] = $pid;
        }
    }
    
    // 父進程等待所有子進程完成
    $total_products_created = 0;
    
    foreach ($child_pids as $process_id => $pid) {
        $status = 0;
        pcntl_waitpid($pid, $status);
        
        if (pcntl_wifexited($status)) {
            $products_created = pcntl_wexitstatus($status);
            $total_products_created += $products_created;
        }
    }
    
    // 更新商品統計
    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients();
    }
    
    return $total_products_created;
}

// 解析命令行參數 (如果有)
// $num_products = 1000; // 預設生成1000個商品
// $num_processes = 4;   // 預設使用4個進程

// if (isset($argv) && count($argv) > 1) {
//     // 如果指定了商品數量
//     if (isset($argv[1]) && is_numeric($argv[1])) {
//         $num_products = (int)$argv[1];
//     }
    
//     // 如果指定了進程數量
//     if (isset($argv[2]) && is_numeric($argv[2])) {
//         $num_processes = (int)$argv[2];
//     }
// }

// // 執行多進程生成程序 (靜默模式)
// generate_woocommerce_products_multi($num_products, $num_processes); 

/**
 * 直接操作數據庫生成訂單資料 (支援 WooCommerce HPOS)
 * 當需要生成非常大量訂單時，繞過 WC_Order API 以提高性能
 * 支援新的 HPOS (高性能訂單存儲) 資料表結構
 * 注意：此方法僅適用於測試環境，因為它直接操作數據庫
 */
function create_order_direct_db_hpos($product_ids) {
    global $wpdb;
    
    // 生成顧客資料
    $customer_data = generate_customer_data();
    
    // 訂單狀態
    $statuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'];
    $weights = [15, 30, 10, 35, 5, 3, 2]; // 各狀態的權重
    $status = random_weighted_element($statuses, $weights);
    
    // 訂單日期
    $days_ago = rand(1, 365); // 過去一年內的訂單
    $date = date('Y-m-d H:i:s', strtotime("-{$days_ago} days") + rand(0, 86400));
    $date_gmt = get_gmt_from_date($date);
    
    // 隨機總額和稅額
    $order_total = 0;
    $tax_amount = 0;
    
    // 1. 插入到 wp_wc_orders 表
    $wpdb->insert(
        $wpdb->prefix . 'wc_orders',
        [
            'status'               => $status,
            'currency'             => 'TWD',
            'type'                 => 'shop_order',
            'tax_amount'           => $tax_amount,
            'total_amount'         => $order_total, // 暫時設為0，後面會更新
            'customer_id'          => 0, // 訪客購買
            'billing_email'        => $customer_data['email'],
            'date_created_gmt'     => $date_gmt,
            'date_updated_gmt'     => $date_gmt,
            'parent_order_id'      => 0,
            'payment_method'       => 'cod',
            'payment_method_title' => '貨到付款',
            'ip_address'           => rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255),
            'user_agent'           => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'customer_note'        => '',
        ]
    );
    
    $order_id = $wpdb->insert_id;
    
    if (!$order_id) {
        return false;
    }
    
    // 2. 添加帳單地址
    $wpdb->insert(
        $wpdb->prefix . 'wc_order_addresses',
        [
            'order_id'     => $order_id,
            'address_type' => 'billing',
            'first_name'   => $customer_data['first_name'],
            'last_name'    => $customer_data['last_name'],
            'company'      => '',
            'address_1'    => $customer_data['address']['address_1'],
            'address_2'    => $customer_data['address']['address_2'],
            'city'         => $customer_data['address']['city'],
            'state'        => '',
            'postcode'     => $customer_data['address']['postcode'],
            'country'      => 'TW',
            'email'        => $customer_data['email'],
            'phone'        => $customer_data['phone'],
        ]
    );
    
    // 3. 添加送貨地址
    $wpdb->insert(
        $wpdb->prefix . 'wc_order_addresses',
        [
            'order_id'     => $order_id,
            'address_type' => 'shipping',
            'first_name'   => $customer_data['first_name'],
            'last_name'    => $customer_data['last_name'],
            'company'      => '',
            'address_1'    => $customer_data['address']['address_1'],
            'address_2'    => $customer_data['address']['address_2'],
            'city'         => $customer_data['address']['city'],
            'state'        => '',
            'postcode'     => $customer_data['address']['postcode'],
            'country'      => 'TW',
            'email'        => $customer_data['email'],
            'phone'        => $customer_data['phone'],
        ]
    );
    
    // 4. 添加訂單操作數據
    $wpdb->insert(
        $wpdb->prefix . 'wc_order_operational_data',
        [
            'order_id'                    => $order_id,
            'created_via'                 => 'admin',
            'woocommerce_version'         => '8.0.0',
            'prices_include_tax'          => 0,
            'coupon_usages_are_counted'   => 0,
            'download_permission_granted' => 0,
            'cart_hash'                   => '',
            'new_order_email_sent'        => 1,
            'order_key'                   => 'wc_order_' . generate_random_string(13),
            'order_stock_reduced'         => 1,
            'date_paid_gmt'               => $status == 'completed' ? $date_gmt : null,
            'date_completed_gmt'          => $status == 'completed' ? date('Y-m-d H:i:s', strtotime($date_gmt) + rand(7200, 86400 * 5)) : null,
            'shipping_tax_amount'         => 0,
            'shipping_total_amount'       => 0, // 暫時設為0，後面會更新
            'discount_tax_amount'         => 0,
            'discount_total_amount'       => 0,
            'recorded_sales'              => 1,
        ]
    );
    
    // 5. 添加訂單元數據
    $meta_data = [
        '_order_number'         => $order_id,
        '_order_version'        => '8.0.0',
        '_price_decimals'       => '0',
        '_order_currency'       => 'TWD',
        '_ywot_order_tracking'  => '',
    ];
    
    foreach ($meta_data as $meta_key => $meta_value) {
        $wpdb->insert(
            $wpdb->prefix . 'wc_orders_meta',
            [
                'order_id'   => $order_id,
                'meta_key'   => $meta_key,
                'meta_value' => $meta_value
            ]
        );
    }
    
    // 6. 添加訂單項目 (商品)
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
            '_line_tax_data' => serialize(['total' => [], 'subtotal' => []]),
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
        
        // 添加到訂單商品查詢表
        $product_date = date('Y-m-d H:i:s', strtotime($date));
        $wpdb->insert(
            $wpdb->prefix . 'wc_order_product_lookup',
            [
                'order_item_id'         => $order_item_id,
                'order_id'              => $order_id,
                'product_id'            => $product_id,
                'variation_id'          => 0,
                'customer_id'           => 0,
                'date_created'          => $product_date,
                'product_qty'           => $item_qty,
                'product_net_revenue'   => $item_total,
                'product_gross_revenue' => $item_total,
                'coupon_amount'         => 0,
                'tax_amount'            => 0,
                'shipping_amount'       => 0,
                'shipping_tax_amount'   => 0,
            ]
        );
    }
    
    // 7. 添加運費
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
    
    $shipping_meta_data = [
        'method_id' => 'flat_rate',
        'instance_id' => '1',
        'cost' => $shipping_cost,
        'total_tax' => 0,
        'taxes' => serialize([]),
        'method_title' => '標準運送'
    ];
    
    foreach ($shipping_meta_data as $meta_key => $meta_value) {
        $wpdb->insert(
            $wpdb->prefix . 'woocommerce_order_itemmeta',
            [
                'order_item_id' => $shipping_item_id,
                'meta_key'      => $meta_key,
                'meta_value'    => $meta_value
            ]
        );
    }
    
    // 8. 更新訂單總金額
    $wpdb->update(
        $wpdb->prefix . 'wc_orders',
        [
            'total_amount' => $order_total
        ],
        [
            'id' => $order_id
        ]
    );
    
    // 更新訂單操作數據中的運費
    $wpdb->update(
        $wpdb->prefix . 'wc_order_operational_data',
        [
            'shipping_total_amount' => $shipping_cost
        ],
        [
            'order_id' => $order_id
        ]
    );
    
    // 9. 添加到訂單統計表
    $wpdb->insert(
        $wpdb->prefix . 'wc_order_stats',
        [
            'order_id'           => $order_id,
            'parent_id'          => 0,
            'date_created'       => $date,
            'date_created_gmt'   => $date_gmt,
            'date_paid'          => $status == 'completed' ? $date : null,
            'date_paid_gmt'      => $status == 'completed' ? $date_gmt : null,
            'date_completed'     => $status == 'completed' ? date('Y-m-d H:i:s', strtotime($date) + rand(7200, 86400 * 5)) : null,
            'date_completed_gmt' => $status == 'completed' ? date('Y-m-d H:i:s', strtotime($date_gmt) + rand(7200, 86400 * 5)) : null,
            'num_items_sold'     => $item_count,
            'total_sales'        => $order_total,
            'tax_total'          => 0,
            'shipping_total'     => $shipping_cost,
            'net_total'          => $order_total - $shipping_cost,
            'returning_customer' => 0,
            'status'             => $status,
            'customer_id'        => 0,
        ]
    );
    
    return $order_id;
}

/**
 * 多進程直接操作數據庫生成訂單 (支援 HPOS)
 * 
 * @param array $product_ids 可用的商品ID
 * @param int $start_index 開始索引
 * @param int $count 要生成的訂單數量
 * @param int $process_id 進程ID
 * @return int 成功生成的訂單數量
 */
function process_generate_orders_direct_db_hpos($product_ids, $start_index, $count, $process_id) {
    $orders_created = 0;
    
    for ($i = 0; $i < $count; $i++) {
        $order_id = create_order_direct_db_hpos($product_ids);
        if ($order_id) {
            $orders_created++;
        }
    }
    
    return $orders_created;
}

/**
 * 檢測 WooCommerce 是否使用 HPOS（高性能訂單存儲）
 */
function is_using_hpos() {
    global $wpdb;
    
    // 檢查 wp_wc_orders 表是否存在
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wc_orders'");
    
    return !empty($table_exists);
}

// 修改主執行函數，支持 HPOS
function generate_woocommerce_orders_multi_db($num_orders = 1000, $num_processes = 4, $use_direct_db = true) {
    global $wpdb;
    $start_time = microtime(true);
    
    // 檢測是否使用 HPOS
    $using_hpos = is_using_hpos();
    
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
                if ($using_hpos) {
                    $orders_created = process_generate_orders_direct_db_hpos($product_ids, $start_index, $count, $i);
                } else {
                    $orders_created = process_generate_orders_direct_db($product_ids, $start_index, $count, $i);
                }
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
generate_woocommerce_orders_multi_db($num_orders, $num_processes, $use_direct_db);