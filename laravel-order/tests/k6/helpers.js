import { randomString, randomItem } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

// 生成隨機訂單數據
export function generateOrderData() {
  // 隨機生成訂單項目
  const numItems = Math.floor(Math.random() * 3) + 1; // 1-3個項目
  const items = [];
  
  for (let i = 0; i < numItems; i++) {
    const price = parseFloat((Math.random() * 1000 + 50).toFixed(2)); // 50-1050 元
    const quantity = Math.floor(Math.random() * 5) + 1; // 1-5 件
    const taxRate = 0.05; // 5% 稅率
    const taxAmount = parseFloat((price * quantity * taxRate).toFixed(2));
    
    items.push({
      order_item_name: `測試商品 ${randomString(5)}`,
      order_item_type: randomItem(['line_item', 'fee', 'shipping']),
      price: price,
      quantity: quantity,
      tax_amount: taxAmount
    });
  }
  
  // 計算總金額
  const subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  const totalTax = items.reduce((sum, item) => sum + item.tax_amount, 0);
  const totalAmount = subtotal + totalTax;
  
  return {
    status: randomItem(['pending', 'processing', 'completed']),
    currency: 'TWD',
    type: randomItem(['shop_order', 'subscription']),
    customer_id: Math.floor(Math.random() * 1000) + 1,
    billing_email: `test.${randomString(8)}@example.com`,
    payment_method: randomItem(['credit_card', 'bank_transfer', 'paypal']),
    total_amount: parseFloat(totalAmount.toFixed(2)),
    tax_amount: parseFloat(totalTax.toFixed(2)),
    items: items
  };
}

// 生成訂單更新數據（部分更新）
export function generateOrderUpdateData() {
  return {
    status: randomItem(['pending', 'processing', 'completed', 'cancelled']),
    payment_method: randomItem(['credit_card', 'bank_transfer', 'paypal', 'cash_on_delivery']),
    customer_note: `Updated note ${randomString(20)}`
  };
}

// 儲存已創建的訂單ID，用於後續操作
export let createdOrderIds = [];

// 儲存最後一次請求的響應
export let lastResponse = {};

// 初始化測試數據庫
export function setup() {
  return { createdOrderIds: [] };
}

// 清理測試數據
export function teardown(data) {
  // 可選：刪除測試期間創建的所有訂單
  // 注意：這裡我們不直接操作，因為刪除操作應該是測試的一部分
  console.log(`Test completed with ${data.createdOrderIds.length} orders created`);
} 