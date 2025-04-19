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
$num_products = 1000; // 預設生成1000個商品
$num_processes = 4;   // 預設使用4個進程

if (isset($argv) && count($argv) > 1) {
    // 如果指定了商品數量
    if (isset($argv[1]) && is_numeric($argv[1])) {
        $num_products = (int)$argv[1];
    }
    
    // 如果指定了進程數量
    if (isset($argv[2]) && is_numeric($argv[2])) {
        $num_processes = (int)$argv[2];
    }
}

// 執行多進程生成程序 (靜默模式)
generate_woocommerce_products_multi($num_products, $num_processes); 