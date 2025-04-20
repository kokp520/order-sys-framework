import http from 'k6/http';
import { check, sleep } from 'k6';
import { config } from './config.js';
import { generateOrderData } from './helpers.js';
import { SharedArray } from 'k6/data';

// 創建訂單的測試
export default function(data) {
  const orderData = generateOrderData();
  const url = `${config.baseUrl}/order`;
  
  // 發送請求
  const response = http.post(url, JSON.stringify(orderData), {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  });
  
  // 檢查結果
  const checks = check(response, {
    'Status is 201': (r) => r.status === 201,
    'Response has order data': (r) => {
      try {
        const jsonData = JSON.parse(r.body);
        return jsonData.order && jsonData.order.id;
      } catch (e) {
        return false;
      }
    },
    'Response time is acceptable': (r) => r.timings.duration < 1500, // 創建操作容許更多時間
  });
  
  // 如果創建成功，記錄訂單ID供後續測試使用
  if (checks && response.status === 201) {
    try {
      const jsonData = JSON.parse(response.body);
      if (jsonData.order && jsonData.order.id) {
        data.createdOrderIds.push(jsonData.order.id);
      }
    } catch (e) {
      console.error('Failed to parse response:', e);
    }
  }
  
  // 添加間隔以避免過度壓力
  sleep(1);
}

// 設定負載參數
export const options = {
  thresholds: config.thresholds,
  stages: config.stages,
}; 