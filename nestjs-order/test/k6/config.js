// 測試環境配置
export const config = {
  baseUrl: 'http://localhost:3000', // 根據您的實際情況調整
  thresholds: {
    http_req_duration: ['p(95)<1000'], // 95%的請求應在1秒內完成
    http_req_failed: ['rate<0.01'],     // 請求失敗率應小於1%
  },
  // 負載配置
  stages: [
    // { duration: '1m', target: 100 },    // 熱身階段：30秒內增加到5個虛擬用戶
    // { duration: '5m', target: 100 },    // 穩定負載：1分鐘內增加到10個虛擬用戶
    // { duration: '30s', target: 0 },    // 緩和階段：30秒內降至0
    { duration: '1m', target: 100 },   // 逐步增加到 100 用戶
    { duration: '5m', target: 200 },   // 增加到 200 用戶
    { duration: '5m', target: 0 },     // 逐步減少到 0
  ],

  // endpoints: {
  //   getOrders: {
  //     weight: 30,
  //   },
  //   createOrder: {
  //     weight: 20,
  //   },
  //   getOrderDetail: {
  //     weight: 30,
  //   },
  //   updateOrder: {
  //     weight: 15,
  //   },
  //   deleteOrder: {
  //     weight: 5,
  //   }
  // }
}; 