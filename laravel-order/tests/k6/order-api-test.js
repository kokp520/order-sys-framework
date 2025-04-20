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
  // 根據配置的權重決定要執行哪個測試
  const rand = Math.random() * 100;
  let currentWeight = 0;
  
  // 獲取訂單列表 (權重 30%)
  currentWeight += config.endpoints.getOrders.weight;
  if (rand < currentWeight) {
    group('獲取訂單列表', function() {
      getOrders();
    });
    return;
  }
  
  // 創建訂單 (權重 20%)
  currentWeight += config.endpoints.createOrder.weight;
  if (rand < currentWeight) {
    group('創建訂單', function() {
      createOrder(data);
    });
    return;
  }
  
  // 獲取訂單詳情 (權重 30%)
  currentWeight += config.endpoints.getOrderDetail.weight;
  if (rand < currentWeight) {
    group('獲取訂單詳情', function() {
      getOrderDetail(data);
    });
    return;
  }
  
  // 更新訂單 (權重 15%)
  currentWeight += config.endpoints.updateOrder.weight;
  if (rand < currentWeight) {
    group('更新訂單', function() {
      updateOrder(data);
    });
    return;
  }
  
  // 刪除訂單 (權重 5%)
  group('刪除訂單', function() {
    deleteOrder(data);
  });
}

// 設定負載參數
export const options = {
  thresholds: config.thresholds,
  stages: config.stages,
}; 