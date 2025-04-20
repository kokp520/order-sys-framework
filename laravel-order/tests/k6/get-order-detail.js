import http from 'k6/http';
import { check, sleep } from 'k6';
import { config } from './config.js';
import { randomIntBetween } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

// 以下是預先準備的訂單ID樣本，如果您有實際可用的ID，可以替換這些
// 在實際運行之前，您需要確保這些ID存在於系統中
const sampleOrderIds = [1, 2, 3, 4, 5]; // 改為您系統中實際存在的訂單ID

// 獲取訂單詳情的測試
export default function(data) {
  // 優先使用測試期間創建的訂單ID
  let orderId;
  if (data.createdOrderIds && data.createdOrderIds.length > 0) {
    // 從已創建的訂單ID中選擇一個
    const index = randomIntBetween(0, data.createdOrderIds.length - 1);
    orderId = data.createdOrderIds[index];
  } else {
    // 如果沒有創建新訂單，則使用樣本ID
    orderId = sampleOrderIds[randomIntBetween(0, sampleOrderIds.length - 1)];
  }
  
  const url = `${config.baseUrl}/order/${orderId}`;
  
  // 發送請求
  const response = http.get(url, {
    headers: {
      'Accept': 'application/json',
    },
  });
  
  // 檢查結果
  check(response, {
    'Status is 200': (r) => r.status === 200,
    'Response has order data': (r) => {
      try {
        const jsonData = JSON.parse(r.body);
        return jsonData.id && jsonData.id == orderId;
      } catch (e) {
        return false;
      }
    },
    'Response time is acceptable': (r) => r.timings.duration < 1000,
  });
  
  // 添加間隔以避免過度壓力
  sleep(1);
}

// 設定負載參數
export const options = {
  thresholds: config.thresholds,
  stages: config.stages,
}; 