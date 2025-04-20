# 訂單系統 K6 壓力測試

這個目錄包含了用於測試訂單系統所有API端點的K6壓力測試腳本。

## 前置條件

- 安裝 [k6](https://k6.io/docs/getting-started/installation/)
- 啟動訂單系統的API服務器
- 根據需要調整配置文件 `config.js` 中的設置

## 測試腳本說明

- `config.js`: 測試配置，包括目標URL、閾值和負載階段
- `helpers.js`: 輔助函數，用於生成測試數據
- `get-orders.js`: 測試獲取訂單列表API
- `create-order.js`: 測試創建訂單API
- `get-order-detail.js`: 測試獲取訂單詳情API
- `update-order.js`: 測試更新訂單API
- `delete-order.js`: 測試刪除訂單API
- `order-api-test.js`: 整合所有API測試的主腳本

## 運行測試

### 運行單一API測試

```bash
# 運行獲取訂單列表測試
k6 run tests/k6/get-orders.js

# 運行創建訂單測試
k6 run tests/k6/create-order.js

# 運行獲取訂單詳情測試
k6 run tests/k6/get-order-detail.js

# 運行更新訂單測試
k6 run tests/k6/update-order.js

# 運行刪除訂單測試
k6 run tests/k6/delete-order.js
```

### 運行所有API的綜合測試

```bash
k6 run tests/k6/order-api-test.js
```

### 調整負載參數

如需調整測試負載，請修改 `config.js` 文件中的 `stages` 配置：

```javascript
stages: [
  { duration: '30s', target: 5 },    // 熱身階段：30秒內增加到5個虛擬用戶
  { duration: '1m', target: 10 },    // 穩定負載：1分鐘內增加到10個虛擬用戶
  { duration: '2m', target: 20 },    // 中等壓力：2分鐘內增加到20個虛擬用戶
  { duration: '1m', target: 30 },    // 高壓階段：1分鐘內增加到30個虛擬用戶
  { duration: '30s', target: 0 },    // 緩和階段：30秒內降至0
]
```

### 調整API請求的比例

修改 `config.js` 文件中的 `endpoints` 配置調整不同API測試的比例：

```javascript
endpoints: {
  getOrders: {
    weight: 30, // 權重，代表該端點在總測試中的佔比
  },
  createOrder: {
    weight: 20,
  },
  // ...其他端點
}
```

## 測試結果分析

k6 會在測試完成後輸出詳細的指標報告，包括：

- 請求總數和每秒請求數
- 請求延遲（最小、最大、平均、中位數、p90、p95等）
- 錯誤率
- 檢查通過率

對於更詳細的分析，建議將輸出結果匯出為JSON或CSV格式，然後使用其他工具進行數據可視化。

```bash
k6 run --out json=results.json tests/k6/order-api-test.js
```

## 注意事項

- 在生產環境進行壓力測試前，請確保已獲得授權
- 測試中的示例ID需要替換為實際系統中存在的ID
- 對於刪除操作，建議使用專門為測試建立的數據 