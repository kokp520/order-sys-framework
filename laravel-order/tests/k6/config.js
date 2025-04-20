// 測試環境配置
export const config = {
  baseUrl: 'http://localhost:8081/api', // 根據您的實際情況調整
  thresholds: {
    http_req_duration: ['p(95)<1000'], // 95%的請求應在1秒內完成
    http_req_failed: ['rate<0.01'],     // 請求失敗率應小於1%
  },
  // 負載配置
  stages: [
    { duration: '30s', target: 5 },    // 熱身階段：30秒內增加到5個虛擬用戶
    { duration: '1m', target: 10 },    // 穩定負載：1分鐘內增加到10個虛擬用戶
    { duration: '2m', target: 20 },    // 中等壓力：2分鐘內增加到20個虛擬用戶
    { duration: '1m', target: 30 },    // 高壓階段：1分鐘內增加到30個虛擬用戶
    { duration: '30s', target: 0 },    // 緩和階段：30秒內降至0
  ],
  // 不同API端點的測試配置（可根據特定API進行調整）
  endpoints: {
    getOrders: {
      weight: 30, // 權重，代表該端點在總測試中的佔比
    },
    createOrder: {
      weight: 20,
    },
    getOrderDetail: {
      weight: 30,
    },
    updateOrder: {
      weight: 15,
    },
    deleteOrder: {
      weight: 5,
    }
  }
}; 