<?php
/**
 * 生成大量假資料填充WordPress資料庫
 *
 * 目標表格及數量：
 * - wp_commentmeta: ~20,801 行
 * - wp_comments: ~184,882 行
 * - wp_options: ~6,938 行
 * - wp_postmeta: ~98,839 行
 * - wp_posts: ~52,484 行
 * - wp_termmeta: ~450 行
 * - wp_usermeta: ~354,122 行
 * - wp_users: ~12,993 行
 */

// 載入WordPress環境
require_once('wp-load.php');

// 設置執行時間和記憶體限制
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

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

// 生成隨機内容
function generate_random_content($min_length = 50, $max_length = 500) {
    $paragraphs = [
        '這是一個隨機生成的段落。用於測試WordPress資料庫的性能和擴展性。',
        '隨機數據生成對於測試數據庫性能非常重要，特別是在處理大型資料集時。',
        '這個腳本會生成大量假資料以模擬真實世界的WordPress網站資料庫負載。',
        '透過填充足夠的假資料，我們可以測試查詢性能、索引效率和系統資源使用情況。',
        '大型資料集可以幫助識別潛在的性能瓶頸和可擴展性問題。',
        '這是一篇用於測試目的的文章內容。該內容沒有實際意義，僅用於填充數據庫。',
        '在真實環境中測試性能對於確保網站正常運行至關重要。',
        '此測試數據模擬了包含大量文章、評論和用戶的活躍WordPress網站。',
        '隨機生成的內容長度和結構各不相同，以模擬真實的用戶生成內容。',
        '這個假資料生成工具會創建具有合理分佈的模擬WordPress內容。'
    ];
    
    $content = '';
    $target_length = rand($min_length, $max_length);
    
    while (strlen($content) < $target_length) {
        $content .= $paragraphs[array_rand($paragraphs)] . "\n\n";
    }
    
    return substr($content, 0, $target_length);
}

// 生成隨機日期（過去1-3年內）
function generate_random_date() {
    $days = rand(1, 1095); // 最多3年
    return date('Y-m-d H:i:s', strtotime("-$days days"));
}

// 生成隨機IP地址
function generate_random_ip() {
    return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
}

// 生成隨機用戶代理字符串
function generate_random_user_agent() {
    $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/91.0.4472.80 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0'
    ];
    
    return $user_agents[array_rand($user_agents)];
}

// 生成隨機電子郵件
function generate_random_email() {
    $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'example.com', 'test.com', 'domain.com'];
    return strtolower(generate_random_string(rand(5, 10))) . '@' . $domains[array_rand($domains)];
}

// 檢查表的當前行數
function get_table_count($table) {
    global $wpdb;
    return $wpdb->get_var("SELECT COUNT(*) FROM $table");
}

// 產生用戶 (wp_users 表)
function generate_users($target_count = 12993) {
    global $wpdb;
    
    $current_count = get_table_count($wpdb->users);
    $to_generate = max(0, $target_count - $current_count);
    
    if ($to_generate <= 0) {
        echo "wp_users 表已經有 $current_count 行，無需生成更多。\n";
        return;
    }
    
    echo "開始生成 $to_generate 個用戶...\n";
    
    $batch_size = 500;
    $batches = ceil($to_generate / $batch_size);
    
    for ($batch = 0; $batch < $batches; $batch++) {
        $values = [];
        $count_in_batch = min($batch_size, $to_generate - ($batch * $batch_size));
        
        for ($i = 0; $i < $count_in_batch; $i++) {
            $username = 'user_' . generate_random_string(8);
            $email = generate_random_email();
            $date = generate_random_date();
            
            // 僅使用 WordPress 的密碼雜湊函數生成一個簡單的雜湊值
            $password = wp_hash_password('password' . rand(1000, 9999));
            
            $values[] = $wpdb->prepare(
                "(%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                $username,
                $password,
                $username,
                $email,
                $email,
                $date,
                '',
                0,
                $username
            );
        }
        
        if (!empty($values)) {
            $query = "INSERT INTO {$wpdb->users} 
                     (user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name) 
                     VALUES " . implode(", ", $values);
            
            $wpdb->query($query);
        }
        
        echo "已生成 " . (($batch + 1) * $batch_size) . " / $to_generate 用戶\n";
        echo "記憶體使用: " . memory_usage() . "\n";
    }
    
    echo "用戶生成完成。總計: " . get_table_count($wpdb->users) . " 行\n";
}

