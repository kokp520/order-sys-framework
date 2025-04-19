-- 測試一些可能的慢查詢
SELECT * FROM wp_posts p 
JOIN wp_postmeta pm ON p.ID = pm.post_id 
WHERE p.post_type = 'shop_coupon' 
AND pm.meta_key = 'discount_type' 
AND pm.meta_value LIKE '%percent%';

-- 統計分析查詢
SELECT 
    pm.meta_value as discount_type,
    COUNT(*) as count,
    AVG(CAST(pm2.meta_value AS DECIMAL)) as avg_amount
FROM wp_posts p
JOIN wp_postmeta pm ON p.ID = pm.post_id
JOIN wp_postmeta pm2 ON p.ID = pm2.post_id
WHERE p.post_type = 'shop_coupon'
AND pm.meta_key = 'discount_type'
AND pm2.meta_key = 'coupon_amount'
GROUP BY pm.meta_value;