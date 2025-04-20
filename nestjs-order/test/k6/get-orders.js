import http from 'k6/http';
import { check, sleep } from 'k6';
import { config } from './config.js';

// 獲取訂單列表的測試
export default function() {
  const url = `${config.baseUrl}/order`;
  
  // 發送請求
  const response = http.get(url, {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  });
  
  // 檢查結果
  check(response, {
    'Status is 200': (r) => r.status === 200,
    'Response body is not empty': (r) => r.body.length > 0,
    'Response is JSON': (r) => r.headers['Content-Type'] && r.headers['Content-Type'].includes('application/json'),
    'Response time is acceptable': (r) => r.timings.duration < 1000, // 響應時間少於1秒
  });
  
  // 添加間隔以避免過度壓力
  sleep(1);
}

// 設定負載參數
export const options = {
  thresholds: config.thresholds,
  stages: config.stages,
}; 