// 產生用戶元資料 (wp_usermeta 表)
function generate_usermeta($target_count = 3541220) {
    global $wpdb;
    
    $current_count = get_table_count($wpdb->usermeta);
    $to_generate = max(0, $target_count - $current_count);
    
    if ($to_generate <= 0) {
        echo "wp_usermeta 表已經有 $current_count 行，無需生成更多。\n";
        return;
    }
    
    echo "開始生成 $to_generate 條用戶元資料...\n";
    
    // 獲取所有用戶ID
    $user_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->users}");
    if (empty($user_ids)) {
        echo "沒有找到用戶，請先生成用戶數據。\n";
        return;
    }
    
    // 預定義可能的元資料鍵
    $meta_keys = [
        'nickname', 'first_name', 'last_name', 'description', 'wp_capabilities',
        'wp_user_level', 'show_admin_bar_front', 'wp_dashboard_quick_press_last_post_id',
        'wp_user-settings', 'wp_user-settings-time', 'admin_color', 'comment_shortcuts',
        'dismissed_wp_pointers', 'show_welcome_panel', 'session_tokens', 'billing_first_name',
        'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2',
        'billing_city', 'billing_postcode', 'billing_country', 'billing_state',
        'billing_phone', 'billing_email', 'shipping_first_name', 'shipping_last_name',
        'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city',
        'shipping_postcode', 'shipping_country', 'shipping_state'
    ];
    
    $batch_size = 1000;
    $batches = ceil($to_generate / $batch_size);
    
    for ($batch = 0; $batch < $batches; $batch++) {
        $values = [];
        $count_in_batch = min($batch_size, $to_generate - ($batch * $batch_size));
        
        for ($i = 0; $i < $count_in_batch; $i++) {
            $user_id = $user_ids[array_rand($user_ids)];
            $meta_key = $meta_keys[array_rand($meta_keys)];
            $meta_value = generate_random_string(rand(5, 20));
            
            // 針對特殊欄位生成適當的值
            if ($meta_key == 'wp_capabilities') {
                $roles = ['subscriber', 'contributor', 'author', 'editor', 'administrator'];
                $role = $roles[array_rand($roles)];
                $meta_value = serialize([$role => true]);
            } elseif ($meta_key == 'wp_user_level') {
                $meta_value = rand(0, 10);
            }
            
            $values[] = $wpdb->prepare(
                "(%d, %s, %s)",
                $user_id,
                $meta_key,
                $meta_value
            );
        }
        
        if (!empty($values)) {
            $query = "INSERT INTO {$wpdb->usermeta} 
                     (user_id, meta_key, meta_value) 
                     VALUES " . implode(", ", $values);
            
            $wpdb->query($query);
        }
        
        echo "已生成 " . (($batch + 1) * $batch_size) . " / $to_generate 條用戶元資料\n";
        echo "記憶體使用: " . memory_usage() . "\n";
    }
    
    echo "用戶元資料生成完成。總計: " . get_table_count($wpdb->usermeta) . " 行\n";
}

