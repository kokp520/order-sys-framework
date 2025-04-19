import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
  stages: [
    { duration: '2m', target: 100 }, // 逐步增加到 100 個虛擬用戶
    { duration: '5m', target: 100 }, // 維持 100 個虛擬用戶 5 分鐘
    { duration: '2m', target: 200 }, // 增加到 200 個虛擬用戶
    { duration: '5m', target: 200 }, // 維持 200 個虛擬用戶 5 分鐘
    { duration: '2m', target: 0 },   // 逐步減少到 0
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'], // 95% 的請求應在 2 秒內完成
    http_req_failed: ['rate<0.01'],    // 錯誤率應小於 1%
  },
};

const BASE_URL = 'http://localhost';

export default function () {
  // 測試首頁載入
  let homeRes = http.get(BASE_URL);
  check(homeRes, {
    'homepage status is 200': (r) => r.status === 200,
  });
  sleep(1);

  // 測試商品列表頁
  let shopRes = http.get(`${BASE_URL}/shop`);
  check(shopRes, {
    'shop page status is 200': (r) => r.status === 200,
  });
  sleep(1);

  // 測試隨機商品頁面
  let productIds = [1, 2, 3, 4, 5]; // 這裡需要替換成實際的商品 ID
  let randomProductId = productIds[Math.floor(Math.random() * productIds.length)];
  let productRes = http.get(`${BASE_URL}/product/${randomProductId}`);
  check(productRes, {
    'product page status is 200': (r) => r.status === 200,
  });
  sleep(1);

  // 測試購物車操作
  let cartRes = http.post(`${BASE_URL}/cart/`, {
    add_to_cart: randomProductId,
    quantity: 1,
  });
  check(cartRes, {
    'add to cart successful': (r) => r.status === 200,
  });
  sleep(1);

  // 測試搜尋功能
  let searchRes = http.get(`${BASE_URL}/?s=貓&post_type=product`);
  check(searchRes, {
    'search results status is 200': (r) => r.status === 200,
  });
  sleep(1);
} 