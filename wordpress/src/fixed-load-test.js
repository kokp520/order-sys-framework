import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';
import { randomIntBetween } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

// 自定義指標
const pageLoadTime = new Trend('page_load_time');
const requestsPerSecond = new Rate('requests_per_second');
const errors = new Counter('errors');

// 實際的商品 slugs
const productSlugs = [
    'eggy小宅包｜寵物居家外出兩用包',
    '喵星人-主食罐-25',
    '喵星人-主食罐-26',
    '喵星人-主食罐-27',
    '喵星人-主食罐-28'
];

export const options = {
    stages: [
        { duration: '1m', target: 100 },   // 逐步增加到 100 用戶
        { duration: '5m', target: 200 },   // 增加到 200 用戶
        // { duration: '5m', target: 300 },   // 增加到 300 用戶
        // { duration: '5m', target: 400 },   // 增加到 400 用戶
        // { duration: '5m', target: 500 },   // 增加到 500 用戶
        // { duration: '10m', target: 500 },  // 維持 500 用戶 10 分鐘
        { duration: '5m', target: 0 },     // 逐步減少到 0
    ],
    thresholds: {
        'http_req_duration': ['p(95)<3000'],  // 95% 請求在 3 秒內完成
        'http_req_failed': ['rate<0.05'],     // 錯誤率小於 5%
        'page_load_time': ['p(95)<4000'],     // 95% 頁面加載時間在 4 秒內
    },
};

const BASE_URL = 'http://localhost:8080';

// 模擬用戶行為
export default function() {
    group('首頁訪問', function() {
        let response = http.get(BASE_URL);
        check(response, {
            '首頁狀態碼為 200': (r) => r.status === 200,
            '首頁加載時間 < 2s': (r) => r.timings.duration < 2000,
        });
        pageLoadTime.add(response.timings.duration);
        requestsPerSecond.add(1);
        if (response.status !== 200) {
            errors.add(1);
        }
        sleep(randomIntBetween(1, 3));
    });

    group('商品列表頁', function() {
        let response = http.get(`${BASE_URL}/shop`);
        check(response, {
            '商品列表頁狀態碼為 200': (r) => r.status === 200,
            '商品列表頁加載時間 < 2s': (r) => r.timings.duration < 2000,
        });
        pageLoadTime.add(response.timings.duration);
        requestsPerSecond.add(1);
        if (response.status !== 200) {
            errors.add(1);
        }
        sleep(randomIntBetween(1, 3));
    });

    group('商品搜索', function() {
        let response = http.get(`${BASE_URL}/?s=寵物&post_type=product`);
        check(response, {
            '搜索結果狀態碼為 200': (r) => r.status === 200,
            '搜索結果加載時間 < 2s': (r) => r.timings.duration < 2000,
        });
        pageLoadTime.add(response.timings.duration);
        requestsPerSecond.add(1);
        if (response.status !== 200) {
            errors.add(1);
        }
        sleep(randomIntBetween(1, 3));
    });

    group('商品詳情頁', function() {
        // 使用實際的商品 slug
        let slug = productSlugs[Math.floor(Math.random() * productSlugs.length)];
        let encodedSlug = encodeURIComponent(slug);
        let response = http.get(`${BASE_URL}/product/${encodedSlug}`);
        check(response, {
            '商品詳情頁狀態碼為 200': (r) => r.status === 200,
            '商品詳情頁加載時間 < 2s': (r) => r.timings.duration < 2000,
        });
        pageLoadTime.add(response.timings.duration);
        requestsPerSecond.add(1);
        if (response.status !== 200) {
            errors.add(1);
        }
        sleep(randomIntBetween(1, 3));
    });

    group('購物車操作', function() {
        let slug = productSlugs[Math.floor(Math.random() * productSlugs.length)];
        let response = http.post(`${BASE_URL}/cart/`, {
            'add-to-cart': slug,
            quantity: 1,
        });
        check(response, {
            '加入購物車狀態碼為 200': (r) => r.status === 200,
            '加入購物車操作時間 < 2s': (r) => r.timings.duration < 2000,
        });
        pageLoadTime.add(response.timings.duration);
        requestsPerSecond.add(1);
        if (response.status !== 200) {
            errors.add(1);
        }
        sleep(randomIntBetween(1, 3));
    });
} 