// 產生文章 (wp_posts 表)
function generate_posts($target_count = 5248400) {
    global $wpdb;
    
    $current_count = get_table_count($wpdb->posts);
    $to_generate = max(0, $target_count - $current_count);
    
    if ($to_generate <= 0) {
        echo "wp_posts 表已經有 $current_count 行，無需生成更多。\n";
        return;
    }
    
    echo "開始生成 $to_generate 篇文章...\n";
    
    // 獲取所有用戶ID
    $user_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->users}");
    if (empty($user_ids)) {
        echo "沒有找到用戶，請先生成用戶數據。\n";
        return;
    }
    
    // 預定義可能的文章狀態和類型
    $post_statuses = ['publish', 'draft', 'pending', 'private', 'future'];
    $post_types = ['post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'product'];
    
    $batch_size = 500;
    $batches = ceil($to_generate / $batch_size);
    
    for ($batch = 0; $batch < $batches; $batch++) {
        $values = [];
        $count_in_batch = min($batch_size, $to_generate - ($batch * $batch_size));
        
        for ($i = 0; $i < $count_in_batch; $i++) {
            $post_author = $user_ids[array_rand($user_ids)];
            $post_date = generate_random_date();
            $post_title = '測試文章 ' . generate_random_string(10);
            $post_name = sanitize_title($post_title);
            $post_content = generate_random_content(200, 1000);
            $post_excerpt = substr($post_content, 0, 150) . '...';
            $post_status = $post_statuses[array_rand($post_statuses)];
            $post_type = $post_types[array_rand($post_types)];
            
            $values[] = $wpdb->prepare(
                "(%d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s)",
                $post_author,
                $post_date,
                $post_date,
                $post_content,
                $post_title,
                $post_excerpt,
                $post_status,
                'open',
                'open',
                $post_name,
                '',
                0,
                $post_type
            );
        }
        
        if (!empty($values)) {
            $query = "INSERT INTO {$wpdb->posts} 
                     (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, 
                      post_status, comment_status, ping_status, post_name, post_modified, post_parent, post_type) 
                     VALUES " . implode(", ", $values);
            
            $wpdb->query($query);
        }
        
        echo "已生成 " . (($batch + 1) * $batch_size) . " / $to_generate 篇文章\n";
        echo "記憶體使用: " . memory_usage() . "\n";
    }
    
    echo "文章生成完成。總計: " . get_table_count($wpdb->posts) . " 行\n";
}

// 產生文章元資料 (wp_postmeta 表)
function generate_postmeta($target_count = 9883900) {
    global $wpdb;
    
    $current_count = get_table_count($wpdb->postmeta);
    $to_generate = max(0, $target_count - $current_count);
    
    if ($to_generate <= 0) {
        echo "wp_postmeta 表已經有 $current_count 行，無需生成更多。\n";
        return;
    }
    
    echo "開始生成 $to_generate 條文章元資料...\n";
    
    // 獲取所有文章ID
    $post_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts}");
    if (empty($post_ids)) {
        echo "沒有找到文章，請先生成文章數據。\n";
        return;
    }
    
    // 預定義可能的元資料鍵
    $meta_keys = [
        '_edit_last', '_edit_lock', '_thumbnail_id', '_wp_page_template', '_wp_attachment_metadata',
        '_menu_item_type', '_menu_item_menu_item_parent', '_menu_item_object_id', '_menu_item_object',
        '_menu_item_target', '_menu_item_classes', '_menu_item_xfn', '_menu_item_url',
        '_sku', '_regular_price', '_sale_price', '_tax_status', '_tax_class', '_manage_stock',
        '_stock', '_stock_status', '_weight', '_length', '_width', '_height', '_upsell_ids',
        '_crosssell_ids', '_downloadable', '_virtual', '_download_limit', '_download_expiry',
        '_sold_individually', '_backorders', '_purchase_note', '_wc_average_rating', '_wc_review_count'
    ];
    
    $batch_size = 1000;
    $batches = ceil($to_generate / $batch_size);
    
    for ($batch = 0; $batch < $batches; $batch++) {
        $values = [];
        $count_in_batch = min($batch_size, $to_generate - ($batch * $batch_size));
        
        for ($i = 0; $i < $count_in_batch; $i++) {
            $post_id = $post_ids[array_rand($post_ids)];
            $meta_key = $meta_keys[array_rand($meta_keys)];
            $meta_value = generate_random_string(rand(5, 20));
            
            // 針對特殊欄位生成適當的值
            if ($meta_key == '_regular_price' || $meta_key == '_sale_price') {
                $meta_value = rand(10, 1000);
            } elseif ($meta_key == '_stock') {
                $meta_value = rand(0, 100);
            } elseif ($meta_key == '_wc_average_rating') {
                $meta_value = rand(1, 5);
            }
            
            $values[] = $wpdb->prepare(
                "(%d, %s, %s)",
                $post_id,
                $meta_key,
                $meta_value
            );
        }
        
        if (!empty($values)) {
            $query = "INSERT INTO {$wpdb->postmeta} 
                     (post_id, meta_key, meta_value) 
                     VALUES " . implode(", ", $values);
            
            $wpdb->query($query);
        }
        
        echo "已生成 " . (($batch + 1) * $batch_size) . " / $to_generate 條文章元資料\n";
        echo "記憶體使用: " . memory_usage() . "\n";
    }
    
    echo "文章元資料生成完成。總計: " . get_table_count($wpdb->postmeta) . " 行\n";
}

