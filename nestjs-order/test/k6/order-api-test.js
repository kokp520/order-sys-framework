import { sleep, group } from 'k6';
import { config } from './config.js';

// 導入所有測試腳本
import getOrders from './get-orders.js';
import createOrder from './create-order.js';
import getOrderDetail from './get-order-detail.js';
import updateOrder from './update-order.js';
import deleteOrder from './delete-order.js';

// 初始化共享數據
export function setup() {
  return { createdOrderIds: [] };
}

// 清理測試數據
export function teardown(data) {
  console.log(`測試完成。創建訂單總數: ${data.createdOrderIds.length}`);
}

// 主測試函數
export default function(data) {
  // 按順序執行所有測試，不使用權重
  
  // 1. 獲取訂單列表
  group('獲取訂單列表', function() {
    getOrders();
  });
  
  // 2. 創建訂單
  group('創建訂單', function() {
    createOrder(data);
  });
  
  // 3. 獲取訂單詳情
  group('獲取訂單詳情', function() {
    getOrderDetail(data);
  });
  
  // 4. 更新訂單
  group('更新訂單', function() {
    updateOrder(data);
  });
  
  // 5. 刪除訂單
  group('刪除訂單', function() {
    deleteOrder(data);
  });
}

// 設定負載參數
export const options = {
  thresholds: config.thresholds,
  stages: config.stages,
}; 