import http from 'k6/http';
import { check, sleep } from 'k6';
import { config } from './config.js';
import { randomIntBetween } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

// 以下是預先準備的訂單ID樣本，用於刪除測試
// 注意：這些ID應該是可刪除的測試數據，不是生產環境重要數據
const deletableOrderIds = [325604, 325603, 325602, 325601, 325600]; // 改為您系統中可刪除的測試訂單ID

// 刪除訂單的測試
export default function(data) {
  // 優先使用測試期間創建的訂單ID
  let orderId;
  
  if (data.createdOrderIds && data.createdOrderIds.length > 0) {
    // 從已創建的訂單ID中選擇並移除一個
    const index = randomIntBetween(0, data.createdOrderIds.length - 1);
    orderId = data.createdOrderIds[index];
    
    // 從數組中移除已使用的ID，防止重複刪除
    data.createdOrderIds.splice(index, 1);
  } else {
    // 如果沒有創建新訂單或創建的訂單已全部刪除，則使用樣本ID
    orderId = deletableOrderIds[randomIntBetween(0, deletableOrderIds.length - 1)];
  }
  
  const url = `${config.baseUrl}/order/${orderId}`;
  
  // 發送請求
  const response = http.del(url, null, {
    headers: {
      'Accept': 'application/json',
    },
  });
  
  // 檢查結果
  check(response, {
    'Status is 200': (r) => r.status === 200,
    'Delete confirmed': (r) => {
      try {
        const jsonData = JSON.parse(r.body);
        return jsonData.code === 0 && jsonData.message && jsonData.message.includes('成功');
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