// 產生評論 (wp_comments 表)
function generate_comments($target_count = 184882) {
    global $wpdb;
    
    $current_count = get_table_count($wpdb->comments);
    $to_generate = max(0, $target_count - $current_count);
    
    if ($to_generate <= 0) {
        echo "wp_comments 表已經有 $current_count 行，無需生成更多。\n";
        return;
    }
    
    echo "開始生成 $to_generate 條評論...\n";
    
    // 獲取所有文章ID
    $post_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' OR post_type = 'product'");
    if (empty($post_ids)) {
        echo "沒有找到文章或產品，請先生成文章數據。\n";
        return;
    }
    
    // 獲取所有用戶ID
    $user_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->users}");
    
    // 預定義評論狀態
    $comment_statuses = ['1', '0', 'spam', 'trash'];
    
    $batch_size = 1000;
    $batches = ceil($to_generate / $batch_size);
    
    for ($batch = 0; $batch < $batches; $batch++) {
        $values = [];
        $count_in_batch = min($batch_size, $to_generate - ($batch * $batch_size));
        
        for ($i = 0; $i < $count_in_batch; $i++) {
            $post_id = $post_ids[array_rand($post_ids)];
            $comment_date = generate_random_date();
            
            // 隨機決定是否為註冊用戶評論
            $is_registered_user = (rand(0, 1) == 1);
            
            if ($is_registered_user && !empty($user_ids)) {
                $user_id = $user_ids[array_rand($user_ids)];
                $user_data = get_userdata($user_id);
                $comment_author = $user_data ? $user_data->display_name : 'Anonymous';
                $comment_author_email = $user_data ? $user_data->user_email : generate_random_email();
            } else {
                $user_id = 0;
                $comment_author = '訪客 ' . generate_random_string(8);
                $comment_author_email = generate_random_email();
            }
            
            $comment_content = generate_random_content(20, 200);
            $comment_status = $comment_statuses[array_rand($comment_statuses)];
            $comment_author_IP = generate_random_ip();
            $comment_agent = generate_random_user_agent();
            
            $values[] = $wpdb->prepare(
                "(%d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s)",
                $post_id,
                $user_id,
                $comment_author,
                $comment_author_email,
                'http://example.com',
                $comment_author_IP,
                $comment_date,
                $comment_date,
                $comment_content,
                $comment_status,
                0,
                $comment_agent
            );
        }
        
        if (!empty($values)) {
            $query = "INSERT INTO {$wpdb->comments} 
                     (comment_post_ID, user_id, comment_author, comment_author_email, comment_author_url, 
                      comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_approved, 
                      comment_parent, comment_agent) 
                     VALUES " . implode(", ", $values);
            
            $wpdb->query($query);
        }
        
        echo "已生成 " . (($batch + 1) * $batch_size) . " / $to_generate 條評論\n";
        echo "記憶體使用: " . memory_usage() . "\n";
    }
    
    echo "評論生成完成。總計: " . get_table_count($wpdb->comments) . " 行\n";
}

