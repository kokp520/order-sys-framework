import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
  stages: [
    { duration: '30s', target: 20 }, // 逐步增加到 20 個虛擬用戶
    { duration: '1m', target: 20 },  // 維持 20 個虛擬用戶 1 分鐘
    { duration: '30s', target: 0 },  // 逐步減少到 0 個虛擬用戶
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% 的請求應該在 500ms 內完成
    http_req_failed: ['rate<0.01'],   // 錯誤率應該小於 1%
  },
};

export default function () {
  // 測試首頁載入
  const homeRes = http.get('http://localhost:8080');
  check(homeRes, {
    'homepage status is 200': (r) => r.status === 200,
  });
  sleep(1);

  // 測試商店頁面載入
  const shopRes = http.get('http://localhost:8080/shop');
  check(shopRes, {
    'shop page status is 200': (r) => r.status === 200,
  });
  sleep(1);

  // 測試產品頁面載入（需要替換為實際的產品 URL）
  const productRes = http.get('http://localhost:8080/product/sample-product');
  check(productRes, {
    'product page status is 200': (r) => r.status === 200,
  });
  sleep(1);
} 