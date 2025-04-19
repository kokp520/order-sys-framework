import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';
import { randomIntBetween } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';
import { SharedArray } from 'https://jslib.k6.io/k6-utils/1.3.0/index.js';

// 自定義指標
const pageLoadTime = new Trend('page_load_time');
const requestsPerSecond = new Rate('requests_per_second');
const errors = new Counter('errors');

// 慢查詢相關指標
const slowRequests = new Counter('slow_requests');
const slowQueryTrend = new Trend('slow_query_time');
const detailedSlowQueries = new Trend('detailed_slow_query');

// 定義基礎URL
const BASE_URL = 'http://localhost:8080';

// 實際的商品 slugs
const productSlugs = [
    'eggy小宅包｜寵物居家外出兩用包',
    '喵星人-主食罐-25',
    '喵星人-主食罐-26',
    '喵星人-主食罐-27',
    '喵星人-主食罐-28'
];

// 慢查詢閾值（毫秒）
const SLOW_QUERY_THRESHOLD = 1000;

// 儲存慢查詢日誌的陣列
let slowQueryLogs = [];

export const options = {
    stages: [
        { duration: '10s', target: 100 },    // 快速增加到 100 用戶
        { duration: '30s', target: 1000 },   // 30 秒內暴增到 1000 用戶
        { duration: '1m', target: 1000 },    // 維持 1000 用戶 1 分鐘
        { duration: '20s', target: 0 },      // 快速降低到 0
    ],
    thresholds: {
        'http_req_duration': ['p(95)<5000'],  // 95% 請求在 5 秒內完成
        'http_req_failed': ['rate<0.1'],      // 錯誤率小於 10%
        'page_load_time': ['p(95)<6000'],     // 95% 頁面加載時間在 6 秒內
        'slow_query_time': ['p(90)<3000'],    // 90% 的慢查詢時間不超過 3 秒
    },
};

// 記錄慢查詢的函數
function logSlowQuery(requestType, url, duration, status) {
    if (duration > SLOW_QUERY_THRESHOLD) {
        slowRequests.add(1);
        slowQueryTrend.add(duration);
        
        // 根據請求類型記錄詳細的慢查詢時間
        detailedSlowQueries.add(duration, { type: requestType });
        
        // 添加到慢查詢日誌陣列
        slowQueryLogs.push({
            timestamp: new Date().toISOString(),
            type: requestType,
            url: url,
            duration: duration,
            status: status
        });
        
        // 輸出慢查詢信息到控制台
        console.log(`慢查詢: ${requestType} - ${url} - ${duration}ms - 狀態碼: ${status}`);
    }
}

export default function() {
    // 模擬搶購商品場景
    group('商品搶購', function() {
        // 使用實際的商品 slug 而不是隨機ID
        const slug = productSlugs[Math.floor(Math.random() * productSlugs.length)];
        const encodedSlug = encodeURIComponent(slug);
        
        // 檢查商品庫存
        let checkStockResponse = http.get(`${BASE_URL}/product/${encodedSlug}`);
        check(checkStockResponse, {
            '商品頁面可訪問': (r) => r.status === 200,
            '頁面加載時間 < 3s': (r) => r.timings.duration < 3000,
        });
        
        // 記錄慢查詢
        logSlowQuery('商品詳情頁', `/product/${encodedSlug}`, checkStockResponse.timings.duration, checkStockResponse.status);
        
        // 模擬加入購物車
        let addToCartResponse = http.post(`${BASE_URL}/cart/`, {
            'add-to-cart': slug,
            quantity: 1,
        });
        check(addToCartResponse, {
            '成功加入購物車': (r) => r.status === 200,
            '加入購物車操作時間 < 3s': (r) => r.timings.duration < 3000,
        });
        
        // 記錄慢查詢
        logSlowQuery('加入購物車', '/cart/', addToCartResponse.timings.duration, addToCartResponse.status);
        
        // 模擬結帳流程
        if (addToCartResponse.status === 200) {
            let checkoutResponse = http.post(`${BASE_URL}/checkout/`, {
                payment_method: 'cod',
                billing_email: `test${randomIntBetween(1, 1000)}@example.com`,
            });
            check(checkoutResponse, {
                '結帳成功': (r) => r.status === 200,
                '結帳操作時間 < 4s': (r) => r.timings.duration < 4000,
            });
            
            // 記錄慢查詢
            logSlowQuery('結帳', '/checkout/', checkoutResponse.timings.duration, checkoutResponse.status);
        }
        
        // 添加可能導致慢查詢的操作 - 例如複雜搜索
        let complexSearchResponse = http.get(`${BASE_URL}/?s=${encodeURIComponent('寵物 食品')}&orderby=price&min_price=100&max_price=1000&post_type=product`);
        check(complexSearchResponse, {
            '複雜搜索可訪問': (r) => r.status === 200,
        });
        
        // 記錄慢查詢
        logSlowQuery('複雜搜索', '複雜產品搜索', complexSearchResponse.timings.duration, complexSearchResponse.status);
        
        // 記錄指標
        pageLoadTime.add(checkStockResponse.timings.duration);
        requestsPerSecond.add(1);
        if (checkStockResponse.status !== 200 || addToCartResponse.status !== 200) {
            errors.add(1);
        }
        
        // 模擬用戶思考時間（較短）
        sleep(randomIntBetween(0.1, 0.5));
    });
    
    // 隨機測試可能導致慢查詢的頁面
    if (Math.random() < 0.3) { // 30% 的可能性
        group('潛在慢查詢頁面', function() {
            // 測試分類頁面（有很多商品的分類可能會慢）
            let categoryResponse = http.get(`${BASE_URL}/product-category/pet-food/`);
            logSlowQuery('分類頁面', '/product-category/pet-food/', categoryResponse.timings.duration, categoryResponse.status);
            
            // 測試包含很多篩選條件的頁面
            let filteredResponse = http.get(`${BASE_URL}/shop/?filter_price=50-200&filter_color=red,blue&orderby=popularity`);
            logSlowQuery('篩選頁面', '多條件篩選頁面', filteredResponse.timings.duration, filteredResponse.status);
            
            sleep(randomIntBetween(0.1, 0.3));
        });
    }
}

// 在測試結束時匯出慢查詢日誌
export function handleSummary(data) {
    return {
        'stdout': JSON.stringify({
            metrics: data.metrics,
            slowQueries: slowQueryLogs.slice(0, 100) // 只顯示前100個慢查詢
        }, null, 2)
    };
} 