// 產生評論元資料 (wp_commentmeta 表)
function generate_commentmeta($target_count = 20801) {
    global $wpdb;
    
    $current_count = get_table_count($wpdb->commentmeta);
    $to_generate = max(0, $target_count - $current_count);
    
    if ($to_generate <= 0) {
        echo "wp_commentmeta 表已經有 $current_count 行，無需生成更多。\n";
        return;
    }
    
    echo "開始生成 $to_generate 條評論元資料...\n";
    
    // 獲取所有評論ID
    $comment_ids = $wpdb->get_col("SELECT comment_ID FROM {$wpdb->comments}");
    if (empty($comment_ids)) {
        echo "沒有找到評論，請先生成評論數據。\n";
        return;
    }
    
    // 預定義可能的元資料鍵
    $meta_keys = [
        'rating', 'verified', 'upvote_count', 'downvote_count', 
        'akismet_history', 'akismet_result', 'akismet_as_submitted',
        'is_customer', 'review_type', 'review_source'
    ];
    
    $batch_size = 1000;
    $batches = ceil($to_generate / $batch_size);
    
    for ($batch = 0; $batch < $batches; $batch++) {
        $values = [];
        $count_in_batch = min($batch_size, $to_generate - ($batch * $batch_size));
        
        for ($i = 0; $i < $count_in_batch; $i++) {
            $comment_id = $comment_ids[array_rand($comment_ids)];
            $meta_key = $meta_keys[array_rand($meta_keys)];
            
            // 針對特殊欄位生成適當的值
            if ($meta_key == 'rating') {
                $meta_value = rand(1, 5);
            } elseif ($meta_key == 'verified') {
                $meta_value = rand(0, 1);
            } elseif ($meta_key == 'upvote_count' || $meta_key == 'downvote_count') {
                $meta_value = rand(0, 50);
            } else {
                $meta_value = generate_random_string(rand(5, 20));
            }
            
            $values[] = $wpdb->prepare(
                "(%d, %s, %s)",
                $comment_id,
                $meta_key,
                $meta_value
            );
        }
        
        if (!empty($values)) {
            $query = "INSERT INTO {$wpdb->commentmeta} 
                     (comment_id, meta_key, meta_value) 
                     VALUES " . implode(", ", $values);
            
            $wpdb->query($query);
        }
        
        echo "已生成 " . (($batch + 1) * $batch_size) . " / $to_generate 條評論元資料\n";
        echo "記憶體使用: " . memory_usage() . "\n";
    }
    
    echo "評論元資料生成完成。總計: " . get_table_count($wpdb->commentmeta) . " 行\n";
}

// 生成選項 (wp_options 表)
function generate_options($target_count = 6938) {
    global $wpdb;
    
    $current_count = get_table_count($wpdb->options);
    $to_generate = max(0, $target_count - $current_count);
    
    if ($to_generate <= 0) {
        echo "wp_options 表已經有 $current_count 行，無需生成更多。\n";
        return;
    }
    
    echo "開始生成 $to_generate 條選項...\n";
    
    // 預定義可能的選項名稱前綴
    $option_prefixes = [
        'widget_', 'theme_mods_', 'transient_', 'site_transient_', 
        'woocommerce_', 'wp_statistics_', 'wp_mail_smtp_', 'elementor_',
        'yoast_', 'wpforms_', 'test_option_', 'custom_option_', 'temp_data_'
    ];
    
    $batch_size = 500;
    $batches = ceil($to_generate / $batch_size);
    
    for ($batch = 0; $batch < $batches; $batch++) {
        $values = [];
        $count_in_batch = min($batch_size, $to_generate - ($batch * $batch_size));
        
        for ($i = 0; $i < $count_in_batch; $i++) {
            $option_name = $option_prefixes[array_rand($option_prefixes)] . generate_random_string(rand(5, 15));
            $option_value = generate_random_content(10, 100);
            $autoload = (rand(0, 1) == 1) ? 'yes' : 'no';
            
            $values[] = $wpdb->prepare(
                "(%s, %s, %s)",
                $option_name,
                $option_value,
                $autoload
            );
        }
        
        if (!empty($values)) {
            $query = "INSERT INTO {$wpdb->options} 
                     (option_name, option_value, autoload) 
                     VALUES " . implode(", ", $values);
            
            $wpdb->query($query);
        }
        
        echo "已生成 " . (($batch + 1) * $batch_size) . " / $to_generate 條選項\n";
        echo "記憶體使用: " . memory_usage() . "\n";
    }
    
    echo "選項生成完成。總計: " . get_table_count($wpdb->options) . " 行\n";
}

// 生成分類元資料 (wp_termmeta 表)
function generate_termmeta($target_count = 450) {
    global $wpdb;
    
    $current_count = get_table_count($wpdb->termmeta);
    $to_generate = max(0, $target_count - $current_count);
    
    if ($to_generate <= 0) {
        echo "wp_termmeta 表已經有 $current_count 行，無需生成更多。\n";
        return;
    }
    
    echo "開始生成 $to_generate 條分類元資料...\n";
    
    // 獲取所有分類ID
    $term_ids = $wpdb->get_col("SELECT term_id FROM {$wpdb->terms}");
    if (empty($term_ids)) {
        // 如果沒有分類，先創建一些
        echo "沒有找到分類，先創建一些...\n";
        for ($i = 0; $i < 50; $i++) {
            $term_name = '測試分類 ' . generate_random_string(8);
            $term_slug = sanitize_title($term_name);
            
            $wpdb->insert(
                $wpdb->terms,
                [
                    'name' => $term_name,
                    'slug' => $term_slug,
                    'term_group' => 0
                ]
            );
            
            $term_id = $wpdb->insert_id;
            
            if ($term_id) {
                $taxonomy = rand(0, 1) ? 'category' : 'product_cat';
                
                $wpdb->insert(
                    $wpdb->term_taxonomy,
                    [
                        'term_id' => $term_id,
                        'taxonomy' => $taxonomy,
                        'description' => generate_random_content(20, 100),
                        'parent' => 0,
                        'count' => rand(0, 100)
                    ]
                );
            }
        }
        
        // 重新獲取分類ID
        $term_ids = $wpdb->get_col("SELECT term_id FROM {$wpdb->terms}");
    }
    
    // 預定義可能的元資料鍵
    $meta_keys = [
        'thumbnail_id', 'display_type', 'order', 'product_count_product_cat',
        'cat_icon', 'cat_color', 'featured', 'top_level_cat'
    ];
    
    $batch_size = 50;
    $batches = ceil($to_generate / $batch_size);
    
    for ($batch = 0; $batch < $batches; $batch++) {
        $values = [];
        $count_in_batch = min($batch_size, $to_generate - ($batch * $batch_size));
        
        for ($i = 0; $i < $count_in_batch; $i++) {
            $term_id = $term_ids[array_rand($term_ids)];
            $meta_key = $meta_keys[array_rand($meta_keys)];
            
            // 針對特殊欄位生成適當的值
            if ($meta_key == 'thumbnail_id') {
                $meta_value = rand(1, 1000);
            } elseif ($meta_key == 'order') {
                $meta_value = rand(0, 100);
            } elseif ($meta_key == 'featured') {
                $meta_value = rand(0, 1);
            } else {
                $meta_value = generate_random_string(rand(5, 15));
            }
            
            $values[] = $wpdb->prepare(
                "(%d, %s, %s)",
                $term_id,
                $meta_key,
                $meta_value
            );
        }
        
        if (!empty($values)) {
            $query = "INSERT INTO {$wpdb->termmeta} 
                     (term_id, meta_key, meta_value) 
                     VALUES " . implode(", ", $values);
            
            $wpdb->query($query);
        }
        
        echo "已生成 " . (($batch + 1) * $batch_size) . " / $to_generate 條分類元資料\n";
        echo "記憶體使用: " . memory_usage() . "\n";
    }
    
    echo "分類元資料生成完成。總計: " . get_table_count($wpdb->termmeta) . " 行\n";
}

// 主執行函數
function generate_all_fake_data() {
    global $wpdb;
    $start_time = microtime(true);
    
    echo "開始生成假資料...\n";
    echo "資料庫前綴: {$wpdb->prefix}\n";
    
    // 按順序生成各表數據（考慮外鍵關係）
    generate_users(12993);
    generate_usermeta(354122);
    generate_posts(52484);
    generate_postmeta(988390);
    generate_comments(1848820);
    generate_commentmeta(208010);
    generate_options(69380);
    generate_termmeta(4500);
    
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);
    
    echo "\n所有假資料生成完成！執行時間: $execution_time 秒\n";
    echo "最終記憶體使用: " . memory_usage() . "\n";
}

// 執行主函數
generate_all_fake_